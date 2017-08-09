<?php
	/*
	 * Runs function to uninstall plugin from WordPress. This function removes custom database tables from the WordPress 
	 * database. This means all plugin data will be lost and the plugin will have to be reconfigured if you change your mind.
	 */

if (!defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}
$option_name = 'obg_breweries_option';
delete_option( $option_name );

global $wpdb;
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}breweries" );
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}homebrew" );