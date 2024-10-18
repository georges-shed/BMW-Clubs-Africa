<?php
if (!defined('ABSPATH')) {
    exit;
}

// Include function files
require_once plugin_dir_path(__FILE__) . 'includes/club-operations.php';
require_once plugin_dir_path(__FILE__) . 'includes/club-retrieval.php';
require_once plugin_dir_path(__FILE__) . 'includes/form-handler.php';
require_once plugin_dir_path(__FILE__) . 'includes/add-yoco.php'; // Include the Yoco function
// Call the Yoco link addition function
fetch_and_add_yoco_links();

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
        <a href="#edit" class="nav-tab nav-tab-active" id="edit-tab"><?php _e('Edit', 'club-manager'); ?></a>
        <a href="#settings" class="nav-tab" id="settings-tab"><?php _e('Settings', 'club-manager'); ?></a>
        <a href="#add-members" class="nav-tab" id="add-members-tab"><?php _e('Add Club Members', 'club-manager'); ?></a>
    </h2>

    <div class="tab-content">
        <!-- Edit Tab Content -->
        <div id="edit-content" class="tab-pane active">
        <div class="sub-tab-wrapper">
        <a href="#club-details" class="sub-tab sub-tab-active" id="club-details-tab"><?php _e('Club Details', 'club-manager'); ?></a>
        <a href="#eft-details" class="sub-tab" id="eft-details-tab"><?php _e('EFT Details', 'club-manager'); ?></a>
        <a href="#payment-gateways" class="sub-tab" id="payment-gateways-tab"><?php _e('Payment Gateways', 'club-manager'); ?></a>
    </div>
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

                <!-- EFT Details Tab -->
                <div id="eft-details" class="sub-tab-content">
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
                <div id="payment-gateways" class="sub-tab-content">
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

        <!-- Settings Tab Content -->
        <div id="settings-content" class="tab-pane" style="display:none;">
            <h2><?php _e('Club Manager Settings', 'club-manager'); ?></h2>
            <form method="post" action="options.php">
                <?php
                settings_fields('club_manager_settings_group');
                do_settings_sections('club_manager_settings_page');
                submit_button();
                ?>
            </form>
        </div>

        <!-- Add Club Members Tab Content -->
        <!-- Add Club Members Tab Link -->

<!-- Modal Structure for Add Club Members -->
<div id="add-members-modal" class="modal" style="display:none;">
    <div class="modal-content">
        <span class="close">&times;</span> <!-- Close button -->
        
        <!-- Add Club Members Tab Content -->
        <div id="add-members-content">
            
            <?php
            // Fetch clubs from the database to populate the "Select Clubs" dropdown
            global $wpdb;
            $clubs = $wpdb->get_results("SELECT club_id, club_name FROM wp_clubs", OBJECT);
            ?>
            
            <div class="wrap">
                <h1><?php _e('Add Club Members', 'club-manager'); ?></h1>
                <p><?php _e('This is where you can add new club members to your clubs.', 'club-manager'); ?></p>

                <form id="add-club-member-form" method="post">
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="club-id"><?php _e('Select Club', 'club-manager'); ?></label>
                            </th>
                            <td>
                                <select id="club-id" name="club-id">
                                    <option value=""><?php _e('Select a Club', 'club-manager'); ?></option>
                                    <?php
                                    if (!empty($clubs)) {
                                        foreach ($clubs as $club) {
                                            echo '<option value="' . esc_attr($club->club_id) . '">' . esc_html($club->club_name) . '</option>';
                                        }
                                    } else {
                                        echo '<option value="">' . __('No clubs found', 'club-manager') . '</option>';
                                    }
                                    ?>
                                </select>
                            </td>
                        </tr>

                        <!-- Searchable member dropdown -->
                        <tr>
                            <th scope="row">
                                <label for="member-name"><?php _e('Select Member', 'club-manager'); ?></label>
                            </th>
                            <td>
                                <select id="member-name" name="member-name" class="regular-text select2">
                                    <option value=""><?php _e('Search and select a member', 'club-manager'); ?></option>
                                </select>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label for="member-email"><?php _e('Member Email', 'club-manager'); ?></label>
                            </th>
                            <td>
                                <input type="email" id="member-email" name="member-email" class="regular-text" placeholder="<?php _e('Email will be automatically populated', 'club-manager'); ?>" readonly />
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label for="role"><?php _e('Select Role', 'club-manager'); ?></label>
                            </th>
                            <td>
                                <select id="role" name="role">
                                    <option value=""><?php _e('Select a Role', 'club-manager'); ?></option>
                                    <option value="Club Manager"><?php _e('Club Manager', 'club-manager'); ?></option>
                                    <option value="Media/Social"><?php _e('Media/Social', 'club-manager'); ?></option>
                                    <option value="Treasurer"><?php _e('Treasurer', 'club-manager'); ?></option>
                                    <option value="Store Manager"><?php _e('Store Manager', 'club-manager'); ?></option>
                                </select>
                            </td>
                        </tr>
                    </table>
                    <button type="submit" class="button button-primary"><?php _e('Add Member', 'club-manager'); ?></button>
                </form>

                <!-- Dynamic table for showing members based on club -->
                <h2><?php _e('Club Members', 'club-manager'); ?></h2>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('Club Name', 'club-manager'); ?></th>
                            <th><?php _e('Name', 'club-manager'); ?></th>
                            <th><?php _e('Email', 'club-manager'); ?></th>
                            <th><?php _e('Role', 'club-manager'); ?></th>
                            <th><?php _e('Permissions', 'club-manager'); ?></th>
                        </tr>
                    </thead>
                    <tbody id="club-members-list">
                        <tr><td colspan="5"><?php _e('Select a club to see members.', 'club-manager'); ?></td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- CSS for Modal -->
