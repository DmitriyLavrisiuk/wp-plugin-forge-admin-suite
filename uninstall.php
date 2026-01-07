<?php

defined('ABSPATH') || exit;

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

delete_option('forge_admin_suite_options');
