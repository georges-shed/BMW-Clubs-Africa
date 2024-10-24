<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Check if the club_id is provided
if (!isset($_GET['club_id']) || empty($_GET['club_id'])) {
    echo __('No club ID provided.', 'club-manager');
    exit;
}

global $wpdb;
$club_id = intval($_GET['club_id']);

// Fetch the club details from the database
$club = $wpdb->get_row($wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}clubs WHERE club_id = %d",
    $club_id
));

// Set default values for club name, URL, and logo
$club_name = $club ? esc_attr($club->club_name) : '';
$club_url = $club ? esc_attr($club->club_url) : '/';
$club_logo = $club ? esc_url($club->club_logo) : '';

// Fetch EFT details
$eft_details = $wpdb->get_row($wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}eft_details WHERE club_id = %d",
    $club_id
));

// Set default values for EFT details
$eft_account_name = $eft_details ? esc_attr($eft_details->account_name) : '';
$eft_account_number = $eft_details ? esc_attr($eft_details->account_number) : '';
$eft_bank_name = $eft_details ? esc_attr($eft_details->bank_name) : '';
$eft_branch_code = $eft_details ? esc_attr($eft_details->branch_code) : '';

// Fetch existing payment gateway details
$gateway_details = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}payment_gateways WHERE club_id = %d", $club_id));

// Set default values for payment gateway fields
$gateway_type  = $gateway_details ? esc_attr($gateway_details->gateway_type) : '';
$merchant_id   = $gateway_details ? esc_attr($gateway_details->merchant_id) : '';
$merchant_key  = $gateway_details ? esc_attr($gateway_details->merchant_key) : '';
$api_key       = $gateway_details ? esc_attr($gateway_details->api_key) : '';
$secret_key    = $gateway_details ? esc_attr($gateway_details->secret_key) : '';
$yoco_link     = $gateway_details ? esc_attr($gateway_details->yoco_link) : '';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_club_details'])) {
    $club_name = sanitize_text_field($_POST['club_name']);
    $club_url = sanitize_text_field($_POST['club_url']);
    $club_logo = esc_url_raw($_POST['club_logo']);
    $eft_account_name = sanitize_text_field($_POST['eft_account_name']);
    $eft_account_number = sanitize_text_field($_POST['eft_account_number']);
    $eft_bank_name = sanitize_text_field($_POST['eft_bank_name']);
    $eft_branch_code = sanitize_text_field($_POST['eft_branch_code']);
    $gateway_type  = sanitize_text_field($_POST['gateway_type']);
    $merchant_id   = sanitize_text_field($_POST['merchant_id']);
    $merchant_key  = sanitize_text_field($_POST['merchant_key']);
    $api_key       = sanitize_text_field($_POST['api_key']);
    $secret_key    = sanitize_text_field($_POST['secret_key']);
    $yoco_link     = sanitize_text_field($_POST['yoco_link']);
    
    // Update the club in the database
    $wpdb->update(
        "{$wpdb->prefix}clubs",
        array(
            'club_name' => $club_name,
            'club_url' => $club_url,
            'club_logo' => $club_logo
        ),
        array('club_id' => $club_id),
        array('%s', '%s', '%s'),
        array('%d')
    );

    // Update EFT details in the database
    if ($eft_details) {
        $wpdb->update(
            "{$wpdb->prefix}eft_details",
            array(
                'account_name' => $eft_account_name,
                'account_number' => $eft_account_number,
                'bank_name' => $eft_bank_name,
                'branch_code' => $eft_branch_code
            ),
            array('club_id' => $club_id),
            array('%s', '%s', '%s', '%s'),
            array('%d')
        );
    } else {
        $wpdb->insert(
            "{$wpdb->prefix}eft_details",
            array(
                'club_id' => $club_id,
                'account_name' => $eft_account_name,
                'account_number' => $eft_account_number,
                'bank_name' => $eft_bank_name,
                'branch_code' => $eft_branch_code
            ),
            array('%d', '%s', '%s', '%s', '%s')
        );
    }

    // Update payment gateway details in the database
    if ($gateway_details) {
        // Update existing gateway
        $wpdb->update(
            "{$wpdb->prefix}payment_gateways",
            [
                'gateway_type'  => $gateway_type,
                'merchant_id'   => $merchant_id,
                'merchant_key'  => $merchant_key,
                'api_key'       => $api_key,
                'secret_key'    => $secret_key,
                'yoco_link'     => $yoco_link,
            ],
            ['club_id' => $club_id]
        );
    } else {
        // Insert new gateway
        $wpdb->insert(
            "{$wpdb->prefix}payment_gateways",
            [
                'club_id'       => $club_id,
                'gateway_type'  => $gateway_type,
                'merchant_id'   => $merchant_id,
                'merchant_key'  => $merchant_key,
                'api_key'       => $api_key,
                'secret_key'    => $secret_key,
                'yoco_link'     => $yoco_link,
            ]
        );
    }

    echo '<div class="notice notice-success is-dismissible"><p>' . __('Club updated successfully.', 'club-manager') . '</p></div>';

    // Redirect to avoid form resubmission on page reload
    wp_redirect(add_query_arg(['club_id' => $club_id, 'updated' => true], $_SERVER['REQUEST_URI']));
    exit;
}

