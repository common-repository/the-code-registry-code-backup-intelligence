<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://thecoderegistry.com
 * @since      1.0.0
 *
 * @package    TCR_CBI
 * @subpackage TCR_CBI/admin
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    TCR_CBI
 * @subpackage TCR_CBI/admin
 * @author     The Code Registry <support@thecoderegistry.com>
 */
class TCR_CBI_Admin {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * The options instance.
     *
     * @since    1.0.0
     * @access   private
     * @var      TCR_CBI_Options    $options    The options instance.
     */
    private $options;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param    string    $plugin_name       The name of this plugin.
     * @param    string    $version    The version of this plugin.
     */
    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->options = new TCR_CBI_Options();
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        $screen = get_current_screen();
        if ( $screen && ( strpos( $screen->id, 'code-intelligence' ) !== false || strpos( $screen->id, 'code-vault' ) !== false || strpos( $screen->id, 'data-and-tools' ) !== false ) ) {
            wp_enqueue_style( 'code-intelligence', TCR_CBI_PLUGIN_URL . 'admin/css/dist/app.css', array(), TCR_CBI_VERSION, 'all' );
        }
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        $screen = get_current_screen();
        if ( $screen && ( strpos( $screen->id, 'code-intelligence' ) !== false || strpos( $screen->id, 'code-vault' ) !== false || strpos( $screen->id, 'data-and-tools' ) !== false ) ) {
            wp_enqueue_script( 'code-intelligence', TCR_CBI_PLUGIN_URL . 'admin/js/dist/main.js', array(), TCR_CBI_VERSION, true );
        
            $current_user = wp_get_current_user();
            $options = new TCR_CBI_Options();
            
            // Debug to force archive recreation
            // $options->set_option( 'code_vault_id', '' );
            // $options->set_option( 'latest_archive_path', '' );
            // $options->set_option( 'latest_archive_url', '' );

            // Debug to reset all options
            // $options->set_option( 'api_key', '' );
            // $options->set_option( 'user_id', '' );
            // $options->set_option( 'team_id', '' );
            // $options->set_option( 'project_id', '' );
            // $options->set_option( 'code_vault_id', '' );
            // $options->set_option( 'code_vault_created_date', '' );
            // $options->set_option( 'code_vault_version', '' );
            // $options->set_option( 'code_vault_code_synced_date', '' );
            // $options->set_option( 'latest_archive_path', '' );
            // $options->set_option( 'latest_archive_url', '' );
            
            wp_localize_script( 'code-intelligence', 'codeIntelligenceData', array(
                'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                'nonce' => wp_create_nonce( 'tcr_cbi_nonce' ),
                'pluginUrl' => TCR_CBI_PLUGIN_URL,
                'siteName' => get_bloginfo( 'name' ),
                'userProfileName' => $current_user->display_name,
                'userProfileEmail' => $current_user->user_email,
                'apiKey' => $options->get_option( 'api_key' ),
                'userId' => $options->get_option( 'user_id' ),
                'teamId' => $options->get_option( 'team_id' ),
                'projectId' => $options->get_option( 'project_id' ),
                'codeVaultId' => $options->get_option( 'code_vault_id' ),
                'codeVaultCreatedDate' => $options->get_option( 'code_vault_created_date' ),
                'codeVaultVersion' => $options->get_option( 'code_vault_version' ),
                'codeVaultCodeSyncedDate' => $options->get_option( 'code_vault_code_synced_date' ),
                'latestArchivePath' => $options->get_option( 'latest_archive_path' ),
                'latestArchiveUrl' => $options->get_option( 'latest_archive_url' ),
                'subscriptionStatus' => $options->get_option( 'subscription_status' ),
            ));
        }

