<?php
/**
 *
 * @package Fence Plus
 * @subpackage
 * @since
 */

class Fence_Plus_Utility {
	/**
	 * @param int|null $user_id
	 * @param bool $include
	 *
	 * @return array
	 */
	public static function get_all_fencers( $user_id = null, $include = true ) {
		$args = array(
			'role' => 'fencer'
		);
		if ( isset( $user_id ) && self::is_coach( $user_id ) ) {
			$coach = new Fence_Plus_Coach( $user_id );
			$fencer_ids = $coach->get_editable_users();
			if ( $include )
				$args['include'] = $fencer_ids;
			else if ( ! $include )
				$args['exclude'] = $fencer_ids;
		}

		return get_users( $args );
	}

	/**
	 * @param null $user_id
	 * @param bool $include to include (true) or exclude (false)
	 *
	 * @return array
	 */
	public static function get_all_coaches( $user_id = null, $include = true ) {
		$args = array(
			'role' => 'coach'
		);
		if ( isset( $user_id ) && self::is_fencer( $user_id ) ) {
			$fencer = Fence_Plus_Fencer::wp_id_db_load( $user_id );
			$coach_ids = $fencer->get_editable_by_users();
			if ( $include )
				$args['include'] = $coach_ids;
			else if ( ! $include )
				$args['exclude'] = $coach_ids;
		}
		else {
			$args = array(
				'role' => 'coach'
			);
		}

		return get_users( $args );
	}

	/**
	 * Sort Fencers highest to lowest rating
	 *
	 * @param $a Fence_Plus_Fencer
	 * @param $b Fence_Plus_Fencer
	 *
	 * @return int
	 */
	public static function sort_fencers( $a, $b ) {
		if ( $a->get_primary_weapon() == array() )
			return 1;
		if ( $b->get_primary_weapon() == array() )
			return - 1;

		return strcmp( implode( "", $a->get_primary_weapon_rating_letter() ) . ( 3000 - (int) $a->get_primary_weapon_rating_year() ),
		  implode( "", $b->get_primary_weapon_rating_letter() ) . ( 3000 - (int) $b->get_primary_weapon_rating_year() )
		);
	}

	/**
	 * Removes all fencer data. Fires on delete_user hook.
	 *
	 * @param $fencer_id int
	 */
	public static function remove_fencer_data( $fencer_id ) {
		try {
			$fencer = Fence_Plus_Fencer::wp_id_db_load( $fencer_id );
		}
		catch ( InvalidArgumentException $e ) {
			return;
		}
		$fencer->remove_data();
	}

	/**
	 * Removes all coach data. Fires on delete_user hook.
	 *
	 * @param $coach_id int
	 */
	public static function remove_coach_data( $coach_id ) {
		try {
			$coach = new Fence_Plus_Coach( $coach_id );
		}
		catch ( InvalidArgumentException $e ) {
			return;
		}
		$coach->remove_data();
	}

	/**
	 * Add a notification to be added to next page load
	 *
	 * @param $message string text of the message to display
	 * @param $class string class of notification error|updated
	 * @param $user_id int|null
	 */
	public static function add_admin_notification( $message, $class, $user_id = null ) {
		if ( null == $user_id )
			$user_id = get_current_user_id();

		$admin_factory = new IBD_Notify_Admin_Factory();
		$notification = $admin_factory->make( $user_id, "Fence Plus", $message, array( 'class' => $class ) );
		$notification->save();
	}

	/**
	 * @param $user WP_User|int
	 *
	 * @return bool
	 */
	public static function is_coach( $user ) {
		if ( ! is_a( $user, 'WP_User' ) ) {
			$user = get_user_by( 'id', $user );

			if ( false === $user )
				return false;
		}

		if ( ! isset( $user->roles[0] ) )
			return false;

		return $user->roles[0] == "coach";
	}

	/**
	 * Determine if user is a fencer
	 *
	 * @param $user WP_User|int WP_User object or WP User ID
	 *
	 * @return bool
	 */
	public static function is_fencer( $user ) {
		if ( ! is_a( $user, 'WP_User' ) ) {
			$user = get_user_by( 'id', $user );

			if ( false == $user )
				return false;
		}

		return $user->roles[0] == "fencer";
	}

	/**
	 * Return user ID from USFA ID
	 *
	 * @param $usfa_id
	 *
	 * @return string|bool WordPress user ID or false if user does not exist
	 */
	public static function get_user_id_from_usfa_id( $usfa_id ) {
		$fencers = get_users( array( "role" => "fencer" ) );
		foreach ( $fencers as $fencer ) {
			$fencer_meta = get_user_meta( $fencer->ID, 'fence_plus_fencer_data', true );
			if ( $usfa_id == $fencer_meta['usfa_id'] )
				return $fencer->ID;
		}

		return false;
	}
}