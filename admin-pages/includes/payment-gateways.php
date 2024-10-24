<?php
// Ensure to include this within a WordPress environment
defined('ABSPATH') || exit;

// Fetch club ID from the URL
$club_id = isset($_GET['club_id']) ? intval($_GET['club_id']) : 0;
if (!$club_id) {
    echo '<p>' . __('No club ID provided.', 'textdomain') . '</p>';
    return;
}

// Fetch existing payment gateway details for the club if available
global $wpdb;
$gateway_details = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}payment_gateways WHERE club_id = %d", $club_id ) );

// Set default values for payment gateway fields
$gateway_type  = $gateway_details ? esc_attr($gateway_details->gateway_type) : '';
$merchant_id   = $gateway_details ? esc_attr($gateway_details->merchant_id) : '';
$merchant_key  = $gateway_details ? esc_attr($gateway_details->merchant_key) : '';
$api_key       = $gateway_details ? esc_attr($gateway_details->api_key) : '';
$secret_key    = $gateway_details ? esc_attr($gateway_details->secret_key) : '';
$yoco_link     = $gateway_details ? esc_attr($gateway_details->yoco_link) : '';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_payment_gateway'])) {
    $gateway_type  = sanitize_text_field($_POST['gateway_type']);
    $merchant_id   = sanitize_text_field($_POST['merchant_id']);
    $merchant_key  = sanitize_text_field($_POST['merchant_key']);
    $api_key       = sanitize_text_field($_POST['api_key']);
    $secret_key    = sanitize_text_field($_POST['secret_key']);
    $yoco_link     = sanitize_text_field($_POST['yoco_link']);

    // Insert or update the payment gateway details in the database
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

    // Redirect to avoid form resubmission
    wp_redirect(add_query_arg(['club_id' => $club_id, 'updated' => true], $_SERVER['REQUEST_URI']));
    exit;
}

?>
<?php if (isset($_GET['updated']) && $_GET['updated'] == true): ?>
    <div class="notice notice-success is-dismissible">
        <p><?php _e('Payment gateway details saved successfully!', 'textdomain'); ?></p>
    </div>
<?php endif; ?>

<form method="POST" action="">
    <table class="form-table">
        <tr>
            <th scope="row">
                <label for="gateway_type"><?php _e('Gateway Type', 'textdomain'); ?></label>
            </th>
            <td>
                <select id="gateway_type" name="gateway_type" class="regular-text" required>
                    <option value=""><?php _e('Select a gateway', 'textdomain'); ?></option>
                    <option value="yoco" <?php selected($gateway_type, 'yoco'); ?>><?php _e('Yoco', 'textdomain'); ?></option>
                    <option value="payfast" <?php selected($gateway_type, 'payfast'); ?>><?php _e('PayFast', 'textdomain'); ?></option>
                    <option value="stripe" <?php selected($gateway_type, 'stripe'); ?>><?php _e('Stripe', 'textdomain'); ?></option>
                </select>
            </td>
        </tr>

        <tr id="merchant_id_row" style="display: none;">
            <th scope="row">
                <label for="merchant_id"><?php _e('Merchant ID', 'textdomain'); ?></label>
            </th>
            <td>
                <input type="text" id="merchant_id" name="merchant_id" value="<?php echo $merchant_id; ?>" class="regular-text" />
            </td>
        </tr>

        <tr id="merchant_key_row" style="display: none;">
            <th scope="row">
                <label for="merchant_key"><?php _e('Merchant Key', 'textdomain'); ?></label>
            </th>
            <td>
                <input type="text" id="merchant_key" name="merchant_key" value="<?php echo $merchant_key; ?>" class="regular-text" />
            </td>
        </tr>

        <tr id="api_key_row" style="display: none;">
            <th scope="row">
                <label for="api_key"><?php _e('API Key', 'textdomain'); ?></label>
            </th>
            <td>
                <input type="text" id="api_key" name="api_key" value="<?php echo $api_key; ?>" class="regular-text" />
            </td>
        </tr>

        <tr id="secret_key_row" style="display: none;">
            <th scope="row">
                <label for="secret_key"><?php _e('Secret Key', 'textdomain'); ?></label>
            </th>
            <td>
                <input type="text" id="secret_key" name="secret_key" value="<?php echo $secret_key; ?>" class="regular-text" />
            </td>
        </tr>

        <tr id="yoco_link_row" style="display: none;">
            <th scope="row">
                <label for="yoco_link"><?php _e('Yoco Link', 'textdomain'); ?></label>
            </th>
            <td>
                <input type="text" id="yoco_link" name="yoco_link" value="<?php echo $yoco_link; ?>" class="regular-text" />
            </td>
        </tr>
    </table>

    <input type="hidden" name="club_id" value="<?php echo $club_id; ?>" />
    <input type="hidden" name="save_payment_gateway" value="1" />
    <p class="submit">
        <input type="submit" class="button button-primary" value="<?php _e('Save Changes', 'textdomain'); ?>" />
    </p>
</form>

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
