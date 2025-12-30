<?php
/**
 * Plugin Name: Forge Admin Suite
 * Description: Admin SPA for Forge Admin Suite.
 * Version: 0.1.0
 * Author: Forge
 * Text Domain: forge-admin-suite
 */

if (!defined('ABSPATH')) {
    exit;
}

define('FORGE_ADMIN_SUITE_VERSION', '0.1.0');
define('FORGE_ADMIN_SUITE_PATH', plugin_dir_path(__FILE__));
define('FORGE_ADMIN_SUITE_URL', plugin_dir_url(__FILE__));
define('FORGE_ADMIN_SUITE_BASENAME', plugin_basename(__FILE__));

require_once FORGE_ADMIN_SUITE_PATH . 'includes/class-plugin.php';

function forge_admin_suite()
{
    return Forge_Admin_Suite\Plugin::instance();
}

forge_admin_suite();
