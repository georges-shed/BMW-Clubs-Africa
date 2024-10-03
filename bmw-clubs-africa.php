<?php
/**
 * Plugin Name: Bmw Clubs Africa
 * Plugin URI: https://yourpluginwebsite.com
 * Description: A simple plugin to manage BMW Clubs Africa functionalities.
 * Version: 1.0.0
 * Author: Talha Shahid
 * Author URI: https://bmwclubs.africa
 * License: GPL2
 * Text Domain: bmw-clubs-africa
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('BMW_CLUBS_AFRICA_VERSION', '1.0.0');
define('BMW_CLUBS_AFRICA_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('BMW_CLUBS_AFRICA_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include admin pages file
require_once BMW_CLUBS_AFRICA_PLUGIN_DIR . 'admin-pages.php';

// Include the e-card file from the includes folder
require_once BMW_CLUBS_AFRICA_PLUGIN_DIR . 'includes/e-card.php';
require_once BMW_CLUBS_AFRICA_PLUGIN_DIR . 'includes/rides.php';

// Plugin activation hook
register_activation_hook(__FILE__, 'bmw_clubs_africa_activate');
function bmw_clubs_africa_activate() {
    // Placeholder for activation tasks (e.g., setting default options, creating tables)
}

// Plugin deactivation hook
register_deactivation_hook(__FILE__, 'bmw_clubs_africa_deactivate');
function bmw_clubs_africa_deactivate() {
    // Placeholder for deactivation tasks (e.g., removing scheduled events, temporary data cleanup)
}

