<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       http://store.webbycrown.com/
 * @since      1.0.0
 *
 * @package    Webbycrown
 * @subpackage Webbycrown/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Webbycrown
 * @subpackage Webbycrown/includes
 * @author     WebbyCrown Solutions WordPress Team <info@webbycrown.com>
 */
class Webbycrown_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'webbycrown',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
