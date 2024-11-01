<?php
/**
 * AJAX handlers for the plugin.
 *
 * @since      1.0.0
 * @package    TCR_CBI
 * @subpackage TCR_CBI/includes
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Include file.php to get the wp_handle_upload() function
require_once(ABSPATH . 'wp-admin/includes/file.php');

/**
 * AJAX action to create an account.
 */
function tcr_cbi_create_account() {
    check_ajax_referer( 'tcr_cbi_nonce', 'nonce' );

    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => 'You do not have permission to perform this action.' ) );
    }

    $name = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
    $email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';

    // Check for required fields
    if ( empty( $name ) || empty( $email ) ) {
        wp_send_json_error( array( 'message' => 'Name and email are required fields.' ) );
    }

    $team_name = isset( $_POST['team_name'] ) ? sanitize_text_field( wp_unslash( $_POST['team_name'] ) ) : '';
    $source = isset( $_POST['source'] ) ? sanitize_text_field( wp_unslash( $_POST['source'] ) ) : '';
    $account_package_identifier = isset( $_POST['account_package_identifier'] ) ? sanitize_text_field( wp_unslash( $_POST['account_package_identifier'] ) ) : '';
    $two_factor_code = isset( $_POST['two_factor_code'] ) ? sanitize_text_field( wp_unslash( $_POST['two_factor_code'] ) ) : '';

    $options = new TCR_CBI_Options();
    $api_client = new TCR_CBI_API_Client( $options );

    $account_data = array(
        'name' => $name,
        'email' => $email,
        'team_name' => $team_name,
        'source' => $source,
        'account_package_identifier' => $account_package_identifier,
        'redirect_url' => admin_url( 'admin.php?page=code-intelligence&action=account_created' )
    );

    if ( ! empty( $two_factor_code ) ) {
        $account_data['two_factor_code'] = $two_factor_code;
    }

    $response = $api_client->create_account( $account_data );

    if ( is_wp_error( $response ) ) {
        wp_send_json_error( array( 'message' => $response->get_error_message() ) );
    }

    if ( $response['status'] === 'Success' && $response['data'] === 'verification_required' ) {
        wp_send_json_success( array(
            'message' => $response['message'],
            'verification_required' => true
        ) );
    } elseif ( $response['status'] === 'Success' && isset( $response['data']['id'] ) ) {
        // Store the necessary information in options
        $options->set_option( 'user_id', $response['data']['id'] );
        $options->set_option( 'team_id', $response['data']['team_id'] );
        $options->set_option( 'api_key', $response['data']['api_key'] );
        $options->set_option( 'account_email', $email );

        wp_send_json_success( array(
            'message' => 'Account created successfully',
            'payment_url' => $response['data']['payment_url']
        ) );
    } else {
        wp_send_json_error( array( 'message' => $response['message'] ) );
    }
}
add_action( 'wp_ajax_tcr_cbi_create_account', 'tcr_cbi_create_account' );

/**
 * AJAX action to create a project and code vault.
 */
function tcr_cbi_create_project_and_vault() {
    try {
        check_ajax_referer('tcr_cbi_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            throw new Exception('You do not have permission to perform this action.');
        }

        $options = new TCR_CBI_Options();
        $api_client = new TCR_CBI_API_Client($options);

        // If there is a project AND vault id already, return success
        if (!empty($options->get_option('project_id')) && !empty($options->get_option('code_vault_id'))) {
            wp_send_json_success(array(
                'message' => 'Project and Code Vault already created',
                'project_id' => $options->get_option('project_id'),
                'code_vault_id' => $options->get_option('code_vault_id'),
            ));
            return;
        }

        // Check if we already have a project ID
        $project_id = $options->get_option('project_id');

        if (empty($project_id)) {
            // Create project if we don't have an existing project ID
            $project_data = array(
                'user_id' => $options->get_option('user_id'),
                'name' => get_bloginfo('name') . ' Project',
                'description' => 'WordPress site project for ' . get_bloginfo('name'),
            );

            $project_response = $api_client->create_project($project_data);

            if (is_wp_error($project_response)) {
                throw new Exception($project_response->get_error_message());
            }

            if (!isset($project_response['data']['id'])) {
                throw new Exception('Invalid project response from the server.');
            }

            $project_id = $project_response['data']['id'];
            $options->set_option('project_id', $project_id);
        }

        // Create or retrieve code archive
        $archive_path = tcr_cbi_create_archive();

        if (is_wp_error($archive_path)) {
            throw new Exception('Code Intelligence Archive Creation Error: ' . $archive_path->get_error_message());
        }

        // Create code vault
        $code_vault_data = array(
            'user_id' => $options->get_option('user_id'),
            'project_id' => $project_id,
            'name' => 'Code Vault',
            'archive_url' => $options->get_option('latest_archive_url'),
        );

        $code_vault_response = $api_client->create_code_vault($code_vault_data);

        if (is_wp_error($code_vault_response)) {
            throw new Exception($code_vault_response->get_error_message());
        }

        if (!isset($code_vault_response['data']['id'])) {
            throw new Exception('Invalid code vault response from the server: ' . print_r($code_vault_response, true));
        }

        $options->set_option('code_vault_created_date', gmdate('Y-m-d H:i', strtotime($code_vault_response['data']['created_at'])));
        $options->set_option('code_vault_id', $code_vault_response['data']['id']);
        $options->set_option('code_vault_version', $code_vault_response['data']['version']);
        $options->set_option('code_vault_code_synced_date', gmdate('Y-m-d H:i', strtotime($code_vault_response['data']['code_last_synced'])));

        wp_send_json_success(array(
            'message' => 'Project and Code Vault created successfully',
            'project_id' => $project_id,
            'code_vault_id' => $code_vault_response['data']['id'],
        ), 200);
    } catch (Exception $e) {
        error_log('TCR CBI Error: ' . $e->getMessage());
        wp_send_json_error(array('message' => $e->getMessage()));
    }
}
add_action('wp_ajax_tcr_cbi_create_project_and_vault', 'tcr_cbi_create_project_and_vault');

