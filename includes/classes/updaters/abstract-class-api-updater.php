<?php
/**
 *
 * @package Fence Plus
 * @subpackage
 * @since
 */

interface Fence_Plus_API_Updater {
	/**
	 * Update the object
	 *
	 * @return mixed
	 */
	function update();

	/**
	 * Perform call to askFRED API
	 *
	 * @return void
	 */
	function call_api();

	/**
	 * Generate an MD5 checksum
	 *
	 * @param $data
	 */
	function create_md5_checksum( $data );

	/**
	 * Determine if data needs to be reprocessed
	 * Checks equivalency of MD5 hash of current data vs stored md5 hash
	 *
	 * @return bool
	 */
	function reprocessing_needed();

	/**
	 * Do the processing of results
	 *
	 * This should take the data and load it into the object
	 * and then use that data to process anything necessary
	 *
	 * @return void
	 */
	function process_results();
}