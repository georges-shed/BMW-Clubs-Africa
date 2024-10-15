<?php
// Prevent direct access to this file
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap">
    <h1><?php echo __('BMW Club Members', 'bmw-clubs-africa'); ?></h1>
    <p><?php echo __('Here you can manage club members.', 'bmw-clubs-africa'); ?></p>
    
    <form method="post" action="">
        <h2><?php echo __('Add New Member', 'bmw-clubs-africa'); ?></h2>
        <label for="member_name"><?php echo __('Name', 'bmw-clubs-africa'); ?>:</label>
        <input type="text" id="member_name" name="member_name" required>
        
        <label for="member_email"><?php echo __('Email', 'bmw-clubs-africa'); ?>:</label>
        <input type="email" id="member_email" name="member_email" required>
        
        <input type="submit" value="<?php echo __('Add Member', 'bmw-clubs-africa'); ?>">
    </form>
    
    <h2><?php echo __('Member List', 'bmw-clubs-africa'); ?></h2>
    <table>
        <thead>
            <tr>
                <th><?php echo __('Name', 'bmw-clubs-africa'); ?></th>
                <th><?php echo __('Email', 'bmw-clubs-africa'); ?></th>
            </tr>
        </thead>
        <tbody>
            <!-- Example row, replace with dynamic content -->
            <tr>
                <td>Talha Shahid</td>
                <td>talha.shahid@webhostingguru.io</td>
            </tr>
        </tbody>
    </table>
</div>
