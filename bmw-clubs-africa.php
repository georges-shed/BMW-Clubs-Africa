<?php
/**
 * Plugin Name: Club Manager
 * Plugin URI: https://yourpluginwebsite.com
 * Description: A streamlined dashboard for managing users, memberships, events, and orders, tailored specifically for club administrators.
 * Version: 1.0.0
 * Author: Web Hosting Guru
 * Author URI: https://bmwclubs.africa
 * License: GPL2
 * Text Domain: club-manager
 */
 
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('CLUB_MANAGER_VERSION', '1.0.0');
define('CLUB_MANAGER_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('CLUB_MANAGER_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include admin pages file
require_once CLUB_MANAGER_PLUGIN_DIR . 'admin-pages.php';

// Plugin activation hook
register_activation_hook(__FILE__, 'club_manager_activate');
function club_manager_activate() {
    // Placeholder for activation tasks (e.g., setting default options, creating tables)
}

// Plugin deactivation hook
register_deactivation_hook(__FILE__, 'club_manager_deactivate');
function club_manager_deactivate() {
    // Placeholder for deactivation tasks (e.g., removing scheduled events, temporary data cleanup)
}

// Enqueue the JavaScript file
function club_manager_enqueue_scripts() {
    wp_enqueue_script('edit-clubs-js', CLUB_MANAGER_PLUGIN_URL . 'assets/edit-clubs.js', array('jquery', 'wp-mediaelement'), null, true);
    wp_enqueue_media(); // Media library for file upload
    wp_enqueue_style('select2-css', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css'); // Select2 CSS
    wp_enqueue_script('select2-js', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js', array('jquery'), null, true); // Select2 JS
}
add_action('admin_enqueue_scripts', 'club_manager_enqueue_scripts');
