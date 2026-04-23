<?php
/**
 * Plugin Name: NewPostType
 * Plugin URI: https://atelier.staces.be/
 * Description: Dev tools to create easier a new post type with custom fields
 * Version: 3.3.0
 * Author: Cedric Staces
 * Author URI: https://staces.be/
 * Text Domain: staces-builder
 */

if (!defined('ABSPATH')) { exit; }
namespace StacesBuilder\Inc\NPT;

use StacesBuilder\Inc\NPT\Setups;

require_once(__DIR__.'/custom-fields/custom-fields-manager.php');
require_once(__DIR__.'/new-post-type/cpt-registry.php');
require_once(__DIR__.'/new-post-type/setups.php');
require_once(__DIR__.'/new-post-type/npt.php');

add_action('after_setup_theme', function(){
    if(has_action('npt-setup')) do_action('npt-setup');
});
add_action('init', function(){
    if(has_action('cfm-setup')) do_action('cfm-setup');
}, 1000);