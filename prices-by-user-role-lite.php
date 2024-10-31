<?php
/**
 * Plugin Name: Prices By User Role Lite
 * Plugin URI: https://festi.team/plugins/woocommerce-prices-by-user-role/
 * Description: This is lite version of WooCommerce Prices By User Role plugin
 * Version: 1.0
 * Author: Festi-Team
 * Author URI: https://festi.team/
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: PriceByRoleLite
 * Domain Path: /languages
 *
 * Copyright 2017  Festi-Team  (email : support@festi.team)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

define('PRICE_BY_ROLE_LITE_PLUGIN_VERSION', '1.0.0');
define('PRICE_BY_ROLE_LITE_PLUGIN_URL', plugins_url('/', __FILE__));
define('PRICE_BY_ROLE_LITE_LANGUAGE_DOMAIN', 'PriceByRoleLite');
define('PRICE_BY_ROLE_LITE_SETTINGS_PAGE_SLUG', 'festi-user-role-prices-lite');
define('PRICE_BY_ROLE_LITE_WOOCOMMERCE_SETTINGS_PAGE_SLUG', 'woocommerce');
define('PRICE_BY_ROLE_LITE_PLUGIN_TEMPLATE_PATH', dirname(__FILE__).'/assets/templates/');
define('PRICE_BY_ROLE_LITE_PLUGIN_DIR_NAME', 'woocommerce-prices-by-user-role-lite');
define('PRICE_BY_ROLE_LITE_PLUGIN_MAIN_FILE', 'prices-by-user-role-lite.php');
define('PRICE_BY_ROLE_LITE_PREMIUM_VERSION_URL', 'https://festi.team/plugins/woocommerce_prices_by_user_role/');
define('PRICE_BY_ROLE_LITE_OPTIONS_NAME', 'festi_user_role_prices_lite');
define('PRICE_BY_ROLE_LITE_NONCE_NAME', 'price-by-role-lite-nonce');

function pbr_on_install()
{
    try {
        if (!pbr_is_woocommerce_plugin_active()) {
            throw new Exception('Woocommerce plugin is not active');
        }
        pbr_do_setup_default_options();

    } catch(Exception $e) {
        $error_message = $e->getMessage();
        pbr_display_error_message($error_message);
    }

} //end pbr_on_install

function pbr_on_init()
{
    pbr_do_init_plugin_action();
    pbr_do_init_plugin_page();
} //end pbr_on_init

function pbr_is_woocommerce_plugin_active()
{
    $woocommerce = 'woocommerce/woocommerce.php';
    $plugins = apply_filters('active_plugins', get_option('active_plugins'));
    return in_array($woocommerce, $plugins);
} //end pbr_is_woocommerce_plugin_active

function pbr_do_setup_default_options()
{
    $settings = pbr_do_load_settings();

    foreach ($settings as $ident => $item) {
        if (pbr_has_default_value($item)) {
            $params[$ident] = $item['default'];
        }
    }

    pbr_update_options(PRICE_BY_ROLE_LITE_OPTIONS_NAME, $params);
} //end pbr_do_setup_default_options

function pbr_has_default_value($item)
{
    return array_key_exists('default', $item);
} //end pbr_has_default_value_in_item

function pbr_do_load_settings()
{
    return array(
        'hideAddToCartButtonForUserRoles' => array(
            'caption' => esc_html__(
                'Hide Add to Cart Button for User Roles',
                PRICE_BY_ROLE_LITE_LANGUAGE_DOMAIN
            ),
            'type' => 'multicheck',
            'default' => array(),
            'deleteButton' => false,
        ),
        'hidePriceForUserRoles' => array(
            'caption' => esc_html__(
                'Hide Prices for User Roles',
                PRICE_BY_ROLE_LITE_LANGUAGE_DOMAIN
            ),
            'type' => 'multicheck',
            'default' => array(),
            'deleteButton' => false,
            'classes' => 'festi-user-role-prices-top-border'
        ),
    );
} //end pbr_do_load_settings

function pbr_do_init_plugin_page()
{
    $priority = 100;

    pbr_add_action_listener(
        'admin_menu',
        'pbr_do_create_plugin_settings_page',
        $priority
    );
} //end pbr_do_create_plugin_page

function pbr_do_create_plugin_settings_page()
{
    $params = array(
        'parent' => PRICE_BY_ROLE_LITE_WOOCOMMERCE_SETTINGS_PAGE_SLUG,
        'title' => esc_html__('Prices by User Role Lite', PRICE_BY_ROLE_LITE_LANGUAGE_DOMAIN),
        'caption' => esc_html__('Prices by User Role Lite', PRICE_BY_ROLE_LITE_LANGUAGE_DOMAIN),
        'capability' => 'manage_options',
        'slug' => PRICE_BY_ROLE_LITE_SETTINGS_PAGE_SLUG,
        'method' => 'pbr_on_display_option_page'
    );

    $page = pbr_do_append_sub_menu($params);

    pbr_do_init_settings_page_css_and_js($page);
} // end pbr_do_create_plugin_settings_page

function pbr_do_init_settings_page_css_and_js($page)
{
    pbr_add_action_listener(
        'admin_print_styles-'.$page,
        'pbr_on_init_backend_css_action'
    );

    pbr_add_action_listener(
        'admin_print_scripts-'.$page,
        'pbr_on_init_backend_js_action'
    );
} // end pbr_do_init_settings_page_css_and_js

function pbr_on_plugins_page_append_link($links)
{
    $text = esc_html__('Premium Version', PRICE_BY_ROLE_LITE_LANGUAGE_DOMAIN);

    $newLink = array(
        'premium' => '<a href='.esc_url(PRICE_BY_ROLE_LITE_PREMIUM_VERSION_URL).' target="_blank">'.$text.'</a>'
    );

    $links = array_merge($links, $newLink);
    
    return $links;
} // end pbr_on_plugins_page_append_link

function pbr_on_display_option_page()
{
    pbr_display_menu();
    pbr_on_save_new_settings();
    pbr_display_settings_page();
} // end pbr_on_display_option_page

function pbr_display_menu()
{
    $menu_tabs = array(
        'general' => esc_html__('General', PRICE_BY_ROLE_LITE_LANGUAGE_DOMAIN),
        'premium_version' => esc_html__('Premium Version', PRICE_BY_ROLE_LITE_LANGUAGE_DOMAIN)
    );
    echo pbr_fetch('menu.phtml', $menu_tabs);
} // end pbr_display_menu

function pbr_display_settings_page()
{
    $settings = array(
        'settings_filds' => pbr_do_load_settings(),
        'current_setting' => pbr_get_options(PRICE_BY_ROLE_LITE_OPTIONS_NAME),
        'all_user_roles' => pbr_get_user_roles()
    );

    echo pbr_fetch('settings_page.phtml', $settings);
} // end pbr_display_settings_page

function pbr_on_save_new_settings()
{
    if (!pbr_is_allow_update_options()) {
        return false;
    }

    try {
        $post = sanitize_post($_POST, 'raw');
        $settings = pbr_get_validated_settings($post);
        pbr_update_options(PRICE_BY_ROLE_LITE_OPTIONS_NAME, $settings);
        pbr_on_update_settings_admin_success_message();
    } catch(Exception $e) {
        $error_message = $e->getMessage();
        pbr_display_error_message($error_message);
    }
} // end pbr_on_save_new_settings()

function pbr_on_update_settings_admin_success_message()
{
    $params = array(
        'type' => 'success',
        'message' => esc_html__('Settings saved.', PRICE_BY_ROLE_LITE_LANGUAGE_DOMAIN)
    );

    echo pbr_fetch('message.phtml', $params);
}

function pbr_is_allow_update_options()
{
    return array_key_exists('__action', $_POST) &&
           array_key_exists('_wpnonce', $_POST) &&
           wp_verify_nonce($_POST['_wpnonce'], PRICE_BY_ROLE_LITE_NONCE_NAME) &&
           $_POST['__action'] == 'save';
} // end pbr_is_allow_update_options

function pbr_do_append_sub_menu($params)
{
    $page = add_submenu_page(
        $params['parent'],
        $params['title'],
        $params['caption'],
        $params['capability'],
        $params['slug'],
        $params['method']
    );

    return $page;
} // end pbr_do_append_sub_menu

function pbr_get_user_roles()
{
    if (!pbr_has_roles_in_globals()) {
        return false;
    }

    $roles = $GLOBALS['wp_roles'];
    return $roles->roles;
} // end pbr_get_user_roles

function pbr_has_roles_in_globals()
{
    return array_key_exists('wp_roles', $GLOBALS);
} // end pbr_has_roles_in_globals


function pbr_do_init_plugin_action()
{
    pbr_add_action_listener('wp', 'pbr_on_hidden_or_remove_action');
    pbr_add_filter_listener(
        'plugin_action_links_'.PRICE_BY_ROLE_LITE_PLUGIN_DIR_NAME.'/'.PRICE_BY_ROLE_LITE_PLUGIN_MAIN_FILE,
        'pbr_on_plugins_page_append_link',
        10,
        1
    );
    pbr_add_action_listener('plugins_loaded', 'pbr_on_languages_init_action');
} // end pbr_do_init_plugin_action

function pbr_on_hidden_or_remove_action()
{
    pbr_on_hide_add_to_cart_buttons();

    pbr_on_hide_prices();
} // end pbr_on_hidden_or_remove_action

function pbr_on_hide_add_to_cart_buttons()
{
    if (pbr_is_enabled_hide_add_to_cart_buttons_option()
        && pbr_is_enabled_hide_add_to_cart_button_for_current_user_role()) {
        pbr_do_hide_add_to_cart_buttons_for_selected_roles();
    }
} // end pbr_on_hide_add_to_cart_buttons

function pbr_is_enabled_hide_add_to_cart_buttons_option()
{
    $key = 'hideAddToCartButtonForUserRoles';
    $settings = pbr_get_options(PRICE_BY_ROLE_LITE_OPTIONS_NAME);

    return array_key_exists($key, $settings);
} // end pbr_is_enabled_hide_add_to_cart_buttons_option

function pbr_is_enabled_hide_add_to_cart_button_for_current_user_role()
{
    $user_role = pbr_get_user_role();
    $settings = pbr_get_options(PRICE_BY_ROLE_LITE_OPTIONS_NAME);
    $settings_key = 'hideAddToCartButtonForUserRoles';

    return array_key_exists($user_role, $settings[$settings_key]);
}
function pbr_do_hide_add_to_cart_buttons_for_selected_roles()
{
    $priority = 30;

    remove_action(
        'woocommerce_after_shop_loop_item',
        'woocommerce_template_loop_add_to_cart'
    );
    remove_action(
        'woocommerce_single_product_summary',
        'woocommerce_template_single_add_to_cart', $priority
    );
} // end pbr_do_hide_add_to_cart_buttons_for_selected_roles


function pbr_get_user_role()
{
    $user_id = pbr_get_user_id();

    if (!$user_id) {
        return "non_registered_user";
    }
    $userData = get_userdata($user_id);

    $most_important_role = 0;
    return $userData->roles[$most_important_role];
} // end pbr_get_user_role

function pbr_get_user_id()
{
    return get_current_user_id();
} // end pbr_get_user_id


function pbr_on_hide_prices()
{
    if (pbr_is_enabled_hide_prices_option()
        && pbr_is_enabled_hide_prices_for_current_user_role()) {
        pbr_do_hide_prices_for_selected_roles();
    }
} // end pbr_on_hide_prices

function pbr_is_enabled_hide_prices_option()
{
    $key = 'hidePriceForUserRoles';
    $settings = pbr_get_options(PRICE_BY_ROLE_LITE_OPTIONS_NAME);

    return array_key_exists($key, $settings);
}

function pbr_is_enabled_hide_prices_for_current_user_role()
{
    $user_role = pbr_get_user_role();
    $settings = pbr_get_options(PRICE_BY_ROLE_LITE_OPTIONS_NAME);
    $settings_key = 'hidePriceForUserRoles';

    return array_key_exists($user_role, $settings[$settings_key]);
}

function pbr_do_hide_prices_for_selected_roles()
{
    $priority = 10;

    remove_action(
        'woocommerce_single_product_summary',
        'woocommerce_template_single_price',
        $priority
    );
    remove_action(
        'woocommerce_after_shop_loop_item_title',
        'woocommerce_template_loop_price',
        $priority
    );
}

function pbr_fetch($template, $params = array())
{
    if ($params) {
        extract($params);
    }

    ob_start();
    $templates_path = PRICE_BY_ROLE_LITE_PLUGIN_TEMPLATE_PATH.$template;

    include $templates_path;
    $content = ob_get_clean();

    return $content;
} // end pbr_fetch

function pbr_display_error_message($message)
{
    $params = array(
        'message' => esc_html__($message, PRICE_BY_ROLE_LITE_LANGUAGE_DOMAIN),
        'type' => 'error'
    );
    echo pbr_fetch('message.phtml', $params);
} // end pbr_display_error_message

function pbr_on_init_backend_css_action()
{
    pbr_on_enqueue_css_file_action(
        'festi-user-role-prices-styles',
        'backend_style.css',
        array(),
        PRICE_BY_ROLE_LITE_PLUGIN_VERSION
    );

    pbr_on_enqueue_css_file_action(
        'festi-admin-menu',
        'backend_menu.css',
        array(),
        PRICE_BY_ROLE_LITE_PLUGIN_VERSION
    );
} // end pbr_on_init_backend_css_action

function pbr_on_init_backend_js_action()
{
    pbr_on_enqueue_js_file_action(
        'festi-user-role-prices-general',
        'general.js',
        'jquery',
        PRICE_BY_ROLE_LITE_PLUGIN_VERSION
    );
} // end pbr_on_init_backend_js_action


function pbr_get_options($option_name)
{
    $options = get_option($option_name);

    $options = json_decode($options, true);

    return $options;
} //end pbr_get_options


function pbr_update_options($option_name, $options = array())
{
    $value = json_encode($options);
    update_option($option_name, $value);

} //end pbr_update_options

function pbr_add_action_listener(
    $hook,
    $method,
    $priority = 10,
    $accepted_args = 1
)
{
    add_action($hook, $method, $priority, $accepted_args);
} // end pbr_add_action_listener

function pbr_add_filter_listener(
    $hook, $method, $priority = 10, $accepted_args = 1
)
{
    add_filter($hook, $method, $priority, $accepted_args);
} // end pbr_add_filter_listener


function pbr_on_enqueue_css_file_action($handle, $file = false, $depends = array())
{
    $version = false;
    $media = 'all';
    $src = PRICE_BY_ROLE_LITE_PLUGIN_URL.'assets/css/'.$file;

    $args = func_get_args();

    if (isset($args[3])) {
        $version = $args[3];
    }

    if (isset($args[4])) {
        $media = $args[4];
    }

    if ($depends) {
        $depends = array($depends);
    }

    wp_enqueue_style($handle, $src, $depends, $version, $media);
} //end pbr_on_enqueue_css_file_action

function pbr_on_enqueue_js_file_action($handle, $file = false, $depends = false)
{
    $src = PRICE_BY_ROLE_LITE_PLUGIN_URL.'assets/js/'.$file;
    $version = false;
    $in_footer = false;

    $args = func_get_args();

    if (isset($args[3])) {
        $version = $args[3];
    }

    if (isset($args[4])) {
        $in_footer = $args[4];
    }

    if ($depends) {
        $depends = array($depends);
    }

    wp_enqueue_script($handle, $src, $depends, $version, $in_footer);
} // end  pbr_on_enqueue_js_file_action

function pbr_on_languages_init_action()
{
    $langPath = PRICE_BY_ROLE_LITE_PLUGIN_DIR_NAME.'/languages/';

    load_plugin_textdomain(
        PRICE_BY_ROLE_LITE_LANGUAGE_DOMAIN,
        false,
        $langPath
    );

} // end pbr_on_languages_init_action

function pbr_get_validated_settings($importSettings) 
{       
    if (!is_array($importSettings)) {
        return array();
    }
    
    $settings = pbr_do_load_settings();
    
    return array_intersect_key($importSettings, $settings);
} // end pbr_get_validated_settings

register_activation_hook(__FILE__, 'pbr_on_install');

pbr_on_init();