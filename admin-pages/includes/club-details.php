<?php
// Ensure to include this within a WordPress environment
defined('ABSPATH') || exit;

// Load necessary WordPress scripts for media uploader
function enqueue_club_media_uploader() {
    wp_enqueue_media(); // Enqueues WordPress Media Library
}
add_action('admin_enqueue_scripts', 'enqueue_club_media_uploader');

// Fetch club details if available (for edit case)
global $wpdb;
$club_id = isset($_GET['club_id']) ? intval($_GET['club_id']) : 0;
$club_details = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}clubs WHERE club_id = %d", $club_id ) );

// Set default values for club name, URL, and logo
$club_name = $club_details ? esc_attr( $club_details->club_name ) : '';
$club_url = $club_details ? esc_attr( $club_details->club_url ) : '/';
$club_logo = $club_details ? esc_url( $club_details->club_logo ) : '';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_club_details'])) {
    $club_name = sanitize_text_field($_POST['club_name']);
    $club_url = sanitize_text_field($_POST['club_url']);
    $club_logo = esc_url_raw($_POST['club_logo']);

    // Insert or update the club details in the database
    if ($club_id > 0) {
        // Update existing club
        $wpdb->update(
            "{$wpdb->prefix}clubs",
            [
                'club_name' => $club_name,
                'club_url' => $club_url,
                'club_logo' => $club_logo,
            ],
            ['club_id' => $club_id]
        );
    } else {
        // Insert new club
        $wpdb->insert(
            "{$wpdb->prefix}clubs",
            [
                'club_name' => $club_name,
                'club_url' => $club_url,
                'club_logo' => $club_logo,
            ]
        );
        $club_id = $wpdb->insert_id; // Get the new club ID
    }

    // Redirect to avoid resubmitting the form on page reload
    wp_redirect(add_query_arg(['club_id' => $club_id, 'updated' => true], $_SERVER['REQUEST_URI']));
    exit;
}

?>
<?php if (isset($_GET['updated']) && $_GET['updated'] == true): ?>
    <div class="notice notice-success is-dismissible">
        <p><?php _e('Club details saved successfully!', 'textdomain'); ?></p>
    </div>
<?php endif; ?>

<form method="POST" action="" enctype="multipart/form-data">
    <table class="form-table">
        <tr>
            <th scope="row">
                <label for="club_name"><?php _e('Club Name', 'textdomain'); ?></label>
            </th>
            <td>
                <input type="text" id="club_name" name="club_name" value="<?php echo $club_name; ?>" class="regular-text" required />
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="club_url"><?php _e('Home URL', 'textdomain'); ?></label>
            </th>
            <td>
                <input type="text" id="club_url" name="club_url" value="<?php echo $club_url; ?>" class="regular-text" required pattern="^\/[a-zA-Z0-9\-]+$" />
                <p class="description"><?php _e('URL should start with a slash (/) and use hyphens for spaces.', 'textdomain'); ?></p>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="club_logo"><?php _e('Logo', 'textdomain'); ?></label>
            </th>
            <td>
                <div class="club-logo-wrapper">
                    <img id="club-logo-preview" src="<?php echo $club_logo; ?>" style="max-width: 150px; <?php echo !$club_logo ? 'display:none;' : ''; ?>" />
                    <input type="hidden" id="club_logo" name="club_logo" value="<?php echo $club_logo; ?>" />
                    <button type="button" class="button upload-image"><?php _e('Upload/Add Image', 'textdomain'); ?></button>
                    <button type="button" class="button remove-image" style="<?php echo !$club_logo ? 'display:none;' : ''; ?>"><?php _e('Remove Image', 'textdomain'); ?></button>
                </div>
            </td>
        </tr>
    </table>

    <input type="hidden" name="club_id" value="<?php echo $club_id; ?>" />
    <input type="hidden" name="save_club_details" value="1" />
    <p class="submit">
        <input type="submit" class="button button-primary" value="<?php _e('Save Changes', 'textdomain'); ?>" />
    </p>
</form>

<script>
    jQuery(document).ready(function($){
        // Media Uploader for Club Logo
        var mediaUploader;
        $('.upload-image').click(function(e) {
            e.preventDefault();
            if (mediaUploader) {
                mediaUploader.open();
                return;
            }
            mediaUploader = wp.media.frames.file_frame = wp.media({
                title: '<?php _e("Choose Image", "textdomain"); ?>',
                button: {
                    text: '<?php _e("Choose Image", "textdomain"); ?>'
                }, multiple: false
            });
            mediaUploader.on('select', function() {
                var attachment = mediaUploader.state().get('selection').first().toJSON();
                $('#club_logo').val(attachment.url);
                $('#club-logo-preview').attr('src', attachment.url).show();
                $('.remove-image').show();
            });
            mediaUploader.open();
        });

        $('.remove-image').click(function(e) {
            e.preventDefault();
            $('#club_logo').val('');
            $('#club-logo-preview').hide();
            $(this).hide();
        });

        // URL Formatting: Convert spaces to hyphens and capitalize
        $('#club_url').on('input', function() {
            var value = $(this).val();
            value = value.replace(/\s+/g, '-').toLowerCase();
            $(this).val(value);
        });
    });
</script>
