<?php
/**
 * The class responsible for API interactions.
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
 * The class responsible for API interactions.
 *
 * This class defines all code necessary to interact with the Marketplace API.
 *
 * @since      1.0.0
 * @package    TCR_CBI
 * @subpackage TCR_CBI/includes
 * @author     The Code Registry <support@thecoderegistry.com>
 */
class TCR_CBI_API_Client {

    /**
     * The options instance.
     *
     * @since    1.0.0
     * @access   private
     * @var      TCR_CBI_Options    $options    The options instance.
     */
    private $options;

    /**
     * The timeout for API requests in seconds.
     *
     * @since    1.0.0
     * @access   private
     * @var      int    $timeout    The timeout for API requests.
     */
    private $timeout = 30;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param    TCR_CBI_Options    $options    The options instance.
     */
    public function __construct( $options ) {
        $this->options = $options;
    }

    /**
     * Make an API request.
     *
     * @since    1.0.0
     * @param    string    $endpoint    The API endpoint.
     * @param    string    $method      The HTTP method (GET, POST, etc.).
     * @param    array     $data        The data to send with the request.
     * @return   array|WP_Error         The API response or WP_Error on failure.
     */
    private function make_request( $endpoint, $method = 'GET', $data = array() ) {
        $api_url = $this->options->get_option( 'marketplace_api_endpoint' ) . $endpoint;
        $api_key = $this->options->get_option( 'api_key' );

        $args = array(
            'method'  => $method,
            'headers' => array(
                'X-API-Key' => $api_key,
                'Content-Type' => 'application/json',
            ),
            'timeout' => $this->timeout,
        );

        if ( ! empty( $data ) ) {
            $args['body'] = wp_json_encode( $data );
        }

        // Log the request
        $this->log_api_interaction( 'Request', $api_url, $args );

        $response = wp_remote_request( $api_url, $args );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $body = wp_remote_retrieve_body( $response );
        $decoded_body = json_decode( $body, true );

        // Log the response
        $this->log_api_interaction( 'Response', $api_url, $response );

        if ( json_last_error() !== JSON_ERROR_NONE ) {
            return new WP_Error( 'json_decode_error', 'Failed to decode JSON response', array( 'body' => $body ) );
        }

        return $decoded_body;
    }

    /**
     * Get account information.
     *
     * @since    1.0.0
     * @param    string    $email    The email address of the account.
     * @return   array|WP_Error      The account information or WP_Error on failure.
     */
    public function get_account_info( $email ) {
        return $this->make_request( '/accounts?email=' . urlencode( $email ) );
    }

    /**
     * Create a new account.
     *
     * @since    1.0.0
     * @param    array    $account_data    The account data.
     * @return   array|WP_Error            The created account information or WP_Error on failure.
     */
    public function create_account( $account_data ) {
        return $this->make_request( '/accounts', 'POST', $account_data );
    }

    /**
     * Delete a user account.
     *
     * @since    1.0.0
     * @param    string    $user_id    The user ID to delete.
     * @return   array|WP_Error        The API response or WP_Error on failure.
     */
    public function delete_account( $user_id ) {
        return $this->make_request( '/accounts/delete', 'POST', array( 'user_id' => $user_id ) );
    }

    /**
     * Create a new project.
     *
     * @since    1.0.0
     * @param    array    $project_data    The project data.
     * @return   array|WP_Error            The created project information or WP_Error on failure.
     */
    public function create_project( $project_data ) {
        return $this->make_request( '/projects', 'POST', $project_data );
    }

    /**
     * Get project details.
     *
     * @since    1.0.0
     * @param    string    $project_id    The project ID.
     * @return   array|WP_Error           The project details or WP_Error on failure.
     */
    public function get_project_details( $project_id ) {
        return $this->make_request( '/projects/' . $project_id );
    }

    /**
     * Create a new code vault.
     *
     * @since    1.0.0
     * @param    array    $code_vault_data    The code vault data.
     * @return   array|WP_Error               The created code vault information or WP_Error on failure.
     */
    public function create_code_vault( $code_vault_data ) {

        // return fake data for debugging
        // return array(
        //     'status' => 'success',
        //     'data' => array(
        //         'id' => '1234567890',
        //         'name' => 'My Code Vault',
        //     ),
        // );

        return $this->make_request( '/code-vaults', 'POST', $code_vault_data );
    }

    /**
     * Get code vault status and facets.
     *
     * @since    1.0.0
     * @param    string    $user_id         The user ID.
     * @param    string    $code_vault_id   The code vault ID.
     * @return   array|WP_Error             The code vault status and facets or WP_Error on failure.
     */
    public function get_code_vault_status( $user_id, $code_vault_id ) {
        $response = $this->make_request( "/code-vaults/{$user_id}/{$code_vault_id}" );

        if ( ! is_wp_error( $response ) && isset( $response['data']['subscription_status'] ) ) {
            $this->options->set_option( 'subscription_status', $response['data']['subscription_status'] );
        }

        return $response;
    }

    /**
     * Get code vault report URLs.
     *
     * @since    1.0.0
     * @param    string    $user_id         The user ID.
     * @param    string    $code_vault_id   The code vault ID.
     * @return   array|WP_Error             The code vault report URLs or WP_Error on failure.
     */
    public function get_code_vault_reports( $user_id, $code_vault_id ) {
        return $this->make_request( "/code-vaults/{$user_id}/{$code_vault_id}/reports" );
    }

    /**
     * Log API interactions to a custom log file.
     *
     * @param string $type The type of interaction (Request/Response).
     * @param string $url The API URL.
     * @param mixed $data The data to log.
     */
    private function log_api_interaction( $type, $url, $data ) {
        // Uncomment for debugging
        // $log_file = WP_CONTENT_DIR . '/code-intelligence-logs.txt';
        // $timestamp = current_time( 'mysql' );
        // $log_entry = sprintf( "[%s] %s to %s:\n%s\n\n", $timestamp, $type, $url, print_r( $data, true ) );
        
        // error_log( $log_entry, 3, $log_file );
    }
}