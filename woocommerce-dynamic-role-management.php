<?php
/**
 * Plugin Name: WooCommerce Dynamic Role Management
 * Plugin URI: http://yourwebsite.com
 * Description: Dynamically manages user roles based on WooCommerce product purchases.
 * Version: 1.0
 * Author: Your Name
 * Author URI: http://yourwebsite.com
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Plugin code goes here.

add_action('admin_menu', 'wdrm_add_admin_menu');
add_action('admin_init', 'wdrm_settings_init');

function wdrm_add_admin_menu() {
    add_menu_page('Woo Role Management', 'Woo Role Management', 'manage_options', 'woo_role_management', 'wdrm_options_page');
}

function wdrm_settings_init() {
    register_setting('wdrmPlugin', 'wdrm_settings');

    add_settings_section(
        'wdrm_pluginPage_section',
        __('Set Product ID to Role Mappings', 'wordpress'),
        'wdrm_settings_section_callback',
        'wdrmPlugin'
    );

    add_settings_field(
        'wdrm_textarea_field_0',
        __('Product ID:Role', 'wordpress'),
        'wdrm_textarea_field_0_render',
        'wdrmPlugin',
        'wdrm_pluginPage_section'
    );
}

function wdrm_textarea_field_0_render() {
    $options = get_option('wdrm_settings');
    ?>
    <textarea cols='40' rows='5' name='wdrm_settings[wdrm_textarea_field_0]'><?php echo $options['wdrm_textarea_field_0']; ?></textarea>
    <?php
}

function wdrm_settings_section_callback() {
    echo __('Enter each mapping on a new line in the format ProductID:Role.', 'wordpress');
}

function wdrm_options_page() {
    ?>
    <form action='options.php' method='post'>
        <h2>WooCommerce Dynamic Role Management</h2>
        <?php
        settings_fields('wdrmPlugin');
        do_settings_sections('wdrmPlugin');
        submit_button();
        ?>
    </form>
    <?php
}


add_action('woocommerce_order_status_completed', 'wdrm_assign_role_on_purchase');
add_action('woocommerce_order_status_refunded', 'wdrm_remove_role_on_refund');

function wdrm_assign_role_on_purchase($order_id) {
    $order = wc_get_order($order_id);
    $items = $order->get_items();
    $options = get_option('wdrm_settings');
    $mappings = explode("\n", $options['wdrm_textarea_field_0']);
    $product_role_map = [];

    foreach ($mappings as $mapping) {
        list($product_id, $role) = explode(':', trim($mapping));
        $product_role_map[$product_id] = $role;
    }

    foreach ($items as $item) {
        if (array_key_exists($item->get_product_id(), $product_role_map)) {
            $user = $order->get_user();
            if ($user) {
                $user->add_role($product_role_map[$item->get_product_id()]);
            }
        }
    }
}

function wdrm_remove_role_on_refund($order_id) {
    // Similar to wdrm_assign_role_on_purchase, but remove the role instead.
}
