<?php
/**
 * JENTIS - simply better data
 *
 * @package       JENTISPLUGIN
 * @author        Jentis
 * @version       1.0.1
 *
 * @wordpress-plugin
 * Plugin Name:   JENTIS - simply better data
 * Plugin URI:    https://www.jentis.com/article/plugin-woocommerce/
 * Description:   Capture higher-quality data and become privacy compliant – without changing your tech stack.
 * Version:       1.0.1
 * Requires at least: 5.2
 * Requires PHP:  7.2
 * Author:        JENTIS GmbH
 * Author URI:    https://jentis.com
 * Text Domain:   jentis
 * License:       GPL v2 or later
 * License URI:   https://www.gnu.org/licenses/gpl-2.0.html
 * Domain Path:   /languages
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
};

/**
 * Define plugin constants
 */
define( 'JENTISPLUGIN_PATH', trailingslashit( plugin_dir_path( __FILE__ ) ) );
define( 'JENTISPLUGIN_URL', trailingslashit( plugins_url( '/', __FILE__ ) ) );    

// Require woocommerce admin message
function jentis_wc_requirement_notice() {

    if ( ! class_exists( 'WooCommerce' ) ) {
        $text    = esc_html__( 'WooCommerce', 'jentis' );
        $link    = esc_url( add_query_arg( array(
            'tab'       => 'plugin-information',
            'plugin'    => 'woocommerce',
            'TB_iframe' => 'true',
            'width'     => '640',
            'height'    => '500',
        ), admin_url( 'plugin-install.php' ) ) );
        $message = wp_kses( __( "<strong>Jentis for WooCommerce</strong> is an add-on of ", 'jentis' ), array( 'strong' => array() ) );

        printf( '<div class="%1$s"><p>%2$s <a class="thickbox open-plugin-details-modal" href="%3$s"><strong>%4$s</strong></a></p></div>', 'notice notice-error', $message, $link, $text );
    }
}

add_action( 'admin_notices', 'jentis_wc_requirement_notice' );

if ( ! class_exists( 'Jentis_Tracking' ) ):    
    /**
     * Jentis Tracking class
     */
    class Jentis_Tracking {

        public static $instance;

        /** Initializing the tracking */
        public static function init() {
            if ( is_null( self::$instance ) ) {
                self::$instance = new Jentis_Tracking();
            }
            return self::$instance;
        }
        /**
         * Constructor
         */
        private function __construct() {
            /**
             * Loading the integration
             */
            add_action( 'plugins_loaded', array( $this, 'load_integrations' ) );
        }

        /**
         * Load integration classes
         */
        public static function load_integrations() {
            if ( class_exists( 'WooCommerce' ) ) {
                if (class_exists( 'WC_Integration' ) ) {
                    // load our integration class.
                    include_once JENTISPLUGIN_PATH . '/inc/settings/wc-integration-jentis-tracking.php';
    
                    // add to the WooCommerce settings page.
                    add_filter( 'woocommerce_integrations', __CLASS__ . '::add_integration' );
                }
            }            
        }
        

        /**
         * Add integration settings pages
         */
        public static function add_integration( $integrations ) {
            $integrations[] = 'WC_Integration_Jentis_Plugin';
            return $integrations;
        }
    }    
    //Creating jentis tracking class.
    $jentis_tracking = Jentis_Tracking::init();
    
endif;



