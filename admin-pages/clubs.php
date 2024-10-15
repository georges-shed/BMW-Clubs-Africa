<?php
// Prevent direct access to this file
if (!defined('ABSPATH')) {
    exit;
}

// Register the custom post type for clubs
function club_manager_register_club_post_type() {
    register_post_type('club', array(
        'labels' => array(
            'name' => __('Clubs', 'club-manager'),
            'singular_name' => __('Club', 'club-manager')
        ),
        'public' => true,
        'show_ui' => true,
        'supports' => array('title'),
        'capabilities' => array('delete_post' => 'delete_posts') // Enable deleting
    ));
}
add_action('init', 'club_manager_register_club_post_type');

// Handle bulk actions for delete and move to bin
function club_manager_handle_bulk_actions() {
    if (isset($_GET['bulk_action']) && !empty($_GET['club'])) {
        global $wpdb;
        $action = sanitize_text_field($_GET['bulk_action']);
        $club_ids = array_map('intval', $_GET['club']);

        // Determine the new status based on the action
        if ($action === 'delete') {
            $new_status = 'trash';
        } elseif ($action === 'move_to_bin') {
            $new_status = 'draft';
        } else {
            return; // Invalid action, do nothing
        }

        // Perform the update if there are selected club IDs
        if (!empty($club_ids)) {
            $placeholders = implode(', ', array_fill(0, count($club_ids), '%d'));
            $query = "UPDATE {$wpdb->prefix}clubs SET club_status = %s WHERE club_id IN ($placeholders)";
            $wpdb->query($wpdb->prepare($query, array_merge([$new_status], $club_ids)));
        }

        // Redirect after processing
        wp_redirect(admin_url('admin.php?page=club-manager-clubs&club_ids=' . implode(',', $club_ids) . '&bulk_action=' . $action));
        exit;
    }
}
add_action('admin_init', 'club_manager_handle_bulk_actions');

