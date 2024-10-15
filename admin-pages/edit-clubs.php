<?php
if (!defined('ABSPATH')) {
    exit;
}

// Include function files
require_once plugin_dir_path(__FILE__) . 'includes/club-operations.php';
require_once plugin_dir_path(__FILE__) . 'includes/club-retrieval.php';
require_once plugin_dir_path(__FILE__) . 'includes/form-handler.php';


// Fetch club details if club_id is provided in URL
$club_id = isset($_GET['club_id']) ? intval($_GET['club_id']) : 0;
$club = $club_id ? get_club_details($club_id) : null;
$club_roles = $club_id ? get_club_roles($club_id) : [];
$eft_details = $club_id ? get_eft_details($club_id) : null;

// Handle form submission
handle_club_form_submission();
?>

<!-- HTML Form -->
<div class="wrap woocommerce">
    <h2 class="nav-tab-wrapper">
        <a href="?page=club-manager-clubs&tab=edit" class="nav-tab nav-tab-active"><?php _e('Edit', 'club-manager'); ?></a>
        <a href="?page=club-manager-clubs&tab=settings" class="nav-tab"><?php _e('Settings', 'club-manager'); ?></a>
    </h2>

    <div class="sub-tab-wrapper">
        <a href="#club-details" class="sub-tab sub-tab-active"><?php _e('Club Details', 'club-manager'); ?></a>
        <a href="#roles-permissions" class="sub-tab"><?php _e('Roles & Permissions', 'club-manager'); ?></a>
        <a href="#eft-details" class="sub-tab"><?php _e('EFT Details', 'club-manager'); ?></a>
        <a href="#payment-gateways" class="sub-tab"><?php _e('Payment Gateways', 'club-manager'); ?></a>
    </div>

    <div class="tab-content">
        <form method="post" class="woocommerce form">
            <!-- Club Details Tab -->
            <div id="club-details" class="sub-tab-content">
    <h2><?php _e('Club Details', 'club-manager'); ?></h2>
    <p class="form-row form-row-wide">
        <label for="club_name"><?php _e('Club Name', 'club-manager'); ?></label>
        <input type="text" class="input-text" name="club_name" id="club_name" value="<?php echo esc_attr($club ? $club->club_name : ''); ?>" required />
    </p>
    <p class="form-row form-row-wide">
        <label for="club_url"><?php _e('Home URL', 'club-manager'); ?></label>
        <input type="text" class="input-text" name="club_url" id="club_url" value="<?php echo esc_attr($club ? $club->club_url : ''); ?>" required />
    </p>
    <p class="form-row form-row-wide">
        <label for="club_logo"><?php _e('Logo', 'club-manager'); ?></label>
        <input type="hidden" name="club_logo" id="club_logo" value="<?php echo esc_attr($club ? $club->club_logo : ''); ?>" />
        <input type="file" id="club_logo_file" style="display: none;" />
        <button type="button" class="button upload_logo_button"><?php _e('Upload/Add Image', 'club-manager'); ?></button>
        <button type="button" class="button remove_logo_button"><?php _e('Remove Image', 'club-manager'); ?></button>

        <!-- Image preview -->
        <div id="club_logo_preview" style="margin-top: 10px;">
            <?php if ($club && $club->club_logo) : ?>
                <img src="<?php echo esc_url($club->club_logo); ?>" alt="Club Logo" style="max-width: 100px; max-height: 100px;">
            <?php endif; ?>
        </div>
    </p>
</div>


            <!-- Roles & Permissions Tab -->
            <div id="roles-permissions" class="sub-tab-content" style="display: none;">
    <h2><?php _e('Roles & Permissions', 'club-manager'); ?></h2>
    <p class="form-row form-row-wide">
        <label for="club_roles"><?php _e('Assigned Roles', 'club-manager'); ?></label>
        <select name="club_roles[]" id="club_roles" class="wc-enhanced-select" multiple style="width: 350px;">
            <option value="Club Manager" <?php selected(in_array('Club Manager', $club_roles)); ?>><?php _e('Club Manager', 'club-manager'); ?></option>
            <option value="Media/Social" <?php selected(in_array('Media/Social', $club_roles)); ?>><?php _e('Media/Social', 'club-manager'); ?></option>
            <option value="Treasurer" <?php selected(in_array('Treasurer', $club_roles)); ?>><?php _e('Treasurer', 'club-manager'); ?></option>
            <option value="Store Manager" <?php selected(in_array('Store Manager', $club_roles)); ?>><?php _e('Store Manager', 'club-manager'); ?></option>
        </select>
    </p>
