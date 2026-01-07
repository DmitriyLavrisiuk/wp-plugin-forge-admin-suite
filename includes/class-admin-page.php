<?php

namespace Forge_Admin_Suite;

defined('ABSPATH') || exit;

class Admin_Page
{
    private $hook_suffix = '';

    public function init()
    {
        add_action('admin_menu', array($this, 'register_menu'));
    }

    public function register_menu()
    {
        $this->hook_suffix = add_menu_page(
            __('Forge Suite', 'forge-admin-suite'),
            __('Forge Suite', 'forge-admin-suite'),
            'manage_options',
            'forge-admin-suite',
            array($this, 'render_page'),
            'dashicons-admin-generic',
            58
        );
    }

    public function get_hook_suffix()
    {
        return $this->hook_suffix;
    }

    public function render_page()
    {
        echo '<div id="forge-admin-suite-root"></div>';
    }
}