?>

<div class="wrap">
    <h1><?php echo __('Edit Club', 'club-manager'); ?></h1>

    <form method="post">

        <!-- Club Details Section -->
        <div id="club-details" class="postbox">
            <div class="postbox-header" style="background-color: #f7f7f7; border-bottom: 1px solid #e1e1e1; padding: 0px 10px;">
                <h2 class="hndle"><?php echo __('Club Details', 'club-manager'); ?></h2>
            </div>
            <div class="inside">
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="club_name"><?php echo __('Club Name', 'club-manager'); ?></label>
                        </th>
                        <td>
                            <input name="club_name" type="text" id="club_name" value="<?php echo esc_attr($club_name); ?>" class="regular-text" required />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="club_url"><?php echo __('Club URL', 'club-manager'); ?></label>
                        </th>
                        <td>
                            <input name="club_url" type="text" id="club_url" value="<?php echo esc_url($club_url); ?>" class="regular-text" />
                            <p class="description"><?php echo __('URL should start with a slash (/) and use hyphens for spaces.', 'club-manager'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="club_logo"><?php echo __('Logo', 'club-manager'); ?></label>
                        </th>
                        <td>
                            <div class="club-logo-wrapper">
                                <img id="club-logo-preview" src="<?php echo $club_logo; ?>" style="max-width: 150px; <?php echo !$club_logo ? 'display:none;' : ''; ?>" />
                                <input type="hidden" id="club_logo" name="club_logo" value="<?php echo $club_logo; ?>" />
                                <button type="button" class="button upload-image"><?php echo __('Change Image', 'club-manager'); ?></button>
                                <button type="button" class="button remove-image" style="<?php echo !$club_logo ? 'display:none;' : ''; ?>"><?php echo __('Remove Image', 'club-manager'); ?></button>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- EFT Details Section -->
        <div id="eft-details" class="postbox">
            <div class="postbox-header" style="background-color: #f7f7f7; border-bottom: 1px solid #e1e1e1; padding: 0px 10px;">
                <h2 class="hndle"><?php echo __('EFT Details', 'club-manager'); ?></h2>
            </div>
            <div class="inside">
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="eft_account_name"><?php echo __('Account Name', 'club-manager'); ?></label>
                        </th>
                        <td>
                            <input name="eft_account_name" type="text" id="eft_account_name" value="<?php echo esc_attr($eft_account_name); ?>" class="regular-text" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="eft_account_number"><?php echo __('Account Number', 'club-manager'); ?></label>
                        </th>
                        <td>
                            <input name="eft_account_number" type="text" id="eft_account_number" value="<?php echo esc_attr($eft_account_number); ?>" class="regular-text" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="eft_bank_name"><?php echo __('Bank Name', 'club-manager'); ?></label>
                        </th>
                        <td>
                            <input name="eft_bank_name" type="text" id="eft_bank_name" value="<?php echo esc_attr($eft_bank_name); ?>" class="regular-text" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="eft_branch_code"><?php echo __('Branch Code', 'club-manager'); ?></label>
                        </th>
                        <td>
                            <input name="eft_branch_code" type="text" id="eft_branch_code" value="<?php echo esc_attr($eft_branch_code); ?>" class="regular-text" />
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Payment Gateways Section -->
        <div id="payment-gateways" class="postbox">
            <div class="postbox-header" style="background-color: #f7f7f7; border-bottom: 1px solid #e1e1e1; padding: 0px 10px;">
                <h2 class="hndle"><?php echo __('Payment Gateways', 'club-manager'); ?></h2>
            </div>
            <div class="inside">
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="gateway_type"><?php echo __('Gateway Type', 'club-manager'); ?></label>
                        </th>
                        <td>
                            <select id="gateway_type" name="gateway_type" class="regular-text" required>
                                <option value=""><?php _e('Select a gateway', 'club-manager'); ?></option>
                                <option value="yoco" <?php selected($gateway_type, 'yoco'); ?>><?php _e('Yoco', 'club-manager'); ?></option>
                                <option value="payfast" <?php selected($gateway_type, 'payfast'); ?>><?php _e('PayFast', 'club-manager'); ?></option>
                                <option value="stripe" <?php selected($gateway_type, 'stripe'); ?>><?php _e('Stripe', 'club-manager'); ?></option>
                            </select>
                        </td>
                    </tr>

                    <!-- Yoco specific field -->
                    <tr id="yoco_link_row" style="display:none;">
                        <th scope="row">
                            <label for="yoco_link"><?php echo __('Yoco Link', 'club-manager'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="yoco_link" name="yoco_link" value="<?php echo $yoco_link; ?>" class="regular-text" />
                        </td>
                    </tr>

                    <!-- PayFast specific fields -->
                    <tr id="merchant_id_row" style="display:none;">
                        <th scope="row">
                            <label for="merchant_id"><?php echo __('Merchant ID', 'club-manager'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="merchant_id" name="merchant_id" value="<?php echo $merchant_id; ?>" class="regular-text" />
                        </td>
                    </tr>

                    <tr id="merchant_key_row" style="display:none;">
                        <th scope="row">
                            <label for="merchant_key"><?php echo __('Merchant Key', 'club-manager'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="merchant_key" name="merchant_key" value="<?php echo $merchant_key; ?>" class="regular-text" />
                        </td>
                    </tr>

                    <!-- Stripe specific fields -->
                    <tr id="api_key_row" style="display:none;">
                        <th scope="row">
                            <label for="api_key"><?php echo __('API Key', 'club-manager'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="api_key" name="api_key" value="<?php echo $api_key; ?>" class="regular-text" />
                        </td>
                    </tr>

                    <tr id="secret_key_row" style="display:none;">
                        <th scope="row">
                            <label for="secret_key"><?php echo __('Secret Key', 'club-manager'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="secret_key" name="secret_key" value="<?php echo $secret_key; ?>" class="regular-text" />
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Submit Button -->
        <input type="hidden" name="save_club_details" value="1" />
        <?php submit_button(__('Update Club', 'club-manager')); ?>
    </form>

    <!-- Members Section -->
    <div id="members-details" class="postbox">
        <div class="postbox-header" style="background-color: #f7f7f7; border-bottom: 1px solid #e1e1e1; padding: 0px 10px;">
            <h2 class="hndle"><?php echo __('Members', 'club-manager'); ?></h2>
        </div>
        <div class="inside">
            <?php require_once CLUB_MANAGER_PLUGIN_DIR . 'admin-pages/includes/add-club-members.php'; ?>
        </div>
    </div>
</div>

<style>
    .postbox{
        width: 85% !important;
        margin-top: 25px;
    }
</style>
<script>
    jQuery(document).ready(function($) {
        function toggleFields(gateway) {
            $('#merchant_id_row, #merchant_key_row, #api_key_row, #secret_key_row, #yoco_link_row').hide();
            if (gateway === 'yoco') {
                $('#yoco_link_row').show();
            } else if (gateway === 'payfast') {
                $('#merchant_id_row, #merchant_key_row').show();
            } else if (gateway === 'stripe') {
                $('#api_key_row, #secret_key_row').show();
            }
        }

        // Show/hide fields based on the selected gateway on load
        toggleFields($('#gateway_type').val());

        // Listen for gateway type change
        $('#gateway_type').change(function() {
            toggleFields($(this).val());
        });
    });
</script>
