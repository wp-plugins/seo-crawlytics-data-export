<?php
/**
Plugin Name: Seo Crawlytics Data Export & Delete
Plugin URI: http://apasionados.es
Description: Exports all entries of SeoCrawlytics and gives you the option to delete the entries that are older than 1 month. We noticed that the size of the SeoCrawlytics table in the database can get huge if your website has visits while using "WP Clean UP" to clean up WordPress installations and so we created this plugin. Disable when not in use.
Version: 1.0
Author: Apasionados.es
Author URI: http://apasionados.es
License: GPL2
Text Domain: seocrawlyticsdataexport
*/
 
 /*  Copyright 2014  Apasionados.es  (email: info@apasionados.es)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

$plugin_header_translate = array( __('SeoCrawlytics Data Export & Delete', 'seocrawlyticsdataexport'), __('Exports all entries of SeoCrawlytics and gives you the option to delete the entries that are older than 1 month. We noticed that the size of the SeoCrawlytics table in the database can get huge if your website has visits while using "WP Clean UP" to clean up WordPress installations and so we created this plugin. Disable when not in use.', 'seocrawlyticsdataexport') );

add_action( 'admin_init', 'seocrawlyticsdataexport_load_language' );
function seocrawlyticsdataexport_load_language() {
	load_plugin_textdomain( 'seocrawlyticsdataexport', false,  dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}

if ( ! function_exists('is_plugin_active')) {
    require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
}

function seocrawlytics_data_export_admin( ) {
	if ( !is_super_admin() || !is_admin_bar_showing() )
		return;

	global $wpdb;
	$seocrawlytics = "";
	$seocrawlyticsQ = "SELECT * FROM " . $wpdb->prefix . "plugin_bota WHERE 1 = 1 ORDER BY `id` ASC";
	$seocrawlytics = $wpdb->get_results( $seocrawlyticsQ, ARRAY_A);
	$seocrawlyticsString = "";
	$count = 0;
	if( $seocrawlytics ){
		$first = true;
		foreach( $seocrawlytics as $row ){
			if( $first ){
				// column labels
				foreach ( $row as $col => $value ){
						$seocrawlyticsString .= $col . chr( 9 );
				}
				$seocrawlyticsString .= chr( 13 );
				$first = false;
			}
			foreach ( $row as $col => $value ){
				//remove tabs and line breaks from comment content
				$seocrawlyticsString .= str_replace( chr(10), '', str_replace( chr(13), '', str_replace( chr(9), '', $value ))) . chr( 9 );
			}
			$seocrawlyticsString .= chr( 13 );
			$count++;
			//if( $count > 25 ){ break 1; }
		}
	} else{
		// no comments found
		$seocrawlyticsString = "There are no entries in the database table of SeoCrawlytics.";
	}

	$seocrawlyticsDeleteQ = "DELETE FROM " . $wpdb->prefix . "plugin_bota WHERE `visited_on` < DATE_SUB(NOW(), INTERVAL 1 MONTH);";
	$seocrawlyticsDeleteQ2 = "SELECT COUNT(*) FROM " . $wpdb->prefix . "plugin_bota WHERE `visited_on` < DATE_SUB(NOW(), INTERVAL 1 MONTH);";
	$seocrawlyticsOptimizeQ = "OPTIMIZE TABLE " . $wpdb->prefix . "plugin_bota";
?>

<div class=wrap>
	<h2>SeoCrawlytics Data Export & Delete</h2>
   	<h3>Export all Seo Crawlytics Data</h3>
        <?php
			if($_POST['deleterows_hidden'] == 'Y' && $_POST['chkdelete'] == 'Y') {
				if ($wpdb->get_var($seocrawlyticsDeleteQ2) == 0 ) { echo "<p style='color:red'><strong>There are no SeoCrawlytics entries older than one month that can been deleted.</strong></p>"; }
				elseif ($wpdb->query($seocrawlyticsDeleteQ) != FALSE) { echo "<p style='color:green'><strong>All SeoCrawlytics entries older than one month have been deleted.</strong></p>"; }
				else { echo "<p style='color:red'><strong>Internal error occured while deleting entries. Please try again later.</strong></p>"; }
	        } elseif($_POST['opsoptimize_hidden'] == 'Y') {
				if($wpdb->query($seocrawlyticsOptimizeQ) != FALSE) { echo "<p style='color:green'><strong>Database SeoCrawlytics table optimization successful.</strong></p>"; }
				else { echo "<p style='color:red'><strong>Internal error occured while optimizing. Please try again later.</strong></p>"; }
			}
			if($_POST['ops_hidden'] == 'Y') {
		?>
                <p>Found <strong><?php echo $count; ?></strong> SeoCrawlytics entries:</p>
                <p>Please select them all and paste them in a spreadsheat to save them:</p>
                <form>
	                <textarea id="txt_cmts" name="text_cmts" cols="66" rows="10"><?php echo $seocrawlyticsString; ?></textarea>
                </form>
                <p>Query used: <code><?php echo $seocrawlyticsQ; ?></code></p>
			<?php } else { ?>
				<p>To export the SeoCrawlytics data, please click:</p>
                <form method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
                    <p><input type="hidden" name="ops_hidden" value="Y"></p>
                    <p><input type="submit" id="ops" name="submit" value="Show / Export SeoCrawlytics Data"></p>
                </form>
            <?php } ?>
    <p><small>Please note that there is no file creation included in this plugin. In order to export the entries, please select the contents of the textarea (which is visible after clicking on "Export SeoCrawlytics Data") and paste into a spreadsheet. Columns are separated by a tabulator.</small></p>
	<hr>
   	<h3>Delete Seo Crawlytics database entries older than 1 month</h3>    
	<form method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
        <p><input type="hidden" name="deleterows_hidden" value="Y"></p>
		<p>Please keep in mind that this action cannot be undone, unless you have a database backup.</p>
        <p><input type="checkbox" name="chkdelete" value="Y" /> Check to delete Seocrawlyitcs data older than 1 month.</p>
		<p><input type="submit" id="opsdelete" name="Submit" value="Delete SeoCrawlytics Data older than 1 month"></p>
		<p>Query that will be used: <code><?php echo $seocrawlyticsDeleteQ; ?></code></p>
	</form>
	<hr>
   	<h3>Optimize Seo Crawlytics table after deleting rows</h3>        
	<form method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
        <p><input type="hidden" name="opsoptimize_hidden" value="Y"></p>
		<p><input type="submit" id="opsoptimize" name="Submit" value="Optimize SeoCrawlytics Table"></p>
		<p>Query that will be used: <code><?php echo $seocrawlyticsOptimizeQ; ?></code></p>
	</form>
	<hr>
	<h2>SeoCrawlytics Data Export & Delete: Help</h2>
	<p><class style='color:red'><strong>Please disable this plugin after you have exported and deleted the entries of SeoCrawlytics older than a month. Don't leave it active.</strong></class> Click <a href="<?php echo site_url(); ?>/wp-admin/plugins.php">here</a> to go to the plugin page and deactivate this plugin if you have finished using it (enable it when you need it again).</p>
	<p>To <strong>export the SeoCrawlytics data</strong>, click on "Export SeoCrawlytics data". You will see a textarea with all the entries. Please select them all and paste them into a spreadsheet and save it. The export is tab-delimited.</p>
    <p>To <strong>delete all entries older than last month</strong>, please check "Check to delete Seocrawlyitcs data older than 1 month." and click on "Delete SeoCrawlytics Data older than 1 month". If the entries have been successfully deleted, you will receive a message with this information. If there has been an error, you will also get a message. If you don't get any message at all, try again.</p>
    <p>To <strong>optimize the table after deleting data</strong>, click on "Optimize SeoCrawlytics Table".</p>
</div>

<?php
}  //end seocrawlytics_data_export_admin

if ( is_plugin_active( 'seo-crawlytics/seocrawlytics.php' ) ) {
	if (!function_exists("seocrawlytics_dataexport_admin_menu")) {
		add_action('admin_menu', 'seocrawlytics_dataexport_admin_menu');
		function seocrawlytics_dataexport_admin_menu() {
			add_management_page('Seo Crawlytics Export & Entry Delete', 'Seo Crawlytics Export', 1, 'seocrawlytics-export', 'seocrawlytics_data_export_admin');
		}
	}
}
?>