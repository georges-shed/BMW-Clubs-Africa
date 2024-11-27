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
$gateway_details = $wpdb->get_row($wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}payment_gateways WHERE club_id = %d",
    $club_id
));

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

    <!-- Tabs -->
    <h2 class="nav-tab-wrapper">
        <a href="#tab-club-details" class="nav-tab nav-tab-active"><?php _e('Club Details', 'club-manager'); ?></a>
        <a href="#tab-eft-details" class="nav-tab"><?php _e('EFT Details', 'club-manager'); ?></a>
        <a href="#tab-payment-gateways" class="nav-tab"><?php _e('Payment Gateways', 'club-manager'); ?></a>
        <a href="#tab-members-details" class="nav-tab"><?php _e('Members', 'club-manager'); ?></a>
    </h2>

    <!-- Club Details Tab -->
<div id="tab-club-details" class="tab-content" style="display: block;">
    <form method="post">
        <table class="form-table">
            <tr>
                <th><label for="club_name"><?php _e('Club Name', 'club-manager'); ?></label></th>
                <td><input type="text" name="club_name" id="club_name" value="<?php echo esc_attr($club_name); ?>" class="regular-text" required></td>
            </tr>
            <tr>
                <th><label for="club_url"><?php _e('Club URL', 'club-manager'); ?></label></th>
                <td>
                    <input type="text" name="club_url" id="club_url" value="<?php echo esc_url($club_url); ?>" class="regular-text">
                    <p class="description"><?php echo __('URL should start with a slash (/) and use hyphens for spaces.', 'club-manager'); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="club_logo"><?php _e('Logo', 'club-manager'); ?></label></th>
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
    </form>
