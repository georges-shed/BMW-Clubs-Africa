<?php
function bmw_print_card_function() {
    global $wp;
    global $wpdb;

    // Check if the current page is the e-card page
    if (home_url($wp->request) == home_url() . '/e-card') {

        global $glob_membername, $glob_membernumber, $glob_imageClubLogo, $glob_memberof, $glob_memberdateexpires, $glob_qrCodeURL, $glob_imageSizeMember, $glob_user_ice_contact_name, $glob_user_ice_contact_number;
        $current_user = wp_get_current_user();

        // Get the current user or external user ID
        $current_user_id = isset($_GET['userIDexternal']) ? $_GET['userIDexternal'] : $current_user->ID;

        // Get membership plans
        $memberships = wc_memberships_get_membership_plans();
        $whichmembership = '';

        if (!empty($memberships)) {
            foreach ($memberships as $membership) {
                $check = wc_memberships_is_user_active_member($current_user_id, $membership->id);
                if ($check == 1) {
                    $whichmembership = $membership->id;
                    break;
                }
            }
        }

        // Get product and club details, ensure $_GET keys exist
        $clubsproductID = isset($_GET['sh']) ? base64_decode($_GET['sh']) : '';
        $memnum = isset($_GET['mx']) ? base64_decode($_GET['mx']) : '';

        // Get the club logo
        if ($clubsproductID && get_field('clublogo', $clubsproductID)) {
            $imageClubLogoID = wp_get_attachment_image_src(get_field('clublogo', $clubsproductID));
            $imageClubLogo = $imageClubLogoID[0];
            $path_parts = pathinfo($imageClubLogo);
            $imageClubLogoPNG = $path_parts['filename'] . ".png";
        } else {
            $imageClubLogoPNG = 'BMW-Clubs-Africa-259x126.png'; // Default placeholder
        }

        // Get user's subscription information
        $users_subscriptions = wcs_get_users_subscriptions($current_user_id);
        $expiry_date = '';
        $items = [];

        foreach ($users_subscriptions as $subscription) {
            if ($subscription->has_status(array('active'))) {
                $usersubscription = $subscription->get_id();
                $order = wc_get_order($usersubscription);
                $items = $order->get_items();
                $expiry_date = get_post_meta($usersubscription, '_schedule_next_payment', true);
                break;
            }
        }

        // Check if items were found
        $subscription_items = '';
        if (!empty($items)) {
            foreach ($items as $item) {
                $subscription_items .= $item->get_name() . ', ';
            }
            $subscription_items = rtrim($subscription_items, ', ');
        } else {
            $subscription_items = 'No active subscription items found';
        }

        // Get ICE contact information
        $ICEContactName = esc_attr(get_the_author_meta('user_ice_contact_name', $current_user_id));
        $glob_user_ice_contact_name = $ICEContactName;

        $ICEContactPhoneNumber = esc_attr(get_the_author_meta('user_ice_contact_number', $current_user_id));
        $glob_user_ice_contact_number = $ICEContactPhoneNumber;

        // Set global variables for display
        $glob_membernumber = isset($usersubscription) ? $usersubscription : 'N/A';
        $glob_membername = $current_user->first_name . ' ' . $current_user->last_name;
        $glob_memberdateexpires = !empty($expiry_date) ? date('Y-m-d', strtotime($expiry_date)) : 'N/A';
        $glob_memberof = $subscription_items;

        // Set the club logo path to plugin directory
        $imageClubLogo = BMW_CLUBS_AFRICA_PLUGIN_URL . 'assets/club_logos_png/' . $imageClubLogoPNG;
        $glob_imageClubLogo = $imageClubLogo;
        $glob_qrCodeURL = get_home_url() . '/check-membership/?userIDexternal=' . $current_user_id . '&memnum=' . $memnum;

        // Create the HTML output for the page
        $htmlcc = '
        <style type="text/Css">
        .entry-content table {
            border: none;
        }
        .entry-content tr td {
            padding: 3px 10px;
            border-top: none;
        }
        .test1 {
            background: #FFFFFF;
            border-collapse: collapse;
        }
        </style>

        <table style="padding:3px;border: 7px groove #1C6EA4;border-radius:5px;">
            <tr>
                <td><div style="vertical-align: middle;"><img src="' . $imageClubLogo . '" alt="Logo" width="123" /></div></td> 
                <td><div>
                        <b>' . $glob_membername . ' </b>
                        <br>Mem #: ' . $glob_membernumber . '
                        <br>' . $glob_memberof . '
                        <br>Exp: ' . $glob_memberdateexpires . '
                        <br><em>ICE Contact: ' . $ICEContactName . ' ' . $ICEContactPhoneNumber . '</em>
                    </div></td> 	
            </tr>
        </table>';

        $html = '<div class="wrap" style="width:70%;"><h1>Subscription Details</h1>
        <p>Please verify that all the information is correct.<br></p>
        <div>' . $htmlcc . '</div>';

        return $html;
    }
}

// Shortcode for displaying e-card [bmw_print_card_shortcode]
add_shortcode('bmw_print_card_shortcode', 'bmw_print_card_function');
