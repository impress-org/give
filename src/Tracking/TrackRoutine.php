<?php
namespace Give\Tracking;

use Give\Helpers\ArrayDataSet;
use Give\Tracking\Track;
use Give\Tracking\TrackingData\DonationData;
use Give\Tracking\TrackingData\DonationFormData;
use Give\Tracking\TrackingData\DonationFormsData;
use Give\Tracking\TrackingData\DonorData;
use WP_Upgrader;

/**
 * Class TrackRoutine
 *
 * @since 2.10.0
 * @package Give\Tracking
 */
class TrackRoutine {
	const LAST_REQUEST_OPTION_NAME = 'give_usage_tracking_last_request';

	/**
	 * The limit for the option.
	 *
	 * @var int
	 */
	protected $threshold = WEEK_IN_SECONDS * 2;

	/**
	 * Schedules a new sending of the tracking data after a WordPress core update.
	 *
	 * @param  bool|WP_Upgrader  $upgrader
	 * @param  array  $data  Array of update data.
	 *
	 * @return void
	 *@since 2.10.0
	 *
	 */
	public function scheduleTrackingDataSending( $upgrader = false, $data = [] ) {
		// Return if it's not a WordPress core update.
		if ( ! $upgrader || ! isset( $data['type'] ) || ! in_array( $data['type'], [ 'core', 'plugin' ] ) ) {
			return;
		}

		if ( ! wp_next_scheduled( 'give_send_tracking_data_routine_job', true ) ) {
			wp_schedule_single_event( ( time() + ( HOUR_IN_SECONDS * 6 ) ), 'give_send_tracking_data_routine_job', true );
		}
	}

	/**
	 * Sends the tracking data.
	 *
	 * @since 2.10.0
	 *
	 * @param  bool  $force  Whether to send the tracking data ignoring the two
	 *                    weeks time threshold. Default false.
	 */
	public function send( $force = false ) {
		if ( ! $this->shouldSendTracking( $force ) ) {
			return;
		}

		do_action( 'give_send_tracking_data' );

		$newDonationIds = $this->getNewDonationIdsSinceLastRequest();

		if ( ! $newDonationIds ) {
			return;
		}

		$trackClient       = new TrackClient();
		$donorData         = new DonorData();
		$donationFormData  = new DonationFormData();
		$donationFormsData = new DonationFormsData();
		$donationData      = new DonationData();

		$donationFormsData->setDonationIds( $newDonationIds )->setFormIdsByDonationIds();

		$trackingData['donor']    = $donorData->get();
		$trackingData['form']     = $donationFormData->get();
		$trackingData['forms']    = $donationFormsData->get();
		$trackingData['donation'] = $donationData->get();

		$trackClient->send( 'track-routine', $trackingData );
		update_option( self::LAST_REQUEST_OPTION_NAME, time() );
	}

	/**
	 * Determines whether to send the tracking data.
	 *
	 * @since 2.10.0
	 *
	 * @param  bool  $ignore_time_threshold  Whether to send the tracking data ignoring the two weeks time threshold. Default false.
	 *
	 * @return bool True when tracking data should be sent.
	 */
	private function shouldSendTracking( $ignore_time_threshold = false ) {
		// Only send tracking on the main site of a multi-site instance. This returns true on non-multisite installs.
		if ( ! is_main_site() ) {
			return false;
		}

		$lastTime = get_option( self::LAST_REQUEST_OPTION_NAME );

		// When tracking data haven't been sent yet or when sending data is forced.
		if ( ! $lastTime || $ignore_time_threshold ) {
			return true;
		}

		return $this->exceedsThreshold( time() - $lastTime );
	}

	/**
	 * Checks if the given amount of seconds exceeds the set threshold.
	 *
	 * @since 2.10.0
	 *
	 * @param  int  $seconds  The amount of seconds to check.
	 *
	 * @return bool True when seconds is bigger than threshold.
	 */
	private function exceedsThreshold( $seconds ) {
		return ( $seconds > $this->threshold );
	}

	/**
	 * Return whether or not website get donation after last tracked request date.
	 *
	 * @sicne 2.10.0
	 * @return array
	 */
	private function getNewDonationIdsSinceLastRequest() {
		global $wpdb;

		$statues = ArrayDataSet::getStringSeparatedByCommaEnclosedWithSingleQuote(
			[
				'publish', // One time donation
				'give_subscription', // Renewal
			]
		);
		$time    = date( 'Y-m-d H:i:s', get_option( self::LAST_REQUEST_OPTION_NAME, time() ) );

		return $wpdb->get_col(
			"
				SELECT ID
				FROM {$wpdb->posts}
				WHERE post_date_gmt >= '{$time}'
				AND post_status IN ({$statues})
				AND post_type='give_payment'
				"
		);
	}
}