<?php
/* 
Plugin Name: u3a SiteWorks Group Finances
Description: Provides facility to allow entry of Group Finances for the treasurer to pick up as a csv file.
Version: 1.0.0
Author: u3a SiteWorks team
Author URI: https://siteworks.u3a.org.uk/
Plugin URI: https://siteworks.u3a.org.uk/
License: GPLv2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/
define('U3A_GROUPFINANCES_VERSION',  '1.0.0'); 

if (!defined('ABSPATH')) {
    exit;
}

//if (! is_admin()) return; // Plugin only relevant on admin pages.

// Check SiteWorks core plugin is active (needed for accessing group and contact data)
if (!is_plugin_active( 'u3a-siteworks-core/u3a-siteworks-core.php' ) ) {
	return;
} 

// Use the plugin update service on SiteWorks update server

/*require 'inc/plugin-update-checker/plugin-update-checker.php';

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$u3aImpExpUpdateChecker = PucFactory::buildUpdateChecker(
    'https://siteworks.u3a.org.uk/wp-update-server/?action=get_metadata&slug=u3a-importexport', //Metadata URL
    __FILE__, //Full path to the main plugin file or functions.php.
    'u3a-importexport'
);*/

function add_query_vars_filter( $vars ){
    array_push($vars,"gp");//group id
    array_push($vars,"year");//year
    return $vars;
}
add_filter( 'query_vars', 'add_query_vars_filter' );



require_once 'inc/definitions.php';
require_once "classes/u3a-groupfinances-csv-class.php";
require_once "u3a-siteworks-group-finances-activate.php";
require_once "u3a-siteworks-groupfinances-add.php";
require_once "u3a-group-finance-admin-menu.php";

register_activation_hook(__FILE__, 'u3a_csv_groupfinances_install');

do_action('add_option','treasurer','Your treasurer');


add_action('wp_enqueue_scripts', 'evt_enqueue');
function evt_enqueue() {
    wp_enqueue_script('scriptfunctions', plugin_dir_url(__FILE__).'js/scriptfunctions.js',array(),'SU3A_GROUPEVENTSVERSION',false ); 
    }
