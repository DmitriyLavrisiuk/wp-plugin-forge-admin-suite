<?php

namespace Forge_Admin_Suite;

if (!defined('ABSPATH')) {
    exit;
}

require_once FORGE_ADMIN_SUITE_PATH . 'includes/class-admin-page.php';
require_once FORGE_ADMIN_SUITE_PATH . 'includes/class-assets.php';
require_once FORGE_ADMIN_SUITE_PATH . 'includes/class-rest.php';

class Plugin
{
    private static $instance = null;

    /** @var Admin_Page */
    private $admin_page;

    /** @var Assets */
    private $assets;

    /** @var Rest */
    private $rest;

    public static function instance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function __construct()
    {
        $this->admin_page = new Admin_Page();
        $this->assets = new Assets($this->admin_page);
        $this->rest = new Rest();

        add_action('init', array($this, 'init'));
    }

    public function init()
    {
        $this->admin_page->init();
        $this->assets->init();
        $this->rest->init();
    }
}
