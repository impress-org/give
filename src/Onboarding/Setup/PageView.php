<?php

/**
 * Onboarding class
 *
 * @package Give
 */

namespace Give\Onboarding\Setup;

defined( 'ABSPATH' ) || exit;

/**
 * @since 2.8.0
 */
class PageView {

	public function render() {
		$settings = wp_parse_args(
			get_option( 'give_onboarding', [] ),
			[
				'addons' => [],
			]
		);
		ob_start();
		include plugin_dir_path( __FILE__ ) . 'templates/index.html.php';
		return ob_get_clean();
	}

	/**
	 * Render templates
	 *
	 * @param string $template
	 * @param array $data The key/value pairs passed as $data are extracted as variables for use within the template file.
	 *
	 * @since 2.8.0
	 */
	public function render_template( $template, $data = [] ) {
		ob_start();
		include plugin_dir_path( __FILE__ ) . "templates/$template.html";
		$output = ob_get_clean();

		foreach ( $data as $key => $value ) {
			if ( is_array( $value ) ) {
				$value = implode( '', $value );
			}
			$output = preg_replace( '/{{\s*' . $key . '\s*}}/', $value, $output );
		}

		// Stripe unmerged tags.
		$output = preg_replace( '/{{\s*.*\s*}}/', '', $output );

		return $output;
	}

	/**
	 * @return bool
	 *
	 * @since 2.8.0
	 */
	public function isStripeSetup() {
		return \Give\Helpers\Gateways\Stripe::isAccountConfigured();
	}

	/**
	 * @return bool
	 *
	 * @since 2.8.0
	 */
	public function isPayPalSetup() {
		return false;
	}

	/**
	 * Returns a qualified image URL.
	 *
	 * @param string $src
	 *
	 * @return string
	 */
	public function image( $src ) {
		return GIVE_PLUGIN_URL . "assets/dist/images/setup-page/$src";
	}

	public function give_link( $href ) {
		return add_query_arg(
			[
				'utm_source'   => 'GiveWPPlugin',
				'utm_medium'   => 'admin',
				'utm_campaign' => 'Plugin_Links',
				'utm_content'  => 'Setup',
			],
			$href
		);
	}
}