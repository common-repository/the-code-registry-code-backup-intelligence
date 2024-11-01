<?php
/**
 * Provide a admin area view for the plugin options
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://thecoderegistry.com
 * @since      1.0.0
 *
 * @package    TCR_CBI
 * @subpackage TCR_CBI/admin/partials
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

$options = $this->options->get_all_options();
?>

<div class="wrap">
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

    <div id="message" class="notice hidden">
        <p></p>
    </div>

    <form method="post" action="options.php">
        <?php
        settings_fields( 'tcr_cbi_options' );
        do_settings_sections( 'tcr_cbi_options' );
        ?>
        <table class="form-table">
            <tr valign="top">
                <th scope="row">Marketplace API Endpoint</th>
                <td><input type="text" name="tcr_cbi_options[marketplace_api_endpoint]" value="<?php echo esc_attr( $options['marketplace_api_endpoint'] ); ?>" class="regular-text" readonly /></td>
            </tr>
            <tr valign="top">
                <th scope="row">API Key</th>
                <td><input type="text" name="tcr_cbi_options[api_key]" value="<?php echo esc_attr( $options['api_key'] ); ?>" class="regular-text" readonly /></td>
            </tr>
            <!-- <tr valign="top">
                <th scope="row">Team ID</th>
                <td><input type="text" name="tcr_cbi_options[team_id]" value="<?php echo esc_attr( $options['team_id'] ); ?>" class="regular-text" readonly /></td>
            </tr>
            <tr valign="top">
                <th scope="row">User ID</th>
                <td><input type="text" name="tcr_cbi_options[user_id]" value="<?php echo esc_attr( $options['user_id'] ); ?>" class="regular-text" readonly /></td>
            </tr>
            <tr valign="top">
                <th scope="row">Project ID</th>
                <td><input type="text" name="tcr_cbi_options[project_id]" value="<?php echo esc_attr( $options['project_id'] ); ?>" class="regular-text" readonly /></td>
            </tr>
            <tr valign="top">
                <th scope="row">Code Vault ID</th>
                <td><input type="text" name="tcr_cbi_options[code_vault_id]" value="<?php echo esc_attr( $options['code_vault_id'] ); ?>" class="regular-text" readonly /></td>
            </tr> -->
            <tr valign="top">
                <th scope="row">Code Vault Created Date</th>
                <td><input type="text" name="tcr_cbi_options[code_vault_created_date]" value="<?php echo esc_attr( $options['code_vault_created_date'] ); ?>" class="regular-text" readonly /></td>
            </tr>
            <tr valign="top">
                <th scope="row">Code Vault Version</th>
                <td><input type="text" name="tcr_cbi_options[code_vault_version]" value="<?php echo esc_attr( $options['code_vault_version'] ); ?>" class="regular-text" readonly /></td>
            </tr>
            <tr valign="top">
                <th scope="row">Code Vault Synced Date</th>
                <td><input type="text" name="tcr_cbi_options[code_vault_code_synced_date]" value="<?php echo esc_attr( $options['code_vault_code_synced_date'] ); ?>" class="regular-text" readonly /></td>
            </tr>
        </table>
    </form>
    <br />

    <hr />

    <br />
    <h1>Automatic code replication</h1>
    <br />
    <p>The archive we create (that contains your WordPress site's code to be analysed) is automatically re-created each month with the latest version of your site's code. We then do a fresh code sync and analysis of this updated archive each month. This also means your latest site code is automatically backed up each month - without you needing to do anything!</p>
    <br />
    <p>The archive needs to be publicly accessible at the location specified below for our automated replication to work correctly.</p>
    <br />
    
    <?php
    $options = new TCR_CBI_Options();
    $archive_path = $options->get_option('latest_archive_path');
    $archive_url = $options->get_option('latest_archive_url');
    $last_created = get_option('tcr_cbi_archive_creation_date');
    $code_vault_created_date = $options->get_option('code_vault_created_date');

    // Calculate the next sync date
    $next_sync_date = '';
    if ($code_vault_created_date) {
        $created_date = new DateTime($code_vault_created_date);
        $now = new DateTime();
        $next_sync = clone $created_date;
        while ($next_sync <= $now) {
            $next_sync->modify('+1 month');
        }
        $next_sync_date = $next_sync->format('Y-m-d H:i:s');
    }
    ?>

    <p><strong>Local code archive Path:</strong> <?php echo esc_html($archive_path); ?></p>
    <p><strong>Code archive URL:</strong> <?php echo esc_url($archive_url); ?></p>
    <p><strong>Date local code archive was last updated:</strong> <?php echo $last_created ? esc_html($last_created) : 'Not created yet'; ?></p>
    <p><strong>Date of next Code Registry code sync:</strong> <?php echo esc_html($next_sync_date); ?></p>
    <br />

    <p>This process is fully automated by our plugin using the WordPress cron system, but you can also manually update the code archive below. This action can take a short while to complete depending on the size of your site's codebase.</p>
    <br />

    <form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="post" id="recreate-archive-form">
        <?php wp_nonce_field( 'tcr_cbi_recreate_archive', 'tcr_cbi_recreate_archive_nonce' ); ?>
        <input type="hidden" name="action" value="tcr_cbi_recreate_archive_manually">
        <?php submit_button( 'Manually Update Code Archive', 'secondary', 'submit', false, array('id' => 'recreate-archive-button') ); ?>
    </form>

    <br /><br />

    <hr />

    <br />
    <h1>Access our main web app directly</h1>
    <br />
    
    <?php
    $options = $this->options->get_all_options();
    $account_email = $options['account_email'];
    $api_key = $options['api_key'];
    if ( ! empty( $account_email ) && ! empty( $api_key ) ) :
    ?>
        <p>
            <?php
            printf(
                /* translators: 1: account email */
                esc_html__( 'Your account is associated with the email address: %s', 'the-code-registry-code-backup-intelligence' ),
                '<strong>' . esc_html( $account_email ) . '</strong>'
            );
            ?>
        </p>
        <br />
        <p>
            <?php esc_html_e( 'You can access our web app directly using this email address. Here\'s what you need to know:', 'the-code-registry-code-backup-intelligence' ); ?>
        </p>
        <br />
        <ul style="list-style-type: disc; margin-left: 20px;">
            <li><?php esc_html_e( 'Visit our web app at', 'the-code-registry-code-backup-intelligence' ); ?> <a href="https://app.thecoderegistry.com" target="_blank" class="text-brand-purple font-semibold hover:text-brand-blue">https://app.thecoderegistry.com</a></li>
            <li><?php esc_html_e( 'Log in using the email address shown above.', 'the-code-registry-code-backup-intelligence' ); ?></li>
            <li><?php esc_html_e( 'On your first login, you\'ll need to verify your email address again for security purposes.', 'the-code-registry-code-backup-intelligence' ); ?></li>
            <li><?php esc_html_e( 'Once verified, you\'ll have full access to all of the many additional features of our web app.', 'the-code-registry-code-backup-intelligence' ); ?></li>
        </ul>
        <br />
        <p>
            <?php esc_html_e( 'Our web app has much more functionality than our WordPress plugin, including live chat with our AI Assistant Ada and a full support system with tickets and knowledgebase. If you encounter any issues logging in or need assistance, please contact our support team.', 'the-code-registry-code-backup-intelligence' ); ?>
        </p>
    <?php else : ?>
        <p>
            <?php esc_html_e( 'Once you sign up for an account (with a free trial), we\'ll explain how to access our main web app directly.', 'the-code-registry-code-backup-intelligence' ); ?>
        </p>
    <?php endif; ?>

    <br /><br />

    <?php if ( ! empty( $account_email ) && ! empty( $api_key ) ) : ?>
        <hr />

        <br />
        <h1>Delete Account</h1>
        <br />

        <p style="color: red;">
            <strong>Warning:</strong> Deleting your account cannot be undone. This action will remove all your data from The Code Registry platform.
        </p>
        <br />
        <p>
            If you have an active subscription with The Code Registry, deleting your account here does not cancel that subscription. You will still be required to finish your subscription term.<br />Please contact The Code Registry support to manage your subscription at customers@thecoderegistry.com.
        </p>
        <br />
        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
            <?php wp_nonce_field( 'tcr_cbi_delete_account', 'tcr_cbi_delete_account_nonce' ); ?>
            <input type="hidden" name="action" value="tcr_cbi_delete_account">
            <p>
                <input type="submit" name="delete_account" id="delete_account" class="button button-secondary" value="Delete Account" onclick="return confirm('Are you sure you want to delete your account? This action cannot be undone.');">
            </p>
        </form>
    <?php endif; ?>
</div>