// Count the clubs by status using SQL
global $wpdb;
$counts = $wpdb->get_results("
    SELECT club_status, COUNT(*) as count 
    FROM {$wpdb->prefix}clubs
    GROUP BY club_status
", OBJECT_K);

// Assign counts to variables for easy access
$count_all = array_sum(array_column($counts, 'count'));
$count_active = isset($counts['active']) ? $counts['active']->count : 0;
$count_draft = isset($counts['draft']) ? $counts['draft']->count : 0;
$count_bin = isset($counts['trash']) ? $counts['trash']->count : 0;

// Retrieve filter values from GET parameters
$filter_role = isset($_GET['filter_role']) ? sanitize_text_field($_GET['filter_role']) : '';
$filter_payment = isset($_GET['filter_payment']) ? sanitize_text_field($_GET['filter_payment']) : '';
$filter_notification = isset($_GET['filter_notification']) ? sanitize_text_field($_GET['filter_notification']) : '';
$search_query = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
$status_filter = isset($_GET['post_status']) ? sanitize_text_field($_GET['post_status']) : 'all';

?>

<!-- HTML -->
<div class="wrap">
    <h1><?php echo __('Clubs', 'club-manager'); ?> 
        <a href="<?php echo admin_url('admin.php?page=club-manager-edit-club'); ?>" class="button-primary"><?php echo __('Add Club', 'club-manager'); ?></a>
    </h1>

    <!-- Filter Links -->
    <ul class="subsubsub">
        <li><a href="<?php echo add_query_arg('post_status', 'all', admin_url('admin.php?page=club-manager-clubs')); ?>" class="<?php echo ($status_filter === 'all') ? 'current' : ''; ?>"><?php echo __('All', 'club-manager'); ?> <span class="count">(<?php echo esc_html($count_all); ?>)</span></a> | </li>
        <li><a href="<?php echo add_query_arg('post_status', 'active', admin_url('admin.php?page=club-manager-clubs')); ?>" class="<?php echo ($status_filter === 'active') ? 'current' : ''; ?>"><?php echo __('Active', 'club-manager'); ?> <span class="count">(<?php echo esc_html($count_active); ?>)</span></a> | </li>
        <li><a href="<?php echo add_query_arg('post_status', 'draft', admin_url('admin.php?page=club-manager-clubs')); ?>" class="<?php echo ($status_filter === 'draft') ? 'current' : ''; ?>"><?php echo __('Draft', 'club-manager'); ?> <span class="count">(<?php echo esc_html($count_draft); ?>)</span></a> | </li>
        <li><a href="<?php echo add_query_arg('post_status', 'trash', admin_url('admin.php?page=club-manager-clubs')); ?>" class="<?php echo ($status_filter === 'trash') ? 'current' : ''; ?>"><?php echo __('Bin', 'club-manager'); ?> <span class="count">(<?php echo esc_html($count_bin); ?>)</span></a></li>
    </ul>

    <!-- Filter Controls -->
    <div class="tablenav top">
        <form method="get">
            <input type="hidden" name="page" value="club-manager-clubs" />
            <input type="hidden" name="post_status" value="<?php echo esc_attr($status_filter); ?>" />
            <div class="alignleft actions bulkactions">
                <select name="bulk_action">
                    <option value=""><?php echo __('Bulk Actions', 'club-manager'); ?></option>
                    <option value="move_to_bin"><?php echo __('Move to Bin', 'club-manager'); ?></option>
                    <option value="delete"><?php echo __('Delete', 'club-manager'); ?></option>
                </select>
                <button type="submit" class="button action"><?php echo __('Apply', 'club-manager'); ?></button>
            </div>

            <div class="alignleft actions">
                <select name="filter_role" onchange="this.form.submit()">
                    <option value=""><?php echo __('Filter by Role', 'club-manager'); ?></option>
                    <option value="Club Manager"<?php selected($filter_role, 'Club Manager'); ?>><?php echo __('Club Manager', 'club-manager'); ?></option>
                    <option value="Treasurer"<?php selected($filter_role, 'Treasurer'); ?>><?php echo __('Treasurer', 'club-manager'); ?></option>
                    <option value="Media/Social"<?php selected($filter_role, 'Media/Social'); ?>><?php echo __('Media/Social', 'club-manager'); ?></option>
                    <option value="Store Manager"<?php selected($filter_role, 'Store Manager'); ?>><?php echo __('Store Manager', 'club-manager'); ?></option>
                </select>

                <select name="filter_payment" onchange="this.form.submit()">
                    <option value=""><?php echo __('Filter by Payment Method', 'club-manager'); ?></option>
                    <option value="EFT"<?php selected($filter_payment, 'EFT'); ?>><?php echo __('EFT', 'club-manager'); ?></option>
                    <option value="Yoco"<?php selected($filter_payment, 'Yoco'); ?>><?php echo __('Yoco', 'club-manager'); ?></option>
                    <option value="Both"<?php selected($filter_payment, 'Both'); ?>><?php echo __('Both', 'club-manager'); ?></option>
                </select>

                <select name="filter_notification" onchange="this.form.submit()">
                    <option value=""><?php echo __('Filter by Notification Setup', 'club-manager'); ?></option>
                    <option value="1"<?php selected($filter_notification, '1'); ?>><?php echo __('Set', 'club-manager'); ?></option>
                    <option value="0"<?php selected($filter_notification, '0'); ?>><?php echo __('Not Set', 'club-manager'); ?></option>
                </select>
                <button type="submit" class="button"><?php echo __('Filter', 'club-manager'); ?></button>
            </div>

            <div class="alignright">
                <input type="search" name="s" value="<?php echo esc_attr($search_query); ?>" placeholder="<?php echo __('Search clubs', 'club-manager'); ?>" />
                <button type="submit" class="button"><?php echo __('Search', 'club-manager'); ?></button>
            </div>

            <!-- Clubs Table -->
            <table class="wp-list-table widefat fixed striped posts" id="clubs-table">
                <thead>
                    <tr>
                        <th class="manage-column column-cb check-column"><input type="checkbox" /></th>
                        <th><?php echo __('Club Name', 'club-manager'); ?></th>
                        <th><?php echo __('Home URL', 'club-manager'); ?></th>
                        <th><?php echo __('Payment Methods', 'club-manager'); ?></th>
                        <th><?php echo __('Roles', 'club-manager'); ?></th>
                        <th><?php echo __('Notifications', 'club-manager'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Prepare SQL query to retrieve clubs, roles, and payment methods from the custom tables
                    $sql = "
                        SELECT c.club_id, c.club_name, c.club_url, pg.gateway_type AS payment_method,
                               COALESCE(notifications.meta_value, '0') AS club_notifications, 
                               GROUP_CONCAT(r.role_name SEPARATOR ', ') AS roles
                        FROM {$wpdb->prefix}clubs c
                        LEFT JOIN {$wpdb->prefix}club_roles r ON c.club_id = r.club_id
                        LEFT JOIN {$wpdb->prefix}payment_gateways pg ON c.club_id = pg.club_id
                        LEFT JOIN {$wpdb->postmeta} notifications ON c.club_id = notifications.post_id AND notifications.meta_key = '_club_notifications'
                        WHERE 1=1
                    ";

                    // Apply status filter if necessary
                    if ($status_filter === 'active') {
                        $sql .= " AND c.club_status = 'active'";
                    } elseif ($status_filter === 'draft') {
                        $sql .= " AND c.club_status = 'draft'";
                    } elseif ($status_filter === 'trash') {
                        $sql .= " AND c.club_status = 'trash'";
                    }

                    // Add conditions based on filters
                    if (!empty($filter_role)) {
                        $sql .= $wpdb->prepare(" AND r.role_name = %s", $filter_role);
                    }
                    if ($filter_payment !== '') {
                        $sql .= $wpdb->prepare(" AND pg.gateway_type = %s", $filter_payment);
                    }
                    if ($filter_notification !== '') {
                        $sql .= $wpdb->prepare(" AND notifications.meta_value = %s", $filter_notification);
                    }
                    if (!empty($search_query)) {
                        $sql .= $wpdb->prepare(" AND c.club_name LIKE %s", '%' . $wpdb->esc_like($search_query) . '%');
                    }

                    // Group by club to handle aggregate roles
                    $sql .= " GROUP BY c.club_id";

                    // Execute the SQL query
                    $clubs = $wpdb->get_results($sql);

                    // Output the results in the table
                    foreach ($clubs as $club) {
                        echo "<tr>
                                <th scope='row' class='check-column'><input type='checkbox' name='club[]' value='{$club->club_id}' /></th>
                                <td><a href='" . admin_url('admin.php?page=club-manager-edit-club&club_id=' . $club->club_id) . "'>" . esc_html($club->club_name) . "</a></td>
                                <td>{$club->club_url}</td>
                                <td>{$club->payment_method}</td>
                                <td>{$club->roles}</td>
                                <td>" . ($club->club_notifications ? 'Yes' : 'No') . "</td>
                            </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </form>
    </div>
</div>
