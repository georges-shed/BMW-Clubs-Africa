<?php

// Hook into WordPress to create the meta box
add_action('add_meta_boxes', 'add_select_club_meta_box');

// Hook into WordPress to save the custom field data
add_action('save_post', 'save_select_club_data');

// Function to create the meta box
function add_select_club_meta_box() {
    add_meta_box(
        'select_club_meta_box', // Unique ID of the meta box
        'Select Club',          // Title of the meta box
        'select_club_meta_box_callback', // Callback function to render the meta box
        'post',                 // The screen on which the box should appear ('post' means post editor)
        'side',                 // Context where the box should appear ('side', 'normal', 'advanced')
        'high'                  // Priority of the meta box
    );
}

// Function to display the custom meta box (with dynamic dropdown)
// Function to display the custom meta box (with dynamic dropdown and "Global" option)
function select_club_meta_box_callback($post) {
    global $wpdb;
    
    // Retrieve saved club ID and club name if they exist
    $selected_club_id = get_post_meta($post->ID, '_select_club_id', true);
    $selected_club_name = get_post_meta($post->ID, '_select_club_name', true);
    
    // SQL query to fetch club_id and club_name
    $clubs = $wpdb->get_results("SELECT club_id, club_name FROM wp_clubs", ARRAY_A);
    
    // Display the dropdown field
    echo '<label for="select_club">Select Club:</label>';
    echo '<select name="select_club" id="select_club">';
    
    // Add the "Global" option
    $global_selected = ($selected_club_id === 'global') ? 'selected="selected"' : '';
    echo '<option value="global" ' . $global_selected . '>Global</option>';
    
    // Check if there are results and loop through each club
    if (!empty($clubs)) {
        foreach ($clubs as $club) {
            $selected = ($club['club_id'] == $selected_club_id) ? 'selected="selected"' : '';
            echo '<option value="' . esc_attr($club['club_id']) . '" ' . $selected . '>' . esc_html($club['club_name']) . '</option>';
        }
    } else {
        echo '<option value="">No clubs available</option>';
    }
    
    echo '</select>';
    
    // Add a nonce field for security
    wp_nonce_field('save_select_club_nonce', 'select_club_nonce');
}

// Function to save the custom field data when the post is saved
function save_select_club_data($post_id) {
    // Check for nonce security
    if (!isset($_POST['select_club_nonce']) || !wp_verify_nonce($_POST['select_club_nonce'], 'save_select_club_nonce')) {
        return $post_id;
    }
    
    // Check if auto-saving
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return $post_id;
    }
    
    // Check user permissions
    if (isset($_POST['post_type']) && 'post' == $_POST['post_type']) {
        if (!current_user_can('edit_post', $post_id)) {
            return $post_id;
        }
    }

    global $wpdb;
    
    // Validate and sanitize the input value
    if (isset($_POST['select_club'])) {
        $selected_club_id = sanitize_text_field($_POST['select_club']);
        
        // Handle the "Global" option separately
        if ($selected_club_id === 'global') {
            update_post_meta($post_id, '_select_club_id', 'global');
            update_post_meta($post_id, '_select_club_name', 'Global');
        } else {
            // Fetch the club name based on the club ID
            $club_name = $wpdb->get_var($wpdb->prepare("SELECT club_name FROM wp_clubs WHERE club_id = %d", $selected_club_id));
            
            // Save the club ID and name
            if ($club_name) {
                update_post_meta($post_id, '_select_club_id', $selected_club_id);
                update_post_meta($post_id, '_select_club_name', $club_name);
            }
        }
    }
}

