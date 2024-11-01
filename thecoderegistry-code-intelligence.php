<?php
/**
 * Plugin Name: The Code Registry - Code Backup & Intelligence
 * Plugin URI: https://thecoderegistry.com/how-it-works/
 * Description: Backup your site's code and analyze it for security vulnerabilities, code complexity, third-party component usage, licensing issues, and code quality. Integrate your WordPress site with The Code Registry's code intelligence and analysis service for comprehensive code insights.
 * Version: 1.0.9
 * Author: The Code Registry
 * Author URI: https://thecoderegistry.com
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: the-code-registry-code-backup-intelligence
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Define plugin constants
define( 'TCR_CBI_VERSION', '1.0.9' );
define( 'TCR_CBI_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'TCR_CBI_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'TCR_CBI_TEXT_DOMAIN', 'the-code-registry-code-backup-intelligence' );

/**
 * The core plugin class.
 */
require_once TCR_CBI_PLUGIN_DIR . 'includes/class-code-intelligence.php';

/**
 * The options management class.
 */
require_once TCR_CBI_PLUGIN_DIR . 'includes/class-code-intelligence-options.php';

/**
 * The API client class.
 */
require_once TCR_CBI_PLUGIN_DIR . 'includes/class-code-intelligence-api-client.php';

// Load the CLI commands if WP-CLI is available
require_once TCR_CBI_PLUGIN_DIR . 'includes/class-code-intelligence-cli.php';

/**
 * Begins execution of the plugin.
 *
 * @since 1.0.0
 */
function tcr_cbi_run() {
    $plugin = new TCR_CBI();
    $plugin->run();

    // Add account deletion action
    $plugin_admin = new TCR_CBI_Admin( TCR_CBI_TEXT_DOMAIN, TCR_CBI_VERSION );
    $plugin_admin->add_account_deletion_action();
}
tcr_cbi_run();

/**
 * Initialize the options.
 */
function tcr_cbi_initialize_options() {
    $options = new TCR_CBI_Options();
}
add_action( 'plugins_loaded', 'tcr_cbi_initialize_options' );

/**
 * Schedule the archive recreation cron job.
 */
function tcr_cbi_schedule_archive_recreation() {
    if ( ! wp_next_scheduled( 'tcr_cbi_recreate_archive' ) ) {
        $options = new TCR_CBI_Options();
        $code_vault_created_date = $options->get_option('code_vault_created_date');
        
        if ( $code_vault_created_date ) {
            $created_date = new DateTime($code_vault_created_date);
            $now = new DateTime();
            $next_sync = clone $created_date;
            
            while ($next_sync <= $now) {
                $next_sync->modify('+1 month');
            }
            
            // Schedule the job for one day before the next sync
            $next_run = $next_sync->modify('-1 day')->getTimestamp();
            
            wp_schedule_event( $next_run, 'monthly', 'tcr_cbi_recreate_archive' );
        }
    }
}
add_action( 'wp', 'tcr_cbi_schedule_archive_recreation' );

/**
 * Recreate the code archive.
 */
function tcr_cbi_recreate_archive() {
    $archive_path = tcr_cbi_create_archive();
    if ( ! is_wp_error( $archive_path ) ) {
        update_option( 'tcr_cbi_archive_last_created', current_time( 'mysql' ) );
    }
}
add_action( 'tcr_cbi_recreate_archive', 'tcr_cbi_recreate_archive' );

/**
 * Trigger the cron job via a GET parameter for testing purposes.
 */
function tcr_cbi_maybe_trigger_cron() {
    if (
        current_user_can('manage_options') &&
        isset($_GET['tcr_cbi_trigger_cron']) &&
        $_GET['tcr_cbi_trigger_cron'] === 'true' &&
        isset($_GET['tcr_cbi_nonce']) &&
        wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['tcr_cbi_nonce'])), 'tcr_cbi_trigger_cron')
    ) {
        do_action('tcr_cbi_recreate_archive');
        wp_die('Cron job triggered successfully. Archive recreation process has started.');
    }
}
add_action('admin_init', 'tcr_cbi_maybe_trigger_cron');

/**
 * Disable Elementor scripts on our pages due to capatibility with Tailwind
 */
function code_intelligence_disable_elementor_scripts($hook) {
    if (strpos($hook, 'code-backup-intelligence') !== false || strpos($hook, 'code-intelligence') !== false) {
        wp_dequeue_script('elementor-frontend');
        wp_deregister_script('elementor-frontend');
        wp_dequeue_script('elementor-preview');
        wp_deregister_script('elementor-preview');
        wp_dequeue_script('elementor-editor');
        wp_deregister_script('elementor-editor');
        wp_dequeue_script('elementor-widgets');
        wp_deregister_script('elementor-widgets');
        wp_dequeue_script('elementor-pro');
        wp_deregister_script('elementor-pro');
        wp_dequeue_script('elementor-pro-frontend');
        wp_deregister_script('elementor-pro-frontend');
        wp_dequeue_script('elementor-pro-preview');
        wp_deregister_script('elementor-pro-preview');
        wp_dequeue_script('elementor-pro-editor');
        wp_deregister_script('elementor-pro-editor');
        wp_dequeue_script('elementor-pro-widgets');
        wp_deregister_script('elementor-pro-widgets');
        wp_dequeue_script('elementor-app');
        wp_deregister_script('elementor-app');
        wp_dequeue_script('elementor-common');
        wp_deregister_script('elementor-common');
        wp_dequeue_script('elementor-admin');
        wp_deregister_script('elementor-admin');
        wp_dequeue_script('elementor-import-export-admin');
        wp_deregister_script('elementor-import-export-admin');
        wp_dequeue_script('elementor-sticky');
        wp_deregister_script('elementor-sticky');
        wp_dequeue_script('elementor-pro-frontend');
        wp_deregister_script('elementor-pro-frontend');
        wp_dequeue_script('elementor-common-modules');
        wp_deregister_script('elementor-common-modules');
        wp_deregister_script('backbone-marionette');
        wp_dequeue_script('backbone-marionette');
        wp_dequeue_script('media-hints');
        wp_deregister_script('media-hints');

        wp_dequeue_style('elementor-icons');
        wp_deregister_style('elementor-icons');
        wp_dequeue_style('elementor-common');
        wp_deregister_style('elementor-common');
        wp_dequeue_style('elementor-admin');
        wp_deregister_style('elementor-admin');
        wp_dequeue_style('e-theme-ui-light');
        wp_deregister_style('e-theme-ui-light');
    }
}
add_action('admin_enqueue_scripts', 'code_intelligence_disable_elementor_scripts', 100);

/**
 * The AJAX call handlers.
 */
require_once TCR_CBI_PLUGIN_DIR . 'includes/code-intelligence-ajax-handlers.php';
