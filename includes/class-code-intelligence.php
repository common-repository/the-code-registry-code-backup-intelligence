<?php
/**
 * The core plugin class.
 *
 * @since      1.0.0
 * @package    TCR_CBI
 * @subpackage TCR_CBI/includes
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class TCR_CBI {

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      TCR_CBI_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The options instance for the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      TCR_CBI_Options    $options    Manages the plugin options.
     */
    protected $options;

    /**
     * The API client instance for the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      TCR_CBI_API_Client    $api_client    Manages the plugin API client.
     */
    protected $api_client;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function __construct() {
        $this->options = new TCR_CBI_Options();
        $this->api_client = new TCR_CBI_API_Client( $this->options );
        $this->load_dependencies();
        $this->define_admin_hooks();
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - TCR_CBI_Loader. Orchestrates the hooks of the plugin.
     * - TCR_CBI_Admin. Defines all hooks for the admin area.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies() {
        require_once TCR_CBI_PLUGIN_DIR . 'includes/class-code-intelligence-loader.php';
        require_once TCR_CBI_PLUGIN_DIR . 'admin/class-code-intelligence-admin.php';

        $this->loader = new TCR_CBI_Loader();
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks() {
        $plugin_admin = new TCR_CBI_Admin('code-intelligence', TCR_CBI_VERSION);

        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
        $this->loader->add_action( 'admin_menu', $plugin_admin, 'add_plugin_admin_menu' );
        $this->loader->add_action( 'admin_init', $plugin_admin, 'register_settings' );
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run() {
        $this->loader->run();
    }

    /**
     * Get the options instance.
     *
     * @since     1.0.0
     * @return    TCR_CBI_Options    The options instance.
     */
    public function get_options() {
        return $this->options;
    }

    /**
     * Get the API client instance.
     *
     * @return TCR_CBI_API_Client
     */
    public function get_api_client() {
        return $this->api_client;
    }
}