<?php
// Prevent direct access to this file
if (!defined('ABSPATH')) {
    exit;
}

// Function to create admin menu and submenus
function club_manager_menu() {
    // Add main menu and make "Clubs" the main content page
    add_menu_page(
        __('Club Manager', 'club-manager'), // Page title
        __('Club Manager', 'club-manager'), // Menu title
        'manage_options', // Capability
        'club-manager-clubs', // Use 'club-manager-clubs' as the Menu slug to avoid the extra submenu
        'club_manager_clubs_page', // Function to display page content for Clubs
        'dashicons-groups', // Dashicon for club icon
        6 // Position in the admin menu
    );

    // Add submenu for "Clubs" (this will effectively be the main page as well)
    add_submenu_page(
        'club-manager-clubs', // Parent slug (main menu)
        __('Clubs', 'club-manager'), // Page title
        __('Clubs <span class="update-plugins count-36"><span class="plugin-count">36</span></span>', 'club-manager'), // Submenu title with notification badge
        'manage_options', // Capability
        'club-manager-clubs', // Submenu slug
        'club_manager_clubs_page' // Function to display page content
    );

    // Add submenu for "Add New Club"
    add_submenu_page(
        'club-manager-clubs', // Parent slug (main menu)
        __('Add New Club', 'club-manager'), // Page title
        __('Add New Club', 'club-manager'), // Submenu title
        'manage_options', // Capability
        'club-manager-edit-club', // Submenu slug
        'club_manager_edit_club_page' // Function to display page content
    );

    // Add submenu for "Add Club Members"
    add_submenu_page(
        'club-manager-clubs', // Parent slug (main menu)
        __('Add Club Members', 'club-manager'), // Page title
        __('Add Club Members', 'club-manager'), // Submenu title
        'manage_options', // Capability
        'club-manager-add-club-members', // Submenu slug
        'club_manager_add_club_members_page' // Function to display page content
    );

    // Add submenu for "Settings"
    add_submenu_page(
        'club-manager-clubs', // Parent slug (main menu)
        __('Settings', 'club-manager'), // Page title
        __('Settings', 'club-manager'), // Submenu title
        'manage_options', // Capability
        'club-manager-settings', // Submenu slug
        'club_manager_settings_page' // Function to display page content
    );
}

// Function to include the "Clubs" page
function club_manager_clubs_page() {
    require_once CLUB_MANAGER_PLUGIN_DIR . 'admin-pages/clubs.php';
}

// Function to include the "Add New Club" page
function club_manager_edit_club_page() {
    require_once CLUB_MANAGER_PLUGIN_DIR . 'admin-pages/edit-clubs.php';
}

// Function to include the "Add Club Members" page
function club_manager_add_club_members_page() {
    require_once CLUB_MANAGER_PLUGIN_DIR . 'admin-pages/add-club-members.php';
}


// Function to include the "Settings" page
function club_manager_settings_page() {
    require_once CLUB_MANAGER_PLUGIN_DIR . 'admin-pages/settings.php';
}

// Hook to add the menu in the WordPress dashboard
add_action('admin_menu', 'club_manager_menu');
