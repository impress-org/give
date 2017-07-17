<?php
/**
 * Price Functions
 *
 * @package     Give
 * @subpackage  Functions
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Checks to see if a Give form has variable prices enabled.
 *
 * @since 1.0
 *
 * @param int $form_id ID number of the form to check
 *
 * @return bool true if has variable prices, false otherwise
 */
function give_has_variable_prices( $form_id = 0 ) {

	if ( empty( $form_id ) ) {
		return false;
	}

	$form = new Give_Donate_Form( $form_id );

	return $form->has_variable_prices();
}


/**
 * Retrieves the variable prices for a form
 *
 * @since 1.0
 *
 * @param int $form_id ID of the Give form
 *
 * @return array|bool Variable prices
 */
function give_get_variable_prices( $form_id = 0 ) {

	if ( empty( $form_id ) ) {
		return false;
	}

	$form = new Give_Donate_Form( $form_id );

	return $form->prices;

}

/**
 * Retrieves the variable price ids for a form
 *
 * @since 1.8.8
 *
 * @param int $form_id ID of the Give form
 *
 * @return array Variable prices
 */
function give_get_variable_price_ids( $form_id = 0 ) {
	if( ! ( $prices = give_get_variable_prices( $form_id ) ) ) {
		return array();
	}

	$price_ids = array();
	foreach ( $prices as $price ){
		$price_ids[] = $price['_give_id']['level_id'];
	}

	return $price_ids;
}


/**
 * Get the default amount for multi-level forms
 *
 * @access public
 * @since  1.0
 *
 * @param int $form_id
 *
 * @return string $default_price
 */
function give_get_default_multilevel_amount( $form_id ) {
	$default_price         = '';
	$default_custom_amount = give_is_setting_enabled(
		give_get_meta( $form_id, '_give_set_custom_amount_default', true )
	);

	if( ! $default_custom_amount ) {

		$prices = apply_filters(
			'give_form_variable_prices',
			give_get_variable_prices( $form_id ),
			$form_id
		);

		foreach ( $prices as $price ) {

			if ( isset( $price['_give_default'] ) && $price['_give_default'] === 'default' ) {
				$default_price = $price['_give_amount'];
			}

		}
	}

	return $default_price;

}


/**
 * Get Default Form Amount
 *
 * Grabs the default amount for set and level forms
 *
 * @param int $form_id
 *
 * @return string $default_price
 * @since      1.0
 */
function give_get_default_form_amount( $form_id ) {

	if ( give_has_variable_prices( $form_id ) ) {

		$default_amount = give_get_default_multilevel_amount( $form_id );

	} else {

		$default_amount = give_get_meta( $form_id, '_give_set_price', true );

	}

	return apply_filters( 'give_default_form_amount', $default_amount );

}


/**
 * Determine if custom price mode is enabled or disabled.
 *
 * This function is wrapper function to Give_Donate_Form::is_custom_price_mode()
 *
 * @since 1.6
 *
 * @param int $form_id Form ID.
 *
 * @use   Give_Donate_Form::is_custom_price_mode()
 *
 * @return bool
 */
function give_is_custom_price_mode( $form_id = 0 ) {

	if ( empty( $form_id ) ) {
		return false;
	}

	$form = new Give_Donate_Form( $form_id );

	return $form->is_custom_price_mode();
}