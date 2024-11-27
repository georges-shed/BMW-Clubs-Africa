<?php
// Prevent direct access to the file
defined('ABSPATH') || exit;

// Add AJAX action to handle user search
add_action('wp_ajax_search_users', 'club_manager_search_users');

function club_manager_search_users() {
    if (!isset($_GET['search'])) {
        wp_send_json([]);
        return;
    }

    $search_term = sanitize_text_field($_GET['search']);
    $users = get_users([
        'search'         => '*' . esc_attr($search_term) . '*',
        'search_columns' => ['user_login', 'user_nicename', 'display_name'],
        'number'         => 10
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

    $club_id = intval($_POST['club_id']);
    $club_name = sanitize_text_field($_POST['club_name']);
    $member_name = sanitize_text_field($_POST['member_name']);
    $member_email = sanitize_email($_POST['member_email']);
    $role = sanitize_text_field($_POST['role']);

    if ($club_id && $club_name && $member_name && $member_email && $role) {
        $wpdb->insert(
            "{$wpdb->prefix}club_members",
            [
                'club_id'    => $club_id,
                'club_name'  => $club_name,
                'user_name'  => $member_name,
                'user_email' => $member_email,
                'role'       => $role,
                'created_at' => current_time('mysql')
            ],
            ['%d', '%s', '%s', '%s', '%s', '%s']
        );

        if ($wpdb->insert_id) {
            wp_send_json_success();
        } else {
            wp_send_json_error();
        }
    } else {
        wp_send_json_error();
    }
    wp_die();
}

// Handle AJAX request to fetch members based on selected club
add_action('wp_ajax_fetch_club_members', 'fetch_club_members');

function fetch_club_members() {
    global $wpdb;

    $club_id = isset($_POST['club_id']) ? intval($_POST['club_id']) : 0;
    if (!$club_id) {
        echo '<tr><td colspan="6">' . __('Invalid club ID.', 'club-manager') . '</td></tr>';
        wp_die();
    }

    $members = $wpdb->get_results(
        $wpdb->prepare("SELECT id, club_name, user_name, user_email, role FROM {$wpdb->prefix}club_members WHERE club_id = %d", $club_id)
    );

    if (!empty($members)) {
        foreach ($members as $member) {
            echo '<tr data-member-id="' . esc_attr($member->id) . '">';
            echo '<td style="border: 1px solid #ddd; padding: 8px;">' . esc_html($member->club_name) . '</td>';
            echo '<td style="border: 1px solid #ddd; padding: 8px;">' . esc_html($member->user_name) . '</td>';
            echo '<td style="border: 1px solid #ddd; padding: 8px;">' . esc_html($member->user_email) . '</td>';
            echo '<td style="border: 1px solid #ddd; padding: 8px;">' . esc_html($member->role) . '</td>';
            echo '<td style="border: 1px solid #ddd; padding: 8px;">' . (esc_html($member->role) === 'Club Manager' ? 'Full Access' : 'Limited Access') . '</td>';
            echo '<td style="border: 1px solid #ddd; padding: 8px; text-align: center;"><a href="#" class="delete-member" data-member-id="' . esc_attr($member->id) . '" style="display: inline-block; width: 100%; color: red;">❌</a></td>';
            echo '</tr>';
        }
    } else {
        echo '<tr><td colspan="6" style="border: 1px solid #ddd; padding: 8px;">' . __('No members found for this club.', 'club-manager') . '</td></tr>';
    }
    wp_die();
}

// Handle AJAX request to delete a club member
add_action('wp_ajax_delete_club_member', 'delete_club_member');

function delete_club_member() {
    global $wpdb;

    $member_id = intval($_POST['member_id']);
    if ($member_id) {
        $deleted = $wpdb->delete("{$wpdb->prefix}club_members", ['id' => $member_id], ['%d']);

        if ($deleted) {
            wp_send_json_success();
        } else {
            wp_send_json_error();
        }
    } else {
        wp_send_json_error();
    }
    wp_die();
}
?>