</div>

            <!-- EFT Details Tab -->
            <div id="eft-details" class="sub-tab-content" style="display: none;">
                <h2><?php _e('EFT Details', 'club-manager'); ?></h2>
                <p class="form-row form-row-first">
                    <label for="eft_account_name"><?php _e('Account Name', 'club-manager'); ?></label>
                    <input type="text" class="input-text" name="eft_account_name" id="eft_account_name" value="<?php echo esc_attr($eft_details ? $eft_details->account_name : ''); ?>" />
                </p>
                <p class="form-row form-row-last">
                    <label for="eft_account_number"><?php _e('Account Number', 'club-manager'); ?></label>
                    <input type="text" class="input-text" name="eft_account_number" id="eft_account_number" value="<?php echo esc_attr($eft_details ? $eft_details->account_number : ''); ?>" />
                </p>
                <p class="form-row form-row-first">
                    <label for="eft_bank_name"><?php _e('Bank Name', 'club-manager'); ?></label>
                    <input type="text" class="input-text" name="eft_bank_name" id="eft_bank_name" value="<?php echo esc_attr($eft_details ? $eft_details->bank_name : ''); ?>" />
                </p>
                <p class="form-row form-row-last">
                    <label for="eft_branch_code"><?php _e('Branch Code', 'club-manager'); ?></label>
                    <input type="text" class="input-text" name="eft_branch_code" id="eft_branch_code" value="<?php echo esc_attr($eft_details ? $eft_details->branch_code : ''); ?>" />
                </p>
                <p class="form-row form-row-wide">
                    <button type="submit" class="button-primary"><?php _e('Save', 'club-manager'); ?></button>
                </p>
            </div>

            <!-- Payment Gateways Tab -->
            <div id="payment-gateways" class="sub-tab-content" style="display: none;">
                <h2><?php _e('Payment Gateways', 'club-manager'); ?></h2>
                <p class="form-row form-row-wide">
                    <label for="payment_gateway"><?php _e('Select Payment Gateway', 'club-manager'); ?></label>
                    <select name="payment_gateway" id="payment_gateway">
                        <option value=""><?php _e('Select Gateway', 'club-manager'); ?></option>
                        <option value="payfast"><?php _e('PayFast', 'club-manager'); ?></option>
                        <option value="stripe"><?php _e('Stripe', 'club-manager'); ?></option>
                        <option value="yoco"><?php _e('Yoco', 'club-manager'); ?></option>
                    </select>
                </p>
                <!-- PayFast Fields -->
                <div id="payfast_fields" style="display: none;">
                    <p class="form-row form-row-wide">
                        <label for="payfast_merchant_id"><?php _e('Merchant ID', 'club-manager'); ?></label>
                        <input type="text" class="input-text" name="payfast_merchant_id" id="payfast_merchant_id" />
                    </p>
                    <p class="form-row form-row-wide">
                        <label for="payfast_merchant_key"><?php _e('Merchant Key', 'club-manager'); ?></label>
                        <input type="text" class="input-text" name="payfast_merchant_key" id="payfast_merchant_key" />
                    </p>
                </div>
                <!-- Stripe Fields -->
                <div id="stripe_fields" style="display: none;">
                    <p class="form-row form-row-wide">
                        <label for="stripe_api_key"><?php _e('API Key', 'club-manager'); ?></label>
                        <input type="text" class="input-text" name="stripe_api_key" id="stripe_api_key" />
                    </p>
                    <p class="form-row form-row-wide">
                        <label for="stripe_secret_key"><?php _e('Secret Key', 'club-manager'); ?></label>
                        <input type="text" class="input-text" name="stripe_secret_key" id="stripe_secret_key" />
                    </p>
                </div>
                <!-- Yoco Fields -->
                <div id="yoco_fields" style="display: none;">
                    <p class="form-row form-row-wide">
                        <label for="yoco_link"><?php _e('Yoco Link', 'club-manager'); ?></label>
                        <input type="text" class="input-text" name="yoco_link" id="yoco_link" />
                    </p>
                </div>
            </div>
        </form>
    </div>
</div>


