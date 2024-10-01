<?php
// Prevent direct access to this file
if (!defined('ABSPATH')) {
    exit;
}

// Function to create admin menu and submenus
function bmw_clubs_africa_menu() {
    // Add main menu
    add_menu_page(
        __('BMW Clubs Africa', 'bmw-clubs-africa'), // Page title
        __('BMW Clubs Africa', 'bmw-clubs-africa'), // Menu title
        'manage_options', // Capability
        'bmw-clubs-africa', // Menu slug
        'bmw_clubs_africa_dashboard_page', // Function to display page content
        'dashicons-car', // Dashicon for car
        6 // Position in the admin menu
    );

    // Add submenu for "Club Events"
    add_submenu_page(
        'bmw-clubs-africa', // Parent slug (main menu)
        __('Club Events', 'bmw-clubs-africa'), // Page title
        __('Club Events', 'bmw-clubs-africa'), // Submenu title
        'manage_options', // Capability
        'bmw-club-events', // Submenu slug
        'bmw_club_events_page' // Function to display page content
    );

    // Add submenu for "Club Members"
    add_submenu_page(
        'bmw-clubs-africa', // Parent slug (main menu)
        __('Club Members', 'bmw-clubs-africa'), // Page title
        __('Club Members', 'bmw-clubs-africa'), // Submenu title
        'manage_options', // Capability
        'bmw-club-members', // Submenu slug
        'bmw_club_members_page' // Function to display page content
    );
}

// Function to include the BMW Clubs Africa Dashboard page
function bmw_clubs_africa_dashboard_page() {
    require_once BMW_CLUBS_AFRICA_PLUGIN_DIR . 'admin-pages/dashboard-plugin.php';
}

// Function to include the "Club Events" page
function bmw_club_events_page() {
    require_once BMW_CLUBS_AFRICA_PLUGIN_DIR . 'admin-pages/club-events.php';
}

// Function to include the "Club Members" page
function bmw_club_members_page() {
    require_once BMW_CLUBS_AFRICA_PLUGIN_DIR . 'admin-pages/club-members.php';
}

// Hook to add the menu in the WordPress dashboard
add_action('admin_menu', 'bmw_clubs_africa_menu');
