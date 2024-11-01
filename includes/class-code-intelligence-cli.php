<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( defined( 'WP_CLI' ) && WP_CLI ) {

    /**
     * Implements example command.
     */
    class TCR_CBI_CLI_Command {

        /**
         * Triggers the archive recreation cron job.
         *
         * ## EXAMPLES
         *
         *     wp code-intelligence trigger-cron
         *
         * @when after_wp_load
         */
        public function trigger_cron( $args, $assoc_args ) {
            do_action( 'tcr_cbi_recreate_archive' );
            WP_CLI::success( 'Cron job triggered successfully. Archive recreation process has started.' );
        }
    }

    WP_CLI::add_command( 'code-intelligence', 'TCR_CBI_CLI_Command' );
}