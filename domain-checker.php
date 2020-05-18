<?php
/**
 * Plugin Name: Domain Checker
 * Plugin URI: https://wahyuwibowo.com/projects/domain-checker/
 * Description: Check domain name availability from your WordPress site.
 * Author: Wahyu Wibowo
 * Author URI: https://wahyuwibowo.com
 * Version: 1.0
 * Text Domain: domain-checker
 * Domain Path: languages
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

class Domain_Checker {
    
    private static $_instance = NULL;
    
    /**
     * Initialize all variables, filters and actions
     */
    public function __construct() {
        add_action( 'init',               array( $this, 'load_plugin_textdomain' ), 0 );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        add_filter( 'http_request_args',  array( $this, 'dont_update_plugin' ), 5, 2 );
        
        add_shortcode( 'down_payment_calculator', array( $this, 'add_shortcode' ) );
    }
    
    /**
     * retrieve singleton class instance
     * @return instance reference to plugin
     */
    public static function instance() {
        if ( NULL === self::$_instance ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    public function load_plugin_textdomain() {
        $locale = is_admin() && function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale();
        $locale = apply_filters( 'plugin_locale', $locale, 'domain-checker' );
        
        unload_textdomain( 'domain-checker' );
        load_textdomain( 'domain-checker', WP_LANG_DIR . '/domain-checker/domain-checker-' . $locale . '.mo' );
        load_plugin_textdomain( 'domain-checker', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );
    }
    
    public function dont_update_plugin( $r, $url ) {
        if ( 0 !== strpos( $url, 'https://api.wordpress.org/plugins/update-check/1.1/' ) ) {
            return $r; // Not a plugin update request. Bail immediately.
        }
        
        $plugins = json_decode( $r['body']['plugins'], true );
        unset( $plugins['plugins'][plugin_basename( __FILE__ )] );
        $r['body']['plugins'] = json_encode( $plugins );
        
        return $r;
    }
    
    public function enqueue_scripts() {
        wp_enqueue_script( 'frontend', plugin_dir_url( __FILE__ ) . 'assets/js/frontend.js', array( 'jquery' ) );
        wp_enqueue_style( 'frontend', plugin_dir_url( __FILE__ ) . 'assets/css/frontend.css' );
        
        wp_localize_script( 'frontend', 'Domain_Checker', array(
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'domain_checker' ),
            'loading' => __( 'Loading...', 'domain-checker' )
        ) );
    }
    
    public function add_shortcode() {
        $output = 'calculator';
        
        return $output;
    }

}

Domain_Checker::instance();
