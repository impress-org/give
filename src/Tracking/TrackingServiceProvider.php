<?php
namespace Give\Tracking;

use Give\Helpers\Hooks;
use Give\ServiceProviders\ServiceProvider;
use Give\Tracking\Contracts\TrackEvent;
use Give\Tracking\Events\DonationFormsTracking;
use Give\Tracking\Events\DonationMetricsTracking;
use Give\Tracking\Events\GivePluginSettingsTracking;
use Give\Tracking\Events\PluginsTracking;
use Give\Tracking\Events\ThemeTracking;
use Give\Tracking\Events\WebsiteTracking;
use Give\Tracking\Helpers\Track as TrackHelper;

/**
 * Class TrackingServiceProvider
 * @package Give\Tracking
 *
 * @since 2.10.0
 */
class TrackingServiceProvider implements ServiceProvider {
	/**
	 * @var TrackEvent[]
	 */
	private $trackingEvents = [
		DonationMetricsTracking::class,
		DonationFormsTracking::class,
		WebsiteTracking::class,
	];

	/**
	 * @inheritdoc
	 */
	public function register() {
		give()->singleton( Track::class );
	}

	/**
	 * @inheritdoc
	 */
	public function boot() {
		Hooks::addAction( 'shutdown', Track::class, 'send' );

		if ( ! TrackHelper::isTrackingEnabled() ) {
			// Send tracking data on `admin_init`.
			Hooks::addAction( 'admin_init', TrackRoutine::class, 'send', 1 );

			// Call `wp_schedule_single_event()` after a WordPress core update.
			Hooks::addAction( 'upgrader_process_complete', TrackRoutine::class, 'scheduleTrackingDataSending', 10, 2 );
		}

		if ( is_admin() ) {
			Hooks::addFilter( 'give_get_settings_advanced', AdminSettings::class, 'addSettings' );
			Hooks::addAction( 'give_opt_in_into_tracking', AdminActionHandler::class, 'optInToUsageTracking' );
			Hooks::addAction( 'give_hide_opt_in_notice_shortly', AdminActionHandler::class, 'optOutFromUsageTracking' );
			Hooks::addAction( 'give_hide_opt_in_notice_permanently', AdminActionHandler::class, 'optOutFromUsageTracking' );
			Hooks::addAction( 'update_option_give_settings', AdminActionHandler::class, 'optInToUsageTrackingAdminGrantManually', 10, 2 );
			Hooks::addAction( 'admin_notices', UsageTrackingOnBoarding::class, 'addNotice' );
			Hooks::addAction( 'give_setup_page_before_sections', UsageTrackingOnBoarding::class, 'addNotice', 0 );

			// Register track events.
			Hooks::addAction( 'update_option_give_settings', GivePluginSettingsTracking::class, 'record' );
			Hooks::addAction( 'update_option_active_plugins', PluginsTracking::class, 'record' );
			Hooks::addAction( 'switch_theme', ThemeTracking::class, 'record' );
		}

		Hooks::addAction( 'upgrader_process_complete', ThemeTracking::class, 'themeUpdateTrackingHandler', 10, 2 );
		$this->registerTrackEvents();
	}

	/**
	 * Register track events.
	 *
	 * 'give_send_tracking_data' action hook that will be triggered track routine cron job.
	 *
	 * @since 2.10.0
	 */
	private function registerTrackEvents() {
		foreach ( $this->trackingEvents as $eventClassName ) {
			Hooks::addAction( 'give_send_tracking_data', $eventClassName, 'record' );
		}
	}
}