<?php
/**
 * Plugin Name: Domain Checker
 * Plugin URI: https://wahyuwibowo.com/projects/domain-checker/
 * Description: Check domain name availability from your WordPress site using Namecheap API.
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
    private $default_options = NULL;
    
    /**
     * Initialize all variables, filters and actions
     */
    public function __construct() {
        $this->default_options = array(
            'namecheap_username' => '',
            'namecheap_apiuser'  => '',
            'namecheap_apikey'   => '',
            'namecheap_sandbox'  => ''
        );
        
        add_action( 'admin_init',         array( $this, 'settings_init' ) );
        add_action( 'admin_menu',         array( $this, 'admin_menu' ) );
        add_action( 'init',               array( $this, 'load_plugin_textdomain' ), 0 );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        add_filter( 'http_request_args',  array( $this, 'dont_update_plugin' ), 5, 2 );
        
        add_shortcode( 'domain_checker', array( $this, 'add_shortcode' ) );
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
    
    public function admin_menu() {
        add_options_page( __( 'Domain Checker Settings', 'domain-checker' ), __( 'Domain Checker', 'domain-checker' ), 'manage_options', 'domain-checker', array( $this, 'admin_page' ) );
    }
    
    public function admin_page() {
        $options = get_option( 'domain_checker_options', $this->default_options );
        ?>
        <div class="wrap">
            <h2><?php _e( 'Domain Checker Settings', 'domain-checker' );?></h2>
            <form method="post" action="options.php">
                <?php settings_fields( 'domain_checker_options' ); ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><?php echo esc_html__( 'Namecheap Username', 'domain-checker' );?></th>
                        <td class="forminp"><input type="text" name="domain_checker_options[namecheap_username]" value="<?php echo esc_attr( $options['namecheap_username'] ) ?>" class="regular-text"></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php echo esc_html__( 'Namecheap API User', 'domain-checker' );?></th>
                        <td class="forminp"><input type="text" name="domain_checker_options[namecheap_apiuser]" value="<?php echo esc_attr( $options['namecheap_apiuser'] ) ?>" class="regular-text"></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php echo esc_html__( 'Namecheap API Key', 'domain-checker' );?></th>
                        <td class="forminp"><input type="text" name="domain_checker_options[namecheap_apikey]" value="<?php echo esc_attr( $options['namecheap_apikey'] ) ?>" class="regular-text"></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php echo esc_html__( 'Sandbox Environment', 'domain-checker' );?></th>
                        <td class="forminp">
                            <p><label for="sandbox"><input id="sandbox" type="checkbox" name="domain_checker_options[namecheap_sandbox]" value="Y" <?php checked( 'Y', $options['namecheap_sandbox'] ) ?>> 
                                <?php echo esc_html__( 'Use Namecheap Sandbox Environment.', 'domain-checker' ) ?></label></p>
                        </td>
                    </tr>
                </table>
                <p class="submit">
                    <input type="submit" class="button-primary" value="<?php echo esc_attr__( 'Save Changes', 'domain-checker' ); ?>" />
                </p>
            </form>
        </div>
        <?php
    }
    
    public function settings_init() {
        register_setting( 'domain_checker_options', 'domain_checker_options', array( $this, 'settings_sanitize' ) );
    }
    
    public function settings_sanitize( $input ) {
        $options = get_option( 'domain_checker_options', $this->default_options );
        $keys = array( 'namecheap_username', 'namecheap_apiuser', 'namecheap_apikey' );
        
        foreach ( $keys as $key ) {
            $options[$key] = sanitize_text_field( $input[$key] );
        }
        
        return $options;
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
        wp_enqueue_script( 'dc-frontend', plugin_dir_url( __FILE__ ) . 'assets/js/frontend.js', array( 'jquery' ) );
        wp_enqueue_style( 'dc-frontend', plugin_dir_url( __FILE__ ) . 'assets/css/frontend.css' );
        
        wp_localize_script( 'dc-frontend', 'Domain_Checker', array(
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'domain_checker' ),
            'loading' => __( 'Loading...', 'domain-checker' )
        ) );
    }
    
    public function add_shortcode() {
        $output = 'domain checker';
        
        return $output;
    }

}

Domain_Checker::instance();
