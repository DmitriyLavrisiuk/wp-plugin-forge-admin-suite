<?php

namespace Forge_Admin_Suite;

if (!defined('ABSPATH')) {
    exit;
}

class Assets
{
    private $admin_page;

    public function __construct(Admin_Page $admin_page)
    {
        $this->admin_page = $admin_page;
    }

    public function init()
    {
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
    }

    public function enqueue_assets($hook_suffix)
    {
        if (!$this->is_plugin_page($hook_suffix)) {
            return;
        }

        $this->register_app_data();

        $mode = apply_filters('forge_admin_suite_asset_mode', null);

        if ($mode === 'dev' || ($mode === null && $this->is_vite_dev_server_available())) {
            $this->enqueue_dev_assets();
            return;
        }

        $this->enqueue_prod_assets();
    }

    private function is_plugin_page($hook_suffix)
    {
        $expected = $this->admin_page->get_hook_suffix();
        if (!empty($expected)) {
            return $hook_suffix === $expected;
        }

        $page = isset($_GET['page']) ? sanitize_key($_GET['page']) : '';
        return $page === 'forge-admin-suite';
    }

    private function register_app_data()
    {
        $data = array(
            'restUrl' => esc_url_raw(rest_url()),
            'nonce' => wp_create_nonce('wp_rest'),
            'pluginVersion' => FORGE_ADMIN_SUITE_VERSION,
        );

        wp_register_script('forge-admin-suite-app-data', '', array(), FORGE_ADMIN_SUITE_VERSION, true);
        wp_enqueue_script('forge-admin-suite-app-data');
        wp_add_inline_script(
            'forge-admin-suite-app-data',
            'window.__FORGE_ADMIN_SUITE__ = ' . wp_json_encode($data) . ';',
            'before'
        );
    }

    private function is_vite_dev_server_available()
    {
        $response = wp_remote_get(
            'http://localhost:5173/@vite/client',
            array(
                'timeout' => 0.2,
            )
        );

        if (is_wp_error($response)) {
            return false;
        }

        return wp_remote_retrieve_response_code($response) === 200;
    }

    private function enqueue_dev_assets()
    {
        $client_handle = 'forge-admin-suite-vite-client';
        $app_handle = 'forge-admin-suite-vite-app';

        wp_enqueue_script(
            $client_handle,
            'http://localhost:5173/@vite/client',
            array(),
            null,
            true
        );
        wp_script_add_data($client_handle, 'type', 'module');

        wp_enqueue_script(
            $app_handle,
            'http://localhost:5173/src/main.tsx',
            array(),
            null,
            true
        );
        wp_script_add_data($app_handle, 'type', 'module');
    }

    private function enqueue_prod_assets()
    {
        $manifest_path = FORGE_ADMIN_SUITE_PATH . 'ui/dist/.vite/manifest.json';
        if (!file_exists($manifest_path)) {
            return;
        }

        $manifest_contents = file_get_contents($manifest_path);
        if ($manifest_contents === false) {
            return;
        }

        $manifest = json_decode($manifest_contents, true);
        if (!is_array($manifest) || !isset($manifest['src/main.tsx'])) {
            return;
        }

        $entry = $manifest['src/main.tsx'];
        if (!empty($entry['file'])) {
            $handle = 'forge-admin-suite-app';
            wp_enqueue_script(
                $handle,
                FORGE_ADMIN_SUITE_URL . 'ui/dist/' . ltrim($entry['file'], '/'),
                array(),
                FORGE_ADMIN_SUITE_VERSION,
                true
            );
            wp_script_add_data($handle, 'type', 'module');
        }

        if (!empty($entry['css']) && is_array($entry['css'])) {
            foreach ($entry['css'] as $index => $css_file) {
                $style_handle = 'forge-admin-suite-style-' . $index;
                wp_enqueue_style(
                    $style_handle,
                    FORGE_ADMIN_SUITE_URL . 'ui/dist/' . ltrim($css_file, '/'),
                    array(),
                    FORGE_ADMIN_SUITE_VERSION
                );
            }
        }
    }
}
