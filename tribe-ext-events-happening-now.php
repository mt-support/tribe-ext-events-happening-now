<?php
/**
 * Plugin Name:       The Events Calendar Extension: Events Happening Now
 * Plugin URI:        https://theeventscalendar.com/extensions/events-happening-now/
 * GitHub Plugin URI: https://github.com/mt-support/tribe-ext-events-happening-now
 * Description:       Provides a widget, shortcode, and optional meta for displaying events that are happening now.
 * Version:           1.0.0
 * Extension Class:   Tribe\Extensions\EventsHappeningNow\Main
 * Author:            Modern Tribe, Inc.
 * Author URI:        http://m.tri.be/1971
 * License:           GPL version 3 or any later version
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       tribe-ext-events-happening-now
 *
 *     This plugin is free software: you can redistribute it and/or modify
 *     it under the terms of the GNU General Public License as published by
 *     the Free Software Foundation, either version 3 of the License, or
 *     any later version.
 *
 *     This plugin is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *     GNU General Public License for more details.
 */

namespace Tribe\Extensions\EventsHappeningNow;

use Tribe__Autoloader;
use Tribe__Extension;

/**
 * Define Constants
 */

if ( ! defined( __NAMESPACE__ . '\NS' ) ) {
	define( __NAMESPACE__ . '\NS', __NAMESPACE__ . '\\' );
}

if ( ! defined( NS . 'PLUGIN_TEXT_DOMAIN' ) ) {
	// `Tribe\Extensions\EventsHappeningNow\PLUGIN_TEXT_DOMAIN` is defined
	define( NS . 'PLUGIN_TEXT_DOMAIN', 'tribe-ext-events-happening-now' );
}

// Do not load unless Tribe Common is fully loaded and our class does not yet exist.
if (
	class_exists( 'Tribe__Extension' )
	&& ! class_exists( Main::class )
) {
	/**
	 * Extension main class, class begins loading on init() function.
	 */
	class Main extends Tribe__Extension {
		const VERSION = '1.0.0';

		const PATH = __DIR__;

		/**
		 * @var Tribe__Autoloader
		 */
		private $class_loader;

		/**
		 * @var Plugin Directory
		 */
		public $plugin_dir;

		/**
		 * @var Plugin path
		 */
		public $plugin_path;

		/**
		 * @var Plugin URL
		 */
		public $plugin_url;

		/**
		 * Setup the Extension's properties.
		 *
		 * This always executes even if the required plugins are not present.
		 */
		public function construct() {
			$this->plugin_dir  = trailingslashit( basename( self::PATH ) );
			$this->plugin_path = trailingslashit( self::PATH );
			$this->plugin_url  = plugins_url( $this->plugin_dir, self::PATH );

			$this->add_required_plugin( 'Tribe__Events__Main', '5.0.2' );
		}

		/**
		 * Extension initialization and hooks.
		 */
		public function init() {
			load_plugin_textdomain( PLUGIN_TEXT_DOMAIN, false, basename( dirname( __FILE__ ) ) . '/languages/' );

			if ( ! $this->php_version_check() ) {
				return;
			}

			$this->class_loader();

			tribe_singleton( static::class, $this );
			tribe_register_provider( Service_Provider::class );
		}

		/**
		 * Check if we have a sufficient version of PHP. Admin notice if we don't and user should see it.
		 *
		 * @link https://theeventscalendar.com/knowledgebase/php-version-requirement-changes/ All extensions require PHP 5.6+.
		 *
		 * @return bool
		 */
		private function php_version_check() {
			$php_required_version = '5.6';

			if ( version_compare( PHP_VERSION, $php_required_version, '<' ) ) {
				if (
					is_admin()
					&& current_user_can( 'activate_plugins' )
				) {
					$message = '<p>';

					$message .= sprintf( __( '%s requires PHP version %s or newer to work. Please contact your website host and inquire about updating PHP.', PLUGIN_TEXT_DOMAIN ), $this->get_name(), $php_required_version );

					$message .= sprintf( ' <a href="%1$s">%1$s</a>', 'https://wordpress.org/about/requirements/' );

					$message .= '</p>';

					tribe_notice( PLUGIN_TEXT_DOMAIN . '-php-version', $message, [ 'type' => 'error' ] );
				}

				return false;
			}

			return true;
		}

		/**
		 * Use Tribe Autoloader for all class files within this namespace in the 'src' directory.
		 *
		 * @return Tribe__Autoloader
		 */
		public function class_loader() {
			if ( empty( $this->class_loader ) ) {
				$this->class_loader = new Tribe__Autoloader;
				$this->class_loader->set_dir_separator( '\\' );
				$this->class_loader->register_prefix(
					NS,
					__DIR__ . DIRECTORY_SEPARATOR . 'src/Tribe'
				);
			}

			$this->class_loader->register_autoloader();

			return $this->class_loader;
		}
	}
}
