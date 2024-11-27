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
require_once CLUB_MANAGER_PLUGIN_DIR . 'bmw-ajax.php';
require_once CLUB_MANAGER_PLUGIN_DIR . 'club-permissions.php';


// Assuming the main plugin file is in the root folder of your plugin
require_once plugin_dir_path(__FILE__) . 'admin-pages/includes/post-permissions.php';


// Plugin activation hook
register_activation_hook(__FILE__, 'club_manager_activate');

function club_manager_activate() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    // Table for Clubs
    $table_name = $wpdb->prefix . 'clubs';
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        club_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        club_name VARCHAR(255) NOT NULL,
        club_url VARCHAR(255) NOT NULL,
        club_logo VARCHAR(255) DEFAULT NULL,
        PRIMARY KEY (club_id)
    ) $charset_collate;";
    $wpdb->query($sql);

    // Table for EFT Details
    $table_name = $wpdb->prefix . 'eft_details';
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        eft_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        club_id BIGINT(20) UNSIGNED NOT NULL,
        account_name VARCHAR(255) NOT NULL,
        account_number VARCHAR(255) NOT NULL,
        bank_name VARCHAR(255) NOT NULL,
        branch_code VARCHAR(50) NOT NULL,
        PRIMARY KEY (eft_id),
        FOREIGN KEY (club_id) REFERENCES {$wpdb->prefix}clubs(club_id) ON DELETE CASCADE
    ) $charset_collate;";
    $wpdb->query($sql);

    // Table for Club Roles
    $table_name = $wpdb->prefix . 'club_roles';
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        role_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        club_id BIGINT(20) UNSIGNED NOT NULL,
        username VARCHAR(255) NOT NULL,
        role_name VARCHAR(255) NOT NULL,
        PRIMARY KEY (role_id),
        FOREIGN KEY (club_id) REFERENCES {$wpdb->prefix}clubs(club_id) ON DELETE CASCADE
    ) $charset_collate;";
    $wpdb->query($sql);

    // Table for Payment Gateways
    $table_name = $wpdb->prefix . 'payment_gateways';
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        gateway_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        club_id BIGINT(20) UNSIGNED NOT NULL,
        gateway_type VARCHAR(50) NOT NULL,
        merchant_id VARCHAR(255) DEFAULT NULL,
        merchant_key VARCHAR(255) DEFAULT NULL,
        api_key VARCHAR(255) DEFAULT NULL,
        secret_key VARCHAR(255) DEFAULT NULL,
        yoco_link VARCHAR(255) DEFAULT NULL,
        PRIMARY KEY (gateway_id),
        FOREIGN KEY (club_id) REFERENCES {$wpdb->prefix}clubs(club_id) ON DELETE CASCADE
    ) $charset_collate;";
    $wpdb->query($sql);

    // Table for Club Members
    $table_name = $wpdb->prefix . 'club_members';
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        club_id BIGINT(20) UNSIGNED NOT NULL,
        club_name VARCHAR(255) NOT NULL,
        user_name VARCHAR(255) NOT NULL,
        user_email VARCHAR(255) NOT NULL,
        role VARCHAR(255) NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        FOREIGN KEY (club_id) REFERENCES {$wpdb->prefix}clubs(club_id) ON DELETE CASCADE
    ) $charset_collate;";
    $wpdb->query($sql);
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






