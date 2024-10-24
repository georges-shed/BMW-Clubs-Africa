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

// Bulk action handling
if (isset($_POST['bulk_action']) && !empty($_POST['club'])) {
    $bulk_action = sanitize_text_field($_POST['bulk_action']);
    $selected_clubs = array_map('intval', $_POST['club']); // Retrieve selected club IDs and sanitize

    // Validate and perform the bulk action
    if (!empty($selected_clubs)) {
        global $wpdb;
        $club_ids_placeholder = implode(',', array_fill(0, count($selected_clubs), '%d'));

        switch ($bulk_action) {
            case 'trash':
                $wpdb->query($wpdb->prepare(
                    "UPDATE {$wpdb->prefix}clubs SET club_status = 'trash' WHERE club_id IN ($club_ids_placeholder)",
                    $selected_clubs
                ));
                break;

            case 'draft':
                $wpdb->query($wpdb->prepare(
                    "UPDATE {$wpdb->prefix}clubs SET club_status = 'draft' WHERE club_id IN ($club_ids_placeholder)",
                    $selected_clubs
                ));
                break;

            case 'active': // New option to mark as active
                $wpdb->query($wpdb->prepare(
                    "UPDATE {$wpdb->prefix}clubs SET club_status = 'active' WHERE club_id IN ($club_ids_placeholder)",
                    $selected_clubs
                ));
                break;

            case 'delete':
                $wpdb->query($wpdb->prepare(
                    "DELETE FROM {$wpdb->prefix}clubs WHERE club_id IN ($club_ids_placeholder)",
                    $selected_clubs
                ));
                break;

            default:
                // Do nothing if no valid action
                break;
        }
    }
}

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
$search_query = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
$status_filter = isset($_GET['post_status']) ? sanitize_text_field($_GET['post_status']) : 'all';

?>

<!-- CSS to improve layout -->
<style>
    .filter-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        margin-bottom: 15px;
        position: relative;
    top: 50px;
    left: 70px;
    }
    .filter-container .alignleft, .filter-container .alignright {
        margin-top: 10px;
    }
    .filter-container .alignleft {
        display: flex;
        gap: 10px;
    }
    .filter-container select, .filter-container input[type="search"] {
        width: 220px;
    }
    #clubs-table th, #clubs-table td {
        white-space: nowrap;
    }
    #clubs-table{
        margin-top: 30px;
    }
</style>

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

    <!-- New row for filters and search -->
    <div class="filter-container">
        <!-- Filter Controls -->
        <form method="get">
            <input type="hidden" name="page" value="club-manager-clubs" />
            <input type="hidden" name="post_status" value="<?php echo esc_attr($status_filter); ?>" />

            <div class="alignleft actions">
                <select name="filter_role">
                    <option value=""><?php echo __('Filter by Role', 'club-manager'); ?></option>
                    <option value="Club Manager"<?php selected($filter_role, 'Club Manager'); ?>><?php echo __('Club Manager', 'club-manager'); ?></option>
                    <option value="Treasurer"<?php selected($filter_role, 'Treasurer'); ?>><?php echo __('Treasurer', 'club-manager'); ?></option>
                    <option value="Media/Social"<?php selected($filter_role, 'Media/Social'); ?>><?php echo __('Media/Social', 'club-manager'); ?></option>
                    <option value="Store Manager"<?php selected($filter_role, 'Store Manager'); ?>><?php echo __('Store Manager', 'club-manager'); ?></option>
                </select>

                <select name="filter_payment">
                    <option value=""><?php echo __('Filter by Payment Method', 'club-manager'); ?></option>
                    <option value="EFT"<?php selected($filter_payment, 'EFT'); ?>><?php echo __('EFT', 'club-manager'); ?></option>
                    <option value="Yoco"<?php selected($filter_payment, 'Yoco'); ?>><?php echo __('Yoco', 'club-manager'); ?></option>
                    <option value="PayFast"<?php selected($filter_payment, 'PayFast'); ?>><?php echo __('PayFast', 'club-manager'); ?></option>
                    <option value="Stripe"<?php selected($filter_payment, 'Stripe'); ?>><?php echo __('Stripe', 'club-manager'); ?></option>
                    <option value="Both"<?php selected($filter_payment, 'Both'); ?>><?php echo __('Both', 'club-manager'); ?></option>
                </select>

                <button type="submit" class="button"><?php echo __('Filter', 'club-manager'); ?></button>
            </div>

            <div class="alignright">
                <input type="search" name="s" value="<?php echo esc_attr($search_query); ?>" placeholder="<?php echo __('Search clubs', 'club-manager'); ?>" />
                <button type="submit" class="button"><?php echo __('Search', 'club-manager'); ?></button>
            </div>
        </form>
    </div>

    <!-- Bulk Action Form -->
    <div class="tablenav top">
        <form method="post" action="">
            <div class="alignleft actions bulkactions">
                <select name="bulk_action">
                    <option value=""><?php echo __('Bulk Actions', 'club-manager'); ?></option>
                    <option value="delete"><?php echo __('Delete Permanently', 'club-manager'); ?></option>
                    <option value="trash"><?php echo __('Move to Trash', 'club-manager'); ?></option>
                    <option value="draft"><?php echo __('Mark as Draft', 'club-manager'); ?></option>
                    <option value="active"><?php echo __('Mark as Active', 'club-manager'); ?></option> <!-- New option for marking clubs as active -->
                </select>
                <button type="submit" class="button action"><?php echo __('Apply', 'club-manager'); ?></button>
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
                    </tr>
                </thead>
                <tbody>
    <?php
    // Prepare SQL query to retrieve clubs, roles, and payment methods from the custom tables
    $sql = "
        SELECT c.club_id, c.club_name, c.club_url, pg.gateway_type AS payment_method,
               COALESCE(GROUP_CONCAT(m.role SEPARATOR ', '), 'No roles') AS roles
        FROM {$wpdb->prefix}clubs c
        LEFT JOIN {$wpdb->prefix}payment_gateways pg ON c.club_id = pg.club_id
        LEFT JOIN {$wpdb->prefix}club_members m ON c.club_id = m.club_id
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
        $sql .= $wpdb->prepare(" AND m.role = %s", $filter_role);
    }
    if ($filter_payment !== '') {
        $sql .= $wpdb->prepare(" AND pg.gateway_type = %s", $filter_payment);
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
        // Use 'club-manager-clubs' page slug to open the edit page under the Clubs tab
        $edit_link = admin_url('admin.php?page=club-manager-clubs&action=edit&club_id=' . $club->club_id);
        echo "<tr>
                <th scope='row' class='check-column'><input type='checkbox' name='club[]' value='{$club->club_id}' /></th>
                <td>
                    <strong>
                        <a class='row-title' href='{$edit_link}'>" . esc_html($club->club_name) . "</a>
                    </strong>
                    <div class='row-actions'>
                        <span class='edit'>
                            <a href='{$edit_link}'>" . __('Edit', 'club-manager') . "</a>
                        </span>
                    </div>
                </td>
                <td>{$club->club_url}</td>
                <td>{$club->payment_method}</td>
                <td>{$club->roles}</td>
            </tr>";
    }
    ?>
                </tbody>
            </table>
        </form>
    </div>
</div>