</div>

    <!-- EFT Details Tab -->
    <div id="tab-eft-details" class="tab-content" style="display: none;">
        <form method="post">
            <table class="form-table">
                <tr>
                    <th><label for="eft_account_name"><?php _e('Account Name', 'club-manager'); ?></label></th>
                    <td><input type="text" name="eft_account_name" id="eft_account_name" value="<?php echo $eft_account_name; ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="eft_account_number"><?php _e('Account Number', 'club-manager'); ?></label></th>
                    <td><input type="text" name="eft_account_number" id="eft_account_number" value="<?php echo $eft_account_number; ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="eft_bank_name"><?php _e('Bank Name', 'club-manager'); ?></label></th>
                    <td><input type="text" name="eft_bank_name" id="eft_bank_name" value="<?php echo $eft_bank_name; ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="eft_branch_code"><?php _e('Branch Code', 'club-manager'); ?></label></th>
                    <td><input type="text" name="eft_branch_code" id="eft_branch_code" value="<?php echo $eft_branch_code; ?>" class="regular-text"></td>
                </tr>
            </table>
        </form>
    </div>

    <!-- Payment Gateways Tab -->
    <div id="tab-payment-gateways" class="tab-content" style="display: none;">
        <h2>Lorem, ipsum dolor sit amet consectetur adipisicing elit. Tenetur ipsam iusto praesentium nobis excepturi rem.</h2>
        <form method="post">
            <table class="form-table">
                <tr>
                    <th><label for="gateway_type"><?php _e('Gateway Type', 'club-manager'); ?></label></th>
                    <td>
                        <select id="gateway_type" name="gateway_type" class="regular-text">
                            <option value=""><?php _e('Select a gateway', 'club-manager'); ?></option>
                            <option value="yoco" <?php selected($gateway_type, 'yoco'); ?>><?php _e('Yoco', 'club-manager'); ?></option>
                            <option value="payfast" <?php selected($gateway_type, 'payfast'); ?>><?php _e('PayFast', 'club-manager'); ?></option>
                            <option value="stripe" <?php selected($gateway_type, 'stripe'); ?>><?php _e('Stripe', 'club-manager'); ?></option>
                        </select>
                    </td>
                </tr>
                <tr id="yoco_link_row" style="display: none;">
                    <th><label for="yoco_link"><?php _e('Yoco Link', 'club-manager'); ?></label></th>
                    <td><input type="text" name="yoco_link" id="yoco_link" value="<?php echo $yoco_link; ?>" class="regular-text"></td>
                </tr>
                <tr id="merchant_id_row" style="display: none;">
                    <th><label for="merchant_id"><?php _e('Merchant ID', 'club-manager'); ?></label></th>
                    <td><input type="text" name="merchant_id" id="merchant_id" value="<?php echo $merchant_id; ?>" class="regular-text"></td>
                </tr>
                <tr id="merchant_key_row" style="display: none;">
                    <th><label for="merchant_key"><?php _e('Merchant Key', 'club-manager'); ?></label></th>
                    <td><input type="text" name="merchant_key" id="merchant_key" value="<?php echo $merchant_key; ?>" class="regular-text"></td>
                </tr>
                <tr id="api_key_row" style="display: none;">
                    <th><label for="api_key"><?php _e('API Key', 'club-manager'); ?></label></th>
                    <td><input type="text" name="api_key" id="api_key" value="<?php echo $api_key; ?>" class="regular-text"></td>
                </tr>
                <tr id="secret_key_row" style="display: none;">
                    <th><label for="secret_key"><?php _e('Secret Key', 'club-manager'); ?></label></th>
                    <td><input type="text" name="secret_key" id="secret_key" value="<?php echo $secret_key; ?>" class="regular-text"></td>
                </tr>
            </table>
        </form>
    </div>

    <!-- Members Details Tab -->
    <div id="tab-members-details" class="tab-content" style="display: none;">
        <div>
            <?php require_once CLUB_MANAGER_PLUGIN_DIR . 'admin-pages/includes/add-club-members.php'; ?>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tabs = document.querySelectorAll('.nav-tab');
        const contents = document.querySelectorAll('.tab-content');

        tabs.forEach((tab, i) => {
            tab.addEventListener('click', function (e) {
                e.preventDefault();
                tabs.forEach(tab => tab.classList.remove('nav-tab-active'));
                contents.forEach(content => (content.style.display = "none"));

                this.classList.add('nav-tab-active');
                contents[i].style.display = "block";
            });
        });

        // Gateway-specific fields toggle
        function toggleGatewayFields(gateway) {
            document.getElementById('yoco_link_row').style.display = gateway === 'yoco' ? 'table-row' : 'none';
            document.getElementById('merchant_id_row').style.display = gateway === 'payfast' ? 'table-row' : 'none';
            document.getElementById('merchant_key_row').style.display = gateway === 'payfast' ? 'table-row' : 'none';
            document.getElementById('api_key_row').style.display = gateway === 'stripe' ? 'table-row' : 'none';
            document.getElementById('secret_key_row').style.display = gateway === 'stripe' ? 'table-row' : 'none';
        }

        const gatewaySelect = document.getElementById('gateway_type');
        gatewaySelect && gatewaySelect.addEventListener('change', function () {
            toggleGatewayFields(this.value);
        });

        toggleGatewayFields(gatewaySelect ? gatewaySelect.value : '');
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const uploadButton = document.querySelector('.upload-image');
        const removeButton = document.querySelector('.remove-image');
        const logoInput = document.getElementById('club_logo');
        const logoPreview = document.getElementById('club-logo-preview');

        // Open WordPress media uploader
        uploadButton.addEventListener('click', function (e) {
            e.preventDefault();
            const frame = wp.media({
                title: '<?php echo __('Select Logo', 'club-manager'); ?>',
                button: { text: '<?php echo __('Use This Logo', 'club-manager'); ?>' },
                multiple: false
            });

            frame.on('select', function () {
                const attachment = frame.state().get('selection').first().toJSON();
                logoInput.value = attachment.url;
                logoPreview.src = attachment.url;
                logoPreview.style.display = 'block';
                removeButton.style.display = 'inline-block';
            });

            frame.open();
        });

        // Remove logo
        removeButton.addEventListener('click', function (e) {
            e.preventDefault();
            logoInput.value = '';
            logoPreview.style.display = 'none';
            removeButton.style.display = 'none';
        });
    });
</script>