        if ( $screen && $screen->id === 'code-backup-intelligence_page_data-and-tools' ) {
            wp_enqueue_script( 'code-intelligence-admin-options', TCR_CBI_PLUGIN_URL . 'admin/js/admin-options.js', array('jquery'), TCR_CBI_VERSION, true );
        }
    }

    /**
     * Add menu item
     *
     * @since    1.0.0
     */
    public function add_plugin_admin_menu() {
        add_menu_page(
            'Code Backup & Intelligence',
            'Code Backup & Intelligence',
            'manage_options',
            'code-intelligence',
            array( $this, 'display_plugin_dashboard_page' ),
            'dashicons-code-standards',
            2
        );

        add_submenu_page(
            'code-intelligence',
            'Code Intelligence',
            'Code Intelligence',
            'manage_options',
            'code-intelligence',
            array( $this, 'display_plugin_dashboard_page' )
        );

        add_submenu_page(
            'code-intelligence',
            'Code Vault',
            'Code Vault',
            'manage_options',
            'code-vault',
            array( $this, 'display_plugin_vault_page' )
        );

        add_submenu_page(
            'code-intelligence',
            'Data & Tools',
            'Data & Tools',
            'manage_options',
            'data-and-tools',
            array( $this, 'display_plugin_options_page' )
        );
    }

    /**
     * Render the dashboard page for plugin
     *
     * @since  1.0.0
     */
    public function display_plugin_dashboard_page() {
        require_once TCR_CBI_PLUGIN_DIR . 'admin/partials/code-intelligence-admin-dashboard-display.php';
    }

    /** 
     * Render the vault page for plugin
     *
     * @since  1.0.0
     */
    public function display_plugin_vault_page() {
        require_once TCR_CBI_PLUGIN_DIR . 'admin/partials/code-intelligence-admin-vault-display.php';
    }

    /**
     * Render the options page for plugin
     *
     * @since  1.0.0
     */
    public function display_plugin_options_page() {
        include_once 'partials/code-intelligence-admin-options-display.php';
    }

    /**
     * Register settings
     *
     * @since  1.0.0
     */
    public function register_settings() {
        register_setting( 'tcr_cbi_options', 'tcr_cbi_options', array( $this, 'sanitize_options' ) );
    }

    /**
     * Sanitize options
     *
     * @since  1.0.0
     * @param  array $input The input options.
     * @return array        The sanitized options.
     */
    public function sanitize_options( $input ) {
        $sanitized_input = array();

        if ( isset( $input['marketplace_api_endpoint'] ) ) {
            $sanitized_input['marketplace_api_endpoint'] = esc_url_raw( $input['marketplace_api_endpoint'] );
        }

        if ( isset( $input['api_key'] ) ) {
            $sanitized_input['api_key'] = sanitize_text_field( $input['api_key'] );
        }

        if ( isset( $input['team_id'] ) ) {
            $sanitized_input['team_id'] = sanitize_text_field( $input['team_id'] );
        }

        if ( isset( $input['user_id'] ) ) {
            $sanitized_input['user_id'] = sanitize_text_field( $input['user_id'] );
        }

        if ( isset( $input['project_id'] ) ) {
            $sanitized_input['project_id'] = sanitize_text_field( $input['project_id'] );
        }

        if ( isset( $input['code_vault_id'] ) ) {
            $sanitized_input['code_vault_id'] = sanitize_text_field( $input['code_vault_id'] );
        }

        if ( isset( $input['latest_archive_path'] ) ) {
            $sanitized_input['latest_archive_path'] = sanitize_text_field( $input['latest_archive_path'] );
        }

        if ( isset( $input['latest_archive_url'] ) ) {
            $sanitized_input['latest_archive_url'] = sanitize_text_field( $input['latest_archive_url'] );
        }

        if ( isset( $input['code_vault_created_date'] ) ) {
            $sanitized_input['code_vault_created_date'] = sanitize_text_field( $input['code_vault_created_date'] );
        }

        if ( isset( $input['code_vault_version'] ) ) {
            $sanitized_input['code_vault_version'] = sanitize_text_field( $input['code_vault_version'] );
        }

        if ( isset( $input['code_vault_code_synced_date'] ) ) {
            $sanitized_input['code_vault_code_synced_date'] = sanitize_text_field( $input['code_vault_code_synced_date'] );
        }

        if ( isset( $input['account_email'] ) ) {
            $sanitized_input['account_email'] = sanitize_text_field( $input['account_email'] );
        }

        if ( isset( $input['subscription_status'] ) ) {
            $sanitized_input['subscription_status'] = sanitize_text_field( $input['subscription_status'] );
        }

        return $sanitized_input;
    }

    /**
     * Handle account deletion.
     *
     * @since    1.0.0
     */
    public function handle_account_deletion() {
        if ( 
            ! isset( $_POST['tcr_cbi_delete_account_nonce'] ) || 
            ! wp_verify_nonce( 
                sanitize_text_field( wp_unslash( $_POST['tcr_cbi_delete_account_nonce'] ) ), 
                'tcr_cbi_delete_account' 
            ) 
        ) {
            wp_die( 'Invalid nonce. Please try again.' );
        }
    
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'You do not have sufficient permissions to perform this action.' );
        }
    
        $user_id = $this->options->get_option( 'user_id' );
        $api_client = new TCR_CBI_API_Client( $this->options );
    
        $response = $api_client->delete_account( $user_id );
    
        if ( is_wp_error( $response ) ) {
            add_settings_error(
                'tcr_cbi_messages',
                'tcr_cbi_account_deletion_error',
                'Error deleting account: ' . esc_html($response->get_error_message()),
                'error'
            );
        } else {
            // Clear all plugin options
            $this->options->clear_all_options();
    
            add_settings_error(
                'tcr_cbi_messages',
                'tcr_cbi_account_deleted',
                'Your account has been successfully deleted.',
                'updated'
            );
        }
    
        // Redirect back to the plugin dashboard
        wp_safe_redirect( admin_url( 'admin.php?page=code-intelligence' ) );
        exit;
    }    

    /**
     * Add action to handle account deletion.
     *
     * @since    1.0.0
     */
    public function add_account_deletion_action() {
        add_action( 'admin_post_tcr_cbi_delete_account', array( $this, 'handle_account_deletion' ) );
    }
}