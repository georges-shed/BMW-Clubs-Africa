<?php

// Ensure this runs after WordPress is initialized
add_action('init', 'bmw_init_rides_functions');

function bmw_init_rides_functions() {
    
    // Function to display Gravity Forms based on user subscription (Car or Bike)
    function bmw_car_or_bike_gf()
    {
        global $current_user;

        // Check if the user has any active subscriptions
        $subscriptions = wcs_get_users_subscriptions($current_user->ID);

        if (empty($subscriptions)) {
            // Display text for users with no active subscription
            $getform = 'No active subscription. You need to be an active subscriber in order to edit your rides.';
        } else {
            $has_motorrad_subscription = false;

            // Check if the user has an active subscription containing the word "motorrad"
            foreach ($subscriptions as $subscription) {
                // Get the subscription items
                $items = $subscription->get_items();

                foreach ($items as $item) {
                    // Get the product name
                    $product_name = $item->get_name();

                    // Check if the product name contains the word "motorrad"
                    if (strpos(strtolower($product_name), 'motorrad') !== false) {
                        $has_motorrad_subscription = true;
                        break;
                    }
                }

                if ($has_motorrad_subscription) {
                    break; // No need to continue checking other subscriptions
                }
            }

            // Display the corresponding Gravity Form based on subscription status
            if ($has_motorrad_subscription) {
                $getform = do_shortcode('[gravityform id=14]'); // Form for "motorrad" subscription
            } else {
                $getform = do_shortcode('[gravityform id=8]'); // Default form
            }
        }

        return $getform;
    }

    add_shortcode('bmw_car_or_bike_gf_sc', 'bmw_car_or_bike_gf');

    // Populate the My Vehicles form with bike values
    if (is_user_logged_in()) {
        add_filter('gform_pre_render_14', 'pre_pop_membership_bike');
    }

    function pre_pop_membership_bike($form)
    {
        $current_user = get_current_user_id();

        $this_user_bike_1_make = get_user_meta($current_user, 'user_bike_1_make', true);
        $this_user_bike_1_model = get_user_meta($current_user, 'user_bike_1_model', true);
        $this_user_bike_1_year = get_user_meta($current_user, 'user_bike_1_year', true);
        $this_user_bike_1_registration_number = get_user_meta($current_user, 'user_bike_1_registration_number', true);

        $this_user_bike_2_make = get_user_meta($current_user, 'user_bike_2_make', true);
        $this_user_bike_2_model = get_user_meta($current_user, 'user_bike_2_model', true);
        $this_user_bike_2_year = get_user_meta($current_user, 'user_bike_2_year', true);
        $this_user_bike_2_registration_number = get_user_meta($current_user, 'user_bike_2_registration_number', true);

        $this_user_bike_3_make = get_user_meta($current_user, 'user_bike_3_make', true);
        $this_user_bike_3_model = get_user_meta($current_user, 'user_bike_3_model', true);
        $this_user_bike_3_year = get_user_meta($current_user, 'user_bike_3_year', true);
        $this_user_bike_3_registration_number = get_user_meta($current_user, 'user_bike_3_registration_number', true);

        $this_user_bike_4_make = get_user_meta($current_user, 'user_bike_4_make', true);
        $this_user_bike_4_model = get_user_meta($current_user, 'user_bike_4_model', true);
        $this_user_bike_4_year = get_user_meta($current_user, 'user_bike_4_year', true);
        $this_user_bike_4_registration_number = get_user_meta($current_user, 'user_bike_4_registration_number', true);

        foreach ($form['fields'] as &$field) {
            if ($field->id == 87) $field->defaultValue = $this_user_bike_1_make;
            if ($field->id == 88) $field->defaultValue = $this_user_bike_1_model;
            if ($field->id == 89) $field->defaultValue = $this_user_bike_1_year;
            if ($field->id == 90) $field->defaultValue = $this_user_bike_1_registration_number;

            if ($field->id == 100) $field->defaultValue = $this_user_bike_2_make;
            if ($field->id == 101) $field->defaultValue = $this_user_bike_2_model;
            if ($field->id == 102) $field->defaultValue = $this_user_bike_2_year;
            if ($field->id == 103) $field->defaultValue = $this_user_bike_2_registration_number;

            if ($field->id == 108) $field->defaultValue = $this_user_bike_3_make;
            if ($field->id == 109) $field->defaultValue = $this_user_bike_3_model;
            if ($field->id == 110) $field->defaultValue = $this_user_bike_3_year;
            if ($field->id == 111) $field->defaultValue = $this_user_bike_3_registration_number;

            if ($field->id == 113) $field->defaultValue = $this_user_bike_4_make;
            if ($field->id == 114) $field->defaultValue = $this_user_bike_4_model;
            if ($field->id == 115) $field->defaultValue = $this_user_bike_4_year;
            if ($field->id == 116) $field->defaultValue = $this_user_bike_4_registration_number;
        }

        return $form;
    }

    // Save bike data after Gravity Forms submission
    add_action('gform_after_submission_14', 'bmw_save_bike_input_fields', 10, 2);
    function bmw_save_bike_input_fields($entry, $form)
    {
        // Update Bike 1
        update_user_meta(get_current_user_id(), 'user_bike_1_make', $entry[87]);
        update_user_meta(get_current_user_id(), 'user_bike_1_model', $entry[88]);
        update_user_meta(get_current_user_id(), 'user_bike_1_year', $entry[89]);
        update_user_meta(get_current_user_id(), 'user_bike_1_registration_number', $entry[90]);

        // Update Bike 2
        update_user_meta(get_current_user_id(), 'user_bike_2_make', $entry[100]);
        update_user_meta(get_current_user_id(), 'user_bike_2_model', $entry[101]);
        update_user_meta(get_current_user_id(), 'user_bike_2_year', $entry[102]);
        update_user_meta(get_current_user_id(), 'user_bike_2_registration_number', $entry[103]);

        // Update Bike 3
        update_user_meta(get_current_user_id(), 'user_bike_3_make', $entry[108]);
        update_user_meta(get_current_user_id(), 'user_bike_3_model', $entry[109]);
        update_user_meta(get_current_user_id(), 'user_bike_3_year', $entry[110]);
        update_user_meta(get_current_user_id(), 'user_bike_3_registration_number', $entry[111]);

        // Update Bike 4
        update_user_meta(get_current_user_id(), 'user_bike_4_make', $entry[113]);
        update_user_meta(get_current_user_id(), 'user_bike_4_model', $entry[114]);
        update_user_meta(get_current_user_id(), 'user_bike_4_year', $entry[115]);
        update_user_meta(get_current_user_id(), 'user_bike_4_registration_number', $entry[116]);
    }

    // Populate the My Vehicles form with car values
    if (is_user_logged_in()) {
        add_filter('gform_pre_render_8', 'pre_pop_membership_car');
    }

    function pre_pop_membership_car($form)
    {
        $current_user = get_current_user_id();

        $this_user_car_1_make = get_user_meta($current_user, 'user_primary_vehicle', true);
        $this_user_car_1_model = get_user_meta($current_user, 'user_primary_vehicle_year', true);
        $this_user_car_1_year = get_user_meta($current_user, 'user_primary_vehicle_capa', true);
        $this_user_car_1_registration_number = get_user_meta($current_user, 'user_primary_vehicle_num_cyl', true);
        $this_user_car_1_aspiration = get_user_meta($current_user, 'user_primary_vehicle_aspiration', true);
        $this_user_car_1_typremake = get_user_meta($current_user, 'user_primary_vehicle_tyre_make', true);
        $this_user_car_1_tyretype = get_user_meta($current_user, 'user_primary_vehicle_tyre_type', true);

        foreach ($form['fields'] as &$field) {
            if ($field->id == 11) $field->defaultValue = $this_user_car_1_make;
            if ($field->id == 24) $field->defaultValue = $this_user_car_1_model;
            if ($field->id == 25) $field->defaultValue = $this_user_car_1_year;
            if ($field->id == 31) $field->defaultValue = $this_user_car_1_registration_number;
            if ($field->id == 32) $field->defaultValue = $this_user_car_1_aspiration;
            if ($field->id == 28) $field->defaultValue = $this_user_car_1_typremake;
            if ($field->id == 30) $field->defaultValue = $this_user_car_1_tyretype;
        }

        return $form;
    }

    // Save car data after Gravity Forms submission
    add_action('gform_after_submission_8', 'bmw_save_car_input_fields', 10, 2);
    function bmw_save_car_input_fields($entry, $form)
    {
        // Update Car 1
        update_user_meta(get_current_user_id(), 'user_primary_vehicle', $entry[11]);
        update_user_meta(get_current_user_id(), 'user_primary_vehicle_year', $entry[24]);
        update_user_meta(get_current_user_id(), 'user_primary_vehicle_capa', $entry[25]);
        update_user_meta(get_current_user_id(), 'user_primary_vehicle_num_cyl', $entry[31]);
        update_user_meta(get_current_user_id(), 'user_primary_vehicle_aspiration', $entry[32]);
        update_user_meta(get_current_user_id(), 'user_primary_vehicle_tyre_make', $entry[28]);
        update_user_meta(get_current_user_id(), 'user_primary_vehicle_tyre_type', $entry[30]);
    }
}