<style>
.modal {
    display: none; 
    position: fixed; 
    z-index: 1000;
    padding-top: 100px; 
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto; 
    background-color: rgb(0,0,0);
    background-color: rgba(0,0,0,0.4); 
}

.modal-content {
    background-color: #fefefe;
    margin: auto;
    padding: 20px;
    border: 1px solid #888;
    width: 80%;
    max-width: 600px;
}

.close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
}

.close:hover,
.close:focus {
    color: black;
    text-decoration: none;
    cursor: pointer;
}
</style>

<!-- Include jQuery and Select2 -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />

<!-- JavaScript for Modal and Form Functionality -->
<script type="text/javascript">
jQuery(document).ready(function($) {
    // Get modal element
    var modal = $('#add-members-modal');
    
    // Get the tab link that triggers the modal
    var tabLink = $('#add-members-tab');
    
    // Get the <span> element that closes the modal
    var span = $('.close');
    
    // When the user clicks the tab link, open the modal
    tabLink.on('click', function(event) {
        event.preventDefault(); // Prevent the default tab link behavior
        modal.show();
    });
    
    // When the user clicks on <span> (x), close the modal
    span.on('click', function() {
        modal.hide();
    });
    
    // When the user clicks anywhere outside of the modal, close it
    $(window).on('click', function(event) {
        if (event.target.id === 'add-members-modal') {
            modal.hide();
        }
    });
    
    // Initialize Select2 for member name search and select
    $('#member-name').select2({
        ajax: {
            url: ajaxurl, // Use WordPress AJAX URL
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return {
                    action: 'search_users', // The AJAX action we will handle in PHP
                    search: params.term // The search term
                };
            },
            processResults: function(data) {
                return {
                    results: $.map(data, function(user) {
                        return {
                            id: user.id,
                            text: user.display_name + ' (' + user.user_login + ')',
                            email: user.user_email
                        };
                    })
                };
            },
            cache: true
        },
        placeholder: "<?php _e('Search and select a member', 'club-manager'); ?>",
        minimumInputLength: 2,
    });

    // Automatically populate email field when a member is selected
    var selectedUserName = ''; // Track the selected user name
    $('#member-name').on('select2:select', function(e) {
        var email = e.params.data.email;
        selectedUserName = e.params.data.text.split(' (')[0]; // Extract the user's name
        $('#member-email').val(email);
    });

    // Handle form submission with AJAX
    $('#add-club-member-form').on('submit', function(e) {
        e.preventDefault();

        // Collect form data
        var club_id = $('#club-id').val();
        var club_name = $('#club-id option:selected').text();
        var member_id = $('#member-name').val();
        var member_email = $('#member-email').val();
        var role = $('#role').val();

        // Send the user name, not just the ID
        var member_name = selectedUserName;

        // AJAX request to add the club member to the database
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'add_club_member',
                club_id: club_id,
                club_name: club_name,
                member_name: member_name, // Send the actual user name
                member_email: member_email,
                role: role,
            },
            success: function(response) {
                if (response.success) {
                    alert('User added in ' + club_name);
                    // Optionally, you can clear the form fields here
                } else {
                    alert('Failed to add the member.');
                }
            }
        });
    });

    // Handle dynamic table based on club selection
    $('#club-id').on('change', function() {
        var selectedClubId = $(this).val();
        var selectedClubName = $('#club-id option:selected').text();

        if (selectedClubId) {
            // AJAX request to fetch members based on the selected club
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'fetch_club_members',
                    club_name: selectedClubName
                },
                success: function(response) {
                    $('#club-members-list').html(response); // Update the table with the fetched data
                }
            });
        } else {
            $('#club-members-list').html('<tr><td colspan="5"><?php _e('Select a club to see members.', 'club-manager'); ?></td></tr>');
        }
    });

    // Handle the delete member action
    $(document).on('click', '.delete-member', function(e) {
        e.preventDefault();

        var memberId = $(this).data('member-id');

        if (confirm('Are you sure you want to delete this member?')) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'delete_club_member',
                    member_id: memberId
                },
                success: function(response) {
                    if (response.success) {
                        $('tr[data-member-id="' + memberId + '"]').remove(); // Remove the row from the table
                    } else {
                        alert('Failed to delete the member.');
                    }
                }
            });
        }
    });
});
</script>


    </div>
</div>

<!-- JavaScript to handle tab switching -->
<script type="text/javascript">
    document.addEventListener('DOMContentLoaded', function() {
        // Get all tab links
        const tabLinks = document.querySelectorAll('.nav-tab-wrapper a');
        
        // Get all tab contents
        const tabContents = document.querySelectorAll('.tab-content .tab-pane');
        
        // Add click event to each tab
        tabLinks.forEach(function(link) {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Remove active class from all tabs and hide content
                tabLinks.forEach(function(tab) {
                    tab.classList.remove('nav-tab-active');
                });
                tabContents.forEach(function(content) {
                    content.style.display = 'none';
                });
                
                // Add active class to clicked tab and show corresponding content
                const target = this.getAttribute('href').substring(1);
                document.getElementById(`${target}-content`).style.display = 'block';
                this.classList.add('nav-tab-active');
            });
        });
    });
</script>