/**
 * Create or retrieve an archive of the site's code.
 *
 * @return string|WP_Error The path to the archive, or WP_Error on failure.
 */
function tcr_cbi_create_archive() {
    $options = new TCR_CBI_Options();

    // If we're here, we need to create a new archive
    wp_raise_memory_limit('admin');
    set_time_limit(300); // 5 minutes

    // WordPress root directory
    $wp_root = ABSPATH;

    // Create archive directory
    $upload_dir = wp_upload_dir();
    $archive_dir = $upload_dir['basedir'] . '/code-intelligence-archives/latest';
    if (!file_exists($archive_dir)) {
        if (!wp_mkdir_p($archive_dir)) {
            return new WP_Error('archive_dir_creation_failed', 'Failed to create archive directory.');
        }
    }
    $archive_path = $archive_dir . '/site-code-archive.zip';

    // Create the archive
    $zip = new ZipArchive();
    if ($zip->open($archive_path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        return new WP_Error('zip_creation_failed', 'Failed to create zip archive.');
    }

    // Add files to the zip
    tcr_cbi_add_dir_to_zip_recursive($zip, $wp_root, '');

    $zip->close();
    
    // Generate random credentials
    $credentials = tcr_cbi_generate_random_credentials();

    // Create an .htaccess file
    $htaccess_content = "Options -Indexes\n";
    $htaccess_content .= "<Files ~ \"^\.ht\">\n";
    $htaccess_content .= "    Order allow,deny\n";
    $htaccess_content .= "    Deny from all\n";
    $htaccess_content .= "</Files>\n\n";
    $htaccess_content .= "# Block access to sensitive files\n";
    $htaccess_content .= "<FilesMatch \"^(wp-config\.php|php\.ini|\.htaccess)$\">\n";
    $htaccess_content .= "    Order deny,allow\n";
    $htaccess_content .= "    Deny from all\n";
    $htaccess_content .= "</FilesMatch>";

    tcr_cbi_write_file_content($archive_dir . '/.htaccess', $htaccess_content);

    // Create an index.php file with authentication
    $index_content = "<?php\n";
    $index_content .= "\$username = '" . esc_js($credentials['username']) . "';\n";
    $index_content .= "\$password = '" . esc_js($credentials['password']) . "';\n\n";
    $index_content .= "if (!isset(\$_SERVER['PHP_AUTH_USER']) || !isset(\$_SERVER['PHP_AUTH_PW']) ||\n";
    $index_content .= "    \$_SERVER['PHP_AUTH_USER'] !== \$username || \$_SERVER['PHP_AUTH_PW'] !== \$password) {\n";
    $index_content .= "    header('WWW-Authenticate: Basic realm=\"Code Intelligence Archive\"');\n";
    $index_content .= "    header('HTTP/1.0 401 Unauthorized');\n";
    $index_content .= "    echo 'Authentication required.';\n";
    $index_content .= "    exit;\n";
    $index_content .= "}\n\n";
    $index_content .= "\$file = 'site-code-archive.zip';\n";
    $index_content .= "if (file_exists(\$file)) {\n";
    $index_content .= "    header('Content-Type: application/zip');\n";
    $index_content .= "    header('Content-Disposition: attachment; filename=\"' . basename(\$file) . '\"');\n";
    $index_content .= "    header('Content-Length: ' . filesize(\$file));\n";
    $index_content .= "    readfile(\$file);\n";
    $index_content .= "    exit;\n";
    $index_content .= "} else {\n";
    $index_content .= "    echo 'File not found.';\n";
    $index_content .= "}";

    tcr_cbi_write_file_content($archive_dir . '/index.php', $index_content);
    
    $archive_url = str_replace( $upload_dir['basedir'], $upload_dir['baseurl'], $archive_path );
    
    // Include credentials in the URL in a wget-friendly format
    $archive_url = str_replace('://', '://' . urlencode($credentials['username']) . ':' . urlencode($credentials['password']) . '@', $archive_url);

    $path_set = $options->set_option('latest_archive_path', $archive_path);
    $url_set = $options->set_option('latest_archive_url', $archive_url);

    if (!$path_set || !$url_set) {
        error_log("Code Intelligence: Failed to save archive path or URL.");
    }

    // Verify saved options
    $saved_path = $options->get_option('latest_archive_path');
    $saved_url = $options->get_option('latest_archive_url');

    if ($saved_path !== $archive_path || $saved_url !== $archive_url) {
        error_log("Code Intelligence: Mismatch in saved archive path or URL.");
    }

    // Set or update the archive creation date
    update_option( 'tcr_cbi_archive_creation_date', current_time( 'mysql' ) );

    return $archive_path;
}

/** 
 * Generate random credentials for the archive.
 *
 * @return array The credentials.
 */
function tcr_cbi_generate_random_credentials() {
    return array(
        'username' => 'user_' . bin2hex(random_bytes(8)),
        'password' => bin2hex(random_bytes(16))
    );
}

/**
 * Recursively add a directory to a ZIP file, excluding unnecessary files and folders.
 *
 * @param ZipArchive $zip The ZIP archive object.
 * @param string $location The directory to add.
 * @param string $name The name of the directory in the ZIP.
 */
function tcr_cbi_add_dir_to_zip_recursive($zip, $dir, $relative_path = '') {
    $dir = rtrim($dir, '/') . '/';
    $files = new DirectoryIterator($dir);
    foreach ($files as $file) {
        if ($file->isDot()) continue;

        $file_path = $file->getPathname();
        $file_relative_path = $relative_path . $file->getFilename();

        if ($file->isDir()) {
            // Exclude specific directories
            if (in_array($file->getFilename(), ['node_modules', 'vendor', '.git', '.svn'])) {
                continue;
            }

            // Exclude WordPress uploads directory
            if ($file->getFilename() === 'uploads' && strpos($file_path, 'wp-content') !== false) {
                continue;
            }

            // Exclude cache directories
            if (in_array($file->getFilename(), ['cache', 'wpcache', 'wp-cache'])) {
                continue;
            }

            // Recursively add subdirectories
            tcr_cbi_add_dir_to_zip_recursive($zip, $file_path, $file_relative_path . '/');
        } else {
            // Ignore files based on various criteria
            if (tcr_cbi_should_ignore_file($file_path, $file->getFilename())) {
                continue;
            }

            // Skip files larger than a certain size (10MB)
            if ($file->getSize() > 10 * 1024 * 1024) {
                continue;
            }

            $zip->addFile($file_path, $file_relative_path);
        }
    }
}

/**
 * Function to check if a file should be ignored.
 *
 * @param string $file_location The file location.
 * @param string $file_name The file name.
 * @return bool True if the file should be ignored, false otherwise.
 */
function tcr_cbi_should_ignore_file($file_path, $file_name) {
    // Ignore certain file types
    $ignore_extensions = ['zip', 'rar', '7z', 'tar', 'gz', 'tgz', 'bz2', 'tbz', 'sql', 'bak', 
                          'jpg', 'jpeg', 'png', 'gif', 'bmp', 'tiff', 'ico', 'svg',
                          'mp3', 'mp4', 'wav', 'ogg', 'avi', 'mov', 'wmv', 'flv',
                          'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'log'];

    $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    if (in_array($ext, $ignore_extensions)) {
        return true;
    }

    // Exclude .js and .css files in 'dist' folders
    if (strpos($file_path, '/dist/') !== false && ($ext === 'js' || $ext === 'css')) {
        return true;
    }

    // Exclude minified files
    if (strpos($file_name, '.min.') !== false) {
        return true;
    }

    // Exclude bundled files
    if (strpos($file_name, '.bundle.') !== false) {
        return true;
    }

    // Exclude files with 'logs' in the name
    if (strpos($file_name, 'logs') !== false) {
        return true;
    }

    // Exclude specific vendor files
    $vendor_patterns = [
        'assets/js/vendor',
        'google-site-kit/dist/assets/js/googlesitekit-vendor',
        'wp-includes/js/dist/',
        'js/dist/components'
    ];

    foreach ($vendor_patterns as $pattern) {
        if (strpos($file_path, $pattern) !== false) {
            return true;
        }
    }

    return false;
}

/**
 * AJAX action to get analysis data for a vault.
 */
function tcr_cbi_get_facet_data() {
    check_ajax_referer( 'tcr_cbi_nonce', 'nonce' );

    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => 'You do not have permission to perform this action.' ) );
    }

    $options = new TCR_CBI_Options();
    $api_client = new TCR_CBI_API_Client( $options );

    $response = $api_client->get_code_vault_status( $options->get_option('user_id'), $options->get_option('code_vault_id') );

    if ( is_wp_error( $response ) ) {
        wp_send_json_error( array( 'message' => $response->get_error_message() ) );
    }

    if ( ! isset( $response['data'] ) ) {
        wp_send_json_error( array( 'message' => 'Invalid response from the server. Please try again or contact support.' ) );
    }

    $options->set_option('code_vault_created_date', gmdate('Y-m-d H:i', strtotime($response['data']['created_at'])));
    $options->set_option('code_vault_version', $response['data']['version']);
    $options->set_option('code_vault_code_synced_date', gmdate('Y-m-d H:i', strtotime($response['data']['code_last_synced'])));

    wp_send_json_success( array(
        'message' => 'Code vault data retrieved successfully',
        'data' => $response['data']
    ) );
}
add_action( 'wp_ajax_tcr_cbi_get_facet_data', 'tcr_cbi_get_facet_data' );

