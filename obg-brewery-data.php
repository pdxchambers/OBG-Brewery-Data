<?php
/*
 Plugin Name: OBG Brewery Data
 Plugin URI:  TBD
 Description: This is a custom plugin for Oregon Beer Guy that adds custom datbase tables for Oregon Brewery information. It is not designed to work with 
 versions of WordPress prior to 3.5.
 Version:     1.0
 Author:      Julien "Oregon Beer Guy" Chambers
 Author URI:  http://www.pdxchambers.com
 License:     GPL3
 OBG Brewery Data is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.
 
OBG Brewery Data is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License
along with OBG Brewery Data. If not, see https://www.gnu.org/licenses/gpl-3.0.html.
 */
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
define('OBG_BREWERY_DATA_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
global $ogb_brewery_version;
$obg_brewery_version = '1.0';

/*Enqueuing Scripts and styles*/
function obg_enqueue_scripts() {
	wp_enqueue_style( 'obg_main_style', plugins_url( '/css/obg_style.css', __FILE__ ), false, null, 'all' );
}

add_action( 'wp_enqueue_scripts', 'ogb_enqueue_scripts' );

/*Register short code, this code gets added to a page to tell WordPress where to 
 *display the contents of the plugin.
 */
function obg_do_brewery_shortcode(){
	global $wpdb;
	$table = $wpdb->prefix.'breweries';
	return obg_display_data( $table );
}

function obg_do_homebrew_shortcode(){
	global $wpdb;
	$table = $wpdb->prefix.'homebrew';
	return obg_display_data( $table );
}

function obg_get_brewery_num_shortcode(){
	global $wpdb;
	return obg_get_table_size( $wpdb->prefix.'breweries' );
}

function obg_get_homebrew_num_shortcode(){
	global $wpdb;
	return obg_get_table_size( $wpdb->prefix.'homebrew' );
}

function obg_register_shortcode(){
	add_shortcode( 'obg-brewery-data', 'obg_do_brewery_shortcode' );
	add_shortcode( 'obg-homebrew-data', 'obg_do_homebrew_shortcode' );
	add_shortcode( 'obg-num-breweries', 'obg_get_brewery_num_shortcode' );
	add_shortcode( 'obg-num-homebrew', 'obg_get_homebrew_num_shortcode' );
}

add_action( 'init', 'obg_register_shortcode' );

/*Activation/Deactivation Hooks: Tells WordPress what to do when the plugin is activated/deactivated*/
register_activation_hook( __FILE__, 'obg_activate_plugin' );
register_deactivation_hook( __FILE__, 'obg_deactivate_plugin' );

function obg_activate_plugin() {
	global $wpdb;
	global $ogb_brewery_version;
	
	$brewery_table = $wpdb->prefix . 'breweries';
	$homebrew_table = $wpdb->prefix . 'homebrew';
	$charset_collate = $wpdb->get_charset_collate();
	if( $wpdb->get_var("SHOW TABLES LIKE '$brewery_table'") != $brewery_table ){ /*Making sure table doesn't already exist*/
		$brewery_sql = "CREATE TABLE $brewery_table (
			p_index int(11) NOT NULL UNIQUE AUTO_INCREMENT,
			name varchar(45) DEFAULT '',
			address varchar(45) DEFAULT '',
			city varchar(45) DEFAULT '',
			state varchar(45) DEFAULT 'OR-Oregon',
			zip varchar(45) DEFAULT '',
			phone varchar(45) DEFAULT '',
			website varchar(45) DEFAULT '',
			twitter varchar(45) DEFAULT '',
			facebook varchar(45) DEFAULT '',
			visited int(11) DEFAULT 0,
			notes longtext,
			instagram varchar(45) DEFAULT '',
			PRIMARY KEY  (p_index)
			) $charset_collate;";
	}
	
	if( $wpdb->get_var("SHOW TABLES LIKE '$brewery_table'") != $homebrew_table ){ /*Making sure table doesn't already exist*/
		$homebrew_sql = "CREATE TABLE $homebrew_table (
			p_index int(11) NOT NULL UNIQUE AUTO_INCREMENT,
			name varchar(45) DEFAULT '',
			address varchar(45) DEFAULT '',
			city varchar(45) DEFAULT '',
			state varchar(45) DEFAULT 'OR-Oregon',
			zip varchar(45) DEFAULT '',
			phone varchar(45) DEFAULT '',
			website varchar(45) DEFAULT '',
			twitter varchar(45) DEFAULT '',
			facebook varchar(45) DEFAULT '',
			visited int(11) DEFAULT 0,
			notes longtext,
			instagram varchar(45) DEFAULT '',
			PRIMARY KEY  (p_index)
			) $charset_collate;";
	}
	/*
	 * NOTE that there is a double space between the PRIMARY KEY keyword and the definition in the above declaration. This is because the 
	 * dbDelta() function below requires it. Likewise, we need to include upgrade.php here because dbDelta() will not be defined otherwise.
	 */
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $brewery_sql );
	dbDelta( $homebrew_sql );
	add_option( 'ogb_brewery_version', $obg_brewery_version );
}
function obg_deactivate_plugin() {
	
}
/*Create Admin panel off the WordPress admin screen. Renders under the "Settings" section.*/
/*TO BE IMPLEMENTED LATER*/

