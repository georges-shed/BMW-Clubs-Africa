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


// Add AJAX action to handle user search
add_action('wp_ajax_search_users', 'club_manager_search_users');

function club_manager_search_users() {
    // Check if search term is set
    if (!isset($_GET['search'])) {
        wp_send_json([]);
        return;
    }

    $search_term = sanitize_text_field($_GET['search']);

    // Query users based on the search term
    $users = get_users([
        'search'         => '*' . esc_attr($search_term) . '*',
        'search_columns' => ['user_login', 'user_nicename', 'display_name'],
        'number'         => 10 // Limit the number of results to avoid memory issues
    ]);

    $results = [];

    foreach ($users as $user) {
        $results[] = [
            'id'           => $user->ID,
            'display_name' => $user->display_name,
            'user_login'   => $user->user_login,
            'user_email'   => $user->user_email
        ];
    }

    wp_send_json($results);
}

// Add AJAX action to handle adding club members
add_action('wp_ajax_add_club_member', 'club_manager_add_club_member');

function club_manager_add_club_member() {
    global $wpdb;

    // Sanitize and get the posted data
    $club_id = intval($_POST['club_id']);
    $club_name = sanitize_text_field($_POST['club_name']);
    $member_name = sanitize_text_field($_POST['member_name']); // Now we receive the name
    $member_email = sanitize_email($_POST['member_email']);
    $role = sanitize_text_field($_POST['role']);

    // Check if all required fields are present
    if ($club_id && $club_name && $member_name && $member_email && $role) {
        // Insert the data into the wp_club_members table
        $wpdb->insert(
            'wp_club_members',
            array(
                'club_id' => $club_id,
                'club_name' => $club_name,
                'user_name' => $member_name, // Insert the actual user name here
                'user_email' => $member_email,
                'role' => $role,
                'created_at' => current_time('mysql') // Use WordPress's current time
            ),
            array('%d', '%s', '%s', '%s', '%s', '%s')
        );

        // Check if the insertion was successful
        if ($wpdb->insert_id) {
            wp_send_json_success();
        } else {
            wp_send_json_error();
        }
    } else {
        wp_send_json_error();
    }

    wp_die(); // Required to properly terminate the request
}

// Handle AJAX request to fetch members based on selected club
add_action('wp_ajax_fetch_club_members', 'fetch_club_members');

function fetch_club_members() {
    global $wpdb;

    // Get the club name from the AJAX request
    $club_name = sanitize_text_field($_POST['club_name']);

    // Query to fetch members from the database where the club name matches
    $members = $wpdb->get_results(
        $wpdb->prepare("SELECT id, club_name, user_name, user_email, role FROM wp_club_members WHERE club_name = %s", $club_name)
    );

    // If members are found, return the table rows
    if (!empty($members)) {
        foreach ($members as $member) {
            echo '<tr data-member-id="' . esc_attr($member->id) . '">'; // Add unique member ID to the row
            echo '<td>' . esc_html($member->club_name) . '</td>';
            echo '<td>' . esc_html($member->user_name) . '</td>';
            echo '<td>' . esc_html($member->user_email) . '</td>';
            echo '<td>' . esc_html($member->role) . '</td>';

            // Show "Full Access" if the role is "Club Manager", otherwise "Limited Access"
            if (esc_html($member->role) === 'Club Manager') {
                echo '<td>Full Access</td>';
            } else {
                echo '<td>Limited Access</td>';
            }

            // Add delete icon
            echo '<td><a href="#" class="delete-member" data-member-id="' . esc_attr($member->id) . '">❌</a></td>';

            echo '</tr>';
        }
    } else {
        echo '<tr><td colspan="6">' . __('No members found for this club.', 'club-manager') . '</td></tr>'; // Adjust colspan for the delete column
    }

    wp_die(); // Terminate the request
}


// Handle AJAX request to delete a club member
add_action('wp_ajax_delete_club_member', 'delete_club_member');

function delete_club_member() {
    global $wpdb;

    // Get the member ID from the AJAX request
    $member_id = intval($_POST['member_id']);

    // Delete the member from the database
    if ($member_id) {
        $deleted = $wpdb->delete('wp_club_members', array('id' => $member_id), array('%d'));

        if ($deleted) {
            wp_send_json_success();
        } else {
            wp_send_json_error();
        }
    } else {
        wp_send_json_error();
    }

    wp_die(); // Terminate the request
}