/**
 * AJAX action to get PDF report status.
 */
function tcr_cbi_get_pdf_report() {
    check_ajax_referer( 'tcr_cbi_nonce', 'nonce' );

    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => 'You do not have permission to perform this action.' ) );
    }

    $options = new TCR_CBI_Options();
    $api_client = new TCR_CBI_API_Client( $options );

    $user_id = $options->get_option( 'user_id' );
    $code_vault_id = $options->get_option( 'code_vault_id' );

    $response = $api_client->get_code_vault_reports( $user_id, $code_vault_id );

    if ( is_wp_error( $response ) ) {
        wp_send_json_error( array( 'message' => $response->get_error_message() ) );
    }

    if ( ! isset( $response['data'] ) || ! isset( $response['data']['snapshot'] ) ) {
        wp_send_json_error( array( 'message' => 'Invalid response from the server. Please try again or contact support.' ) );
    }

    wp_send_json_success( array(
        'snapshot' => $response['data']['snapshot'],
        'comparison' => $response['data']['comparison'] ?? '',
    ) );
}
add_action( 'wp_ajax_tcr_cbi_get_pdf_report', 'tcr_cbi_get_pdf_report' );

/**
 * Handle manual archive recreation.
 */
function tcr_cbi_handle_manual_archive_recreation() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( 'You do not have sufficient permissions to perform this action.' );
    }

    if ( 
        ! isset( $_POST['tcr_cbi_recreate_archive_nonce'] ) || 
        ! wp_verify_nonce( 
            sanitize_text_field( wp_unslash( $_POST['tcr_cbi_recreate_archive_nonce'] ) ),
            'tcr_cbi_recreate_archive' 
        ) 
    ) {
        wp_die( 'Invalid nonce. Please try again.' );
    }

    $archive_path = tcr_cbi_recreate_archive();

    $redirect_url = add_query_arg(
        array(
            'page' => 'data-and-tools',
            'status' => is_wp_error($archive_path) ? 'error' : 'success'
        ),
        admin_url( 'admin.php' )
    );

    wp_safe_redirect( $redirect_url );
    exit;
}
add_action( 'admin_post_tcr_cbi_recreate_archive_manually', 'tcr_cbi_handle_manual_archive_recreation' );

/** 
 * Function to write the file content to the specified file path.
 *
 * @param string $file_path The file path.
 * @param string $content The file content.
 * @return bool True if the file was written successfully, false otherwise.
 */
function tcr_cbi_write_file_content($file_path, $content) {
    global $wp_filesystem;

    // Initialize the WP filesystem, no more using 'file-put-contents' function
    if (empty($wp_filesystem)) {
        require_once (ABSPATH . '/wp-admin/includes/file.php');
        WP_Filesystem();
    }

    // Write the file
    if (!$wp_filesystem->put_contents($file_path, $content, FS_CHMOD_FILE)) {
        // Handle error here
        error_log("Failed to write to file: " . $file_path);
        return false;
    }

    return true;
}