/*function obg_admin_panel() {
	add_options_page (
		'OBG Brewery Data',
		'OBG Brewery Data',
		'manage_options',
		'obg_brewery_data.php',
		'obg_brewery_data_admin'
	);
}

function obg_brewery_data_admin(){
	require_once( plugin_dir_path( __FILE__ ) . '/admin/obg-admin.php' );
}

add_action( 'admin_menu', 'obg_admin_panel' );*/

/*Functions to actually pull data from the database and display it on the page*/

function obg_get_table_size( $table ) {
	/*Returns a count of the total number of rows in the table*/
	global $wpdb;
	return $wpdb->get_var( 'SELECT COUNT(*) FROM ' . $table );
}

function obg_create_dropdown($table, $selector = 'city') {
	/*Creates a dropdown input to display on the page. Defaults to city column.*/
	global $wpdb;
	$dropdown = '<select name="' . $selector . '" style="height: 2.55rem;"><option value="all">--All--</option>';
	$options = $wpdb->get_col( 'SELECT DISTINCT ' . $selector . ' FROM ' . $table );
	foreach ( $options as $option ) {
		$dropdown .= '<option value="' . $option . '">' . $option . '</option>';
	}
	$dropdown .= '</select>';
	return $dropdown;
}

function obg_display_row( $table, $selector = 'all' ){
	/*
	 * Displays the database rows corresponding to $selector. Ideally this should be from POST data, and sanitized before
	 * running the query.
	 */
	global $wpdb;
	$table_html = '<div>';
	if ( $selector == 'all' ) {
		$sql = 'SELECT * FROM ' . $table;
		$rows = $wpdb->get_results( $sql, ARRAY_A );
	} else {
		$sql = $wpdb->prepare('SELECT * FROM ' . $table . ' WHERE city = %s', $selector);
		$rows = $wpdb->get_results( $sql, ARRAY_A );
	}
	foreach ($rows as $row ) {
		$table_html .= '<table>';
		$table_html .= '<tr><th colspan="3"><h3>' . $row['name'] . '</h3></th><td>' . $row['phone'] . '</td></tr>';
		$table_html .= '<tr><th colspan="4">Address:</th></tr>';
		$table_html .= '<tr><td>' . $row['address'] . '</td><td>' . $row['city'] . '</td><td>' . $row['state'] . '</td><td>' . $row['zip'] . '</td></tr>';
		$table_html .= '<tr><th colspan="2">Website</th><th colspan="2">Facebook</th></tr>';
		$table_html .= '<tr><td colspan="2">' . $row['website'] . '</td><td colspan="2">' . $row['facebook'] . '</td></tr>';
		$table_html .= '<tr><th colspan="2">Twitter</th><th colspan="2">Instagram</th>';
		$table_html .= '<tr><td colspan="2">' . $row['twitter'] . '</td><td colspan="2">' . $row['instagram'] . '</td></tr>';
		$table_html .= '<tr><th colspan="4">Notes:</th></tr>';
		$table_html .= '<tr><td colspan="4">' . $row['notes'] . '</td></tr>';
		$table_html .= '</table>';
	}
	$table_html .= '</div>';
	return $table_html;
}

function obg_display_data( $table ) {
	global $wpdb;
	$form_action = esc_url( admin_url( 'admin_post.php' ) );
	$html = '<form style="margin-bottom: 5px;"' . $form_action . '" method="post">' . obg_create_dropdown( $table ) . '<input type="submit" value="Go" action=">';
	$html .= '<input type="hidden" name="action" value="dropdown_query"></form>';
	if ( isset( $_POST['city'] ) ){
		$html .= obg_display_row( $table, $_POST['city'] );
	} else {
		$html .= obg_display_row( $table );
	}
	return $html;	
}

/*
function obg_admin_data() {
	global $wpdb;
	$db_table = $wpdb->prefix.'breweries';
	if (isset( $_POST[ 'admin_update_panel' ] ) ){
		$sanitized_data = array (
			'p_index' => '', //Index for entry, will auto-increment in the database
			'name' => sanitize_text_field( $_POST['brewery_name'] ),
			'phone' => sanitize_text_field( $_POST['brewery_phone'] ),
			'address' => sanitize_text_field( $_POST['brewery_address'] ),
			'city' => sanitize_text_field( $_POST['brewery_city'] ),
			'state' => sanitize_text_field( $_POST['brewery_state'] ),
			'zip' => sanitize_text_field( $_POST['brewery_zip'] ),
			'website' => sanitize_text_field( $_POST['brewery_website'] ),
			'twitter' => sanitize_text_field( $_POST['brewery_twitter'] ),
			'instagram' => sanitize_text_field( $_POST['brewery_instagram'] ),
			'visited' => '',
			'facebook' => sanitize_text_field( $_POST['brewery_facebook'] ),
			'notes' => sanitize_text_field( $_POST['brewery_notes'] )
		);
		switch ( $_POST['query_type'] ) {
			case 'insert':
				$wpdb->insert(
					$db_table,
					$sanitized_data,
					array('%d, %s,%s,%s,%s,%s,%s,%s,%s,%s,%d,%s,%s')
				);	
				break;
			case 'update':
				break;
			case 'delete':
				break;
			default:
				break;
		}
	}
}
*/
add_action( 'admin_post_nopriv_dropdown_query', 'obg_display_data' );
add_action( 'admin_post_dropdown_query', 'obg_display_data' );
/*
add_action( 'admin_post_nopriv_admin_update_panel', 'obg_admin_data' );
add_action( 'admin_post_admin_update_panel', 'obg_admin_data' );
*/

























