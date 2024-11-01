<?php
/**
 * The class responsible for managing plugin options.
 *
 * @link       https://thecoderegistry.com
 * @since      1.0.0
 *
 * @package    TCR_CBI
 * @subpackage TCR_CBI/includes
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * The class responsible for managing plugin options.
 *
 * This class defines all options used throughout the plugin, including
 * API endpoints, keys, and various IDs.
 *
 * @link       https://thecoderegistry.com
 * @since      1.0.0
 * @package    TCR_CBI
 * @subpackage TCR_CBI/includes
 * @author     The Code Registry <support@thecoderegistry.com>
 */
class TCR_CBI_Options {

    /**
     * The array of plugin options.
     *
     * @since    1.0.0
     * @access   private
     * @var      array    $options    The array of plugin options.
     */
    private $options;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     */
    public function __construct() {
        $this->refresh_options();
    }

    /** 
     * Refresh the options from the database.
     *
     * @since 1.0.0
     */
    private function refresh_options() {
        $this->options = get_option('tcr_cbi_options', array());
        $this->set_default_options();
    }

    /**
     * Set default options if they don't exist.
     *
     * @since    1.0.0
     */
    private function set_default_options() {
        $defaults = array(
            'marketplace_api_endpoint' => 'https://app.thecoderegistry.com/api/marketplace',
            'api_key'                  => '',
            'team_id'                  => '',
            'user_id'                  => '',
            'project_id'               => '',
            'code_vault_id'            => '',
            'code_vault_created_date'  => '',
            'code_vault_version'       => '',
            'code_vault_code_synced_date' => '',
            'latest_archive_path'      => '',
            'latest_archive_url'       => '',
            'account_email'            => '',
            'subscription_status'      => '',
        );

        $this->options = wp_parse_args( $this->options, $defaults );
        update_option( 'tcr_cbi_options', $this->options );
    }

    /**
     * Get a specific option value.
     *
     * @since    1.0.0
     * @param    string    $key    The option key.
     * @return   mixed             The option value.
     */
    public function get_option( $key ) {
        $this->refresh_options();
        return isset( $this->options[ $key ] ) ? $this->options[ $key ] : null;
    }

    /**
     * Set a specific option value.
     *
     * @since    1.0.0
     * @param    string    $key      The option key.
     * @param    mixed     $value    The option value.
     */
    public function set_option( $key, $value ) {
        $this->refresh_options();
        $this->options[$key] = $value;
        return update_option('tcr_cbi_options', $this->options);
    }

    /**
     * Get all options.
     *
     * @since    1.0.0
     * @return   array    The array of all options.
     */
    public function get_all_options() {
        $this->refresh_options(); // Refresh before getting
        return $this->options;
    }

    /**
     * Clear all plugin options.
     *
     * @since    1.0.0
     */
    public function clear_all_options() {
        $this->options = array(
            'marketplace_api_endpoint' => 'https://app.thecoderegistry.com/api/marketplace',
            'api_key'                  => '',
            'team_id'                  => '',
            'user_id'                  => '',
            'project_id'               => '',
            'code_vault_id'            => '',
            'code_vault_created_date'  => '',
            'code_vault_version'       => '',
            'code_vault_code_synced_date' => '',
            'latest_archive_path'      => '',
            'latest_archive_url'       => '',
            'account_email'            => '',
            'subscription_status'      => '',
        );
        update_option( 'tcr_cbi_options', $this->options );
    }
}