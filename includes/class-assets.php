<?php

namespace Forge_Admin_Suite;

defined('ABSPATH') || exit;

class Assets
{
    private $admin_page;
    private $show_missing_assets_notice = false;
    private $use_admin_dev_server = false;
    private $vite_dev_origin = '';
    private $admin_env = array(
        'mode' => 'prod',
        'viteAvailable' => false,
        'viteOrigin' => '',
        'entry' => '',
        'manifestPath' => '',
        'viteProbeUrl' => '',
        'viteProbeOk' => false,
        'viteProbeError' => '',
    );

    public function __construct(Admin_Page $admin_page)
    {
        $this->admin_page = $admin_page;
    }

    public function init()
    {
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
        add_action('admin_notices', array($this, 'maybe_render_admin_notice'));
        add_filter('script_loader_tag', array($this, 'filter_module_scripts'), 10, 3);
        add_action('admin_print_footer_scripts', array($this, 'print_dev_preamble'));
    }

    public function enqueue_assets($hook_suffix)
    {
        if (!$this->is_plugin_page($hook_suffix)) {
            return;
        }

        $mode = apply_filters('forge_admin_suite_asset_mode', null);
        $this->admin_env['viteAvailable'] = $this->is_vite_dev_server_available();
        $this->admin_env['viteOrigin'] = $this->vite_dev_origin;

        if ($mode === 'dev' || ($mode === null && $this->admin_env['viteAvailable'])) {
            $this->use_admin_dev_server = true;
            $this->admin_env['mode'] = 'dev';
            $this->admin_env['entry'] = 'src/main.tsx';
            $main_handle = $this->enqueue_dev_assets();
            $this->add_inline_app_data($main_handle);
            return;
        }

        $main_handle = $this->enqueue_prod_assets();
        if ($main_handle) {
            $this->admin_env['mode'] = 'prod';
            $this->add_inline_app_data($main_handle);
            return;
        }

        $this->show_missing_assets_notice = true;
    }

    private function is_plugin_page($hook_suffix)
    {
        if ($hook_suffix === 'toplevel_page_forge-admin-suite') {
            return true;
        }

        $expected = $this->admin_page->get_hook_suffix();
        if (!empty($expected)) {
            return $hook_suffix === $expected;
        }

        if (function_exists('get_current_screen')) {
            $screen = get_current_screen();
            if ($screen && $screen->id === 'toplevel_page_forge-admin-suite') {
                return true;
            }
        }

        $page = isset($_GET['page']) ? sanitize_key($_GET['page']) : '';
        return $page === 'forge-admin-suite';
    }

    public function enqueue_frontend_assets()
    {
        $settings = $this->get_settings();
        if (empty($settings['loadFrontendAssets'])) {
            return;
        }

        if ($this->is_vite_dev_server_available()) {
            $this->enqueue_frontend_dev_assets();
            return;
        }

        $this->enqueue_frontend_prod_assets();
    }

    private function add_inline_app_data($handle)
    {
        $data = array(
            'restUrl' => esc_url_raw(rest_url()),
            'nonce' => wp_create_nonce('wp_rest'),
            'pluginVersion' => FORGE_ADMIN_SUITE_VERSION,
            'env' => $this->admin_env,
        );

        if ($handle) {
            wp_add_inline_script(
                $handle,
                'window.__FORGE_ADMIN_SUITE__ = ' . wp_json_encode($data) . ';',
                'before'
            );
        }
    }

    private function is_vite_dev_server_available()
    {
        if (isset($_GET['forge_recheck_vite']) && $_GET['forge_recheck_vite'] === '1') {
            delete_transient('forge_admin_suite_vite_origin');
        }

        $cached = get_transient('forge_admin_suite_vite_origin');
        if (is_string($cached) && $cached !== '') {
            $this->vite_dev_origin = $cached;
            $this->admin_env['viteProbeOk'] = true;
            return true;
        }

        $probe_urls = array(
            'http://127.0.0.1:5173/@vite/client',
            'http://localhost:5173/@vite/client',
        );
        $response = null;
        $is_up = false;
        $last_error = '';
        $last_url = '';

        foreach ($probe_urls as $probe_url) {
            $last_url = $probe_url;
            $response = wp_remote_get(
                $probe_url,
                array(
                    'timeout' => 0.5,
                )
            );

            if (is_wp_error($response)) {
                $last_error = $response->get_error_message();
                continue;
            }

            $status = wp_remote_retrieve_response_code($response);
            if ($status === 200) {
                $is_up = true;
                $last_error = '';
                break;
            }

            $last_error = wp_remote_retrieve_response_message($response);
        }

        $this->vite_dev_origin = $is_up ? preg_replace('#/@vite/client$#', '', $last_url) : '';
        $this->admin_env['viteProbeUrl'] = $last_url;
        $this->admin_env['viteProbeOk'] = $is_up;
        $this->admin_env['viteProbeError'] = $is_up ? '' : $last_error;
        set_transient('forge_admin_suite_vite_origin', $this->vite_dev_origin, 60);

        return $is_up;
    }

    private function enqueue_dev_assets()
    {
        $origin = $this->vite_dev_origin;
        if ($origin === '') {
            return '';
        }

        $client_handle = 'forge-admin-suite-vite-client';
        $app_handle = 'forge-admin-suite-vite-app';

        wp_enqueue_script(
            $client_handle,
            $origin . '/@vite/client',
            array(),
            null,
            true
        );
        wp_script_add_data($client_handle, 'type', 'module');

        wp_enqueue_script(
            $app_handle,
            $origin . '/src/main.tsx',
            array(),
            null,
            true
        );
        wp_script_add_data($app_handle, 'type', 'module');

        return $app_handle;
    }

    private function enqueue_prod_assets()
    {
        $manifest = $this->get_manifest_data();
        if (!is_array($manifest)) {
            return false;
        }

        $entry_key = 'src/main.tsx';
        $entry = null;

        if (isset($manifest[$entry_key])) {
            $entry = $manifest[$entry_key];
        } elseif (isset($manifest['index.html'])) {
            $entry_key = 'index.html';
            $entry = $manifest['index.html'];
            if (defined('WP_DEBUG') && WP_DEBUG) {
                $this->manifest_debug['fallback_used'] = true;
            }
        } else {
            $this->manifest_debug['selected_key'] = '';
            $this->manifest_debug['available_keys'] = array_keys($manifest);
            return false;
        }

        $this->admin_env['entry'] = $entry_key;
        $main_handle = false;
        if (!empty($entry['file'])) {
            $handle = 'forge-admin-suite-app';
            $relative_path = 'ui/dist/' . ltrim($entry['file'], '/');
            wp_enqueue_script(
                $handle,
                FORGE_ADMIN_SUITE_URL . $relative_path,
                array(),
                $this->get_asset_version($relative_path),
                true
            );
            wp_script_add_data($handle, 'type', 'module');
            $main_handle = $handle;
        }

        if (!empty($entry['css']) && is_array($entry['css'])) {
            foreach ($entry['css'] as $index => $css_file) {
                $style_handle = 'forge-admin-suite-style-' . $index;
                $relative_path = 'ui/dist/' . ltrim($css_file, '/');
                wp_enqueue_style(
                    $style_handle,
                    FORGE_ADMIN_SUITE_URL . $relative_path,
                    array(),
                    $this->get_asset_version($relative_path)
                );
            }
        }

        return $main_handle;
    }

    private function enqueue_frontend_dev_assets()
    {
        $origin = $this->vite_dev_origin;
        if ($origin === '') {
            return;
        }

        $client_handle = 'forge-admin-suite-frontend-vite-client';
        $app_handle = 'forge-admin-suite-frontend-vite-app';

        wp_enqueue_script(
            $client_handle,
            $origin . '/@vite/client',
            array(),
            null,
            true
        );
        wp_script_add_data($client_handle, 'type', 'module');

        wp_enqueue_script(
            $app_handle,
            $origin . '/src/frontend.ts',
            array(),
            null,
            true
        );
        wp_script_add_data($app_handle, 'type', 'module');
    }

    private function enqueue_frontend_prod_assets()
    {
        $manifest = $this->get_manifest_data();
        if (!is_array($manifest) || !isset($manifest['src/frontend.ts'])) {
            return;
        }

        $entry = $manifest['src/frontend.ts'];
        if (!empty($entry['file'])) {
            $handle = 'forge-admin-suite-frontend-app';
            $relative_path = 'ui/dist/' . ltrim($entry['file'], '/');
            wp_enqueue_script(
                $handle,
                FORGE_ADMIN_SUITE_URL . $relative_path,
                array(),
                $this->get_asset_version($relative_path),
                true
            );
            wp_script_add_data($handle, 'type', 'module');
        }

        if (!empty($entry['css']) && is_array($entry['css'])) {
            foreach ($entry['css'] as $index => $css_file) {
                $style_handle = 'forge-admin-suite-frontend-style-' . $index;
                $relative_path = 'ui/dist/' . ltrim($css_file, '/');
                wp_enqueue_style(
                    $style_handle,
                    FORGE_ADMIN_SUITE_URL . $relative_path,
                    array(),
                    $this->get_asset_version($relative_path)
                );
            }
        }
    }

    private function get_settings()
    {
        $stored = get_option('forge_admin_suite_options', array());
        if (!is_array($stored)) {
            $stored = array();
        }

        return array_merge(
            array(
                'apiEndpoint' => '',
                'enableDebug' => false,
                'loadFrontendAssets' => false,
            ),
            $stored
        );
    }

    private function get_asset_version($relative_path)
    {
        $absolute_path = FORGE_ADMIN_SUITE_PATH . ltrim($relative_path, '/');
        if (file_exists($absolute_path)) {
            $modified = filemtime($absolute_path);
            if ($modified !== false) {
                return (string) $modified;
            }
        }

        return FORGE_ADMIN_SUITE_VERSION;
    }

    private function get_manifest_data()
    {
        $primary_path = FORGE_ADMIN_SUITE_PATH . 'ui/dist/.vite/manifest.json';
        $fallback_path = FORGE_ADMIN_SUITE_PATH . 'ui/dist/manifest.json';
        $manifest_path = file_exists($primary_path) ? $primary_path : $fallback_path;
        $this->admin_env['manifestPath'] = $manifest_path === $primary_path
            ? 'ui/dist/.vite/manifest.json'
            : 'ui/dist/manifest.json';

        if (!file_exists($manifest_path)) {
            return false;
        }

        $manifest_contents = file_get_contents($manifest_path);
        if ($manifest_contents === false) {
            return false;
        }

        $manifest = json_decode($manifest_contents, true);
        if (!is_array($manifest)) {
            return false;
        }

        return $manifest;
    }

    public function maybe_render_admin_notice()
    {
        if (!$this->show_missing_assets_notice) {
            return;
        }

        if (function_exists('get_current_screen')) {
            $screen = get_current_screen();
            if (!$screen || $screen->id !== 'toplevel_page_forge-admin-suite') {
                return;
            }
        }

        echo '<div class="notice notice-error"><p>';
        echo esc_html__('Forge Admin Suite UI assets not found. Run pnpm -C ui build or pnpm -C ui dev.', 'forge-admin-suite');
        echo '</p></div>';
    }

    public function filter_module_scripts($tag, $handle, $src)
    {
        if (strpos($handle, 'forge-admin-suite-') !== 0) {
            return $tag;
        }

        if (strpos($tag, 'type=') !== false) {
            return $tag;
        }

        return str_replace('<script ', '<script type="module" ', $tag);
    }

    public function print_dev_preamble()
    {
        if (!$this->use_admin_dev_server) {
            return;
        }

        if (function_exists('get_current_screen')) {
            $screen = get_current_screen();
            if (!$screen || $screen->id !== 'toplevel_page_forge-admin-suite') {
                return;
            }
        }

        echo '<script type="module">';
        echo 'import RefreshRuntime from "' . esc_url_raw($this->vite_dev_origin) . '/@react-refresh";';
        echo 'RefreshRuntime.injectIntoGlobalHook(window);';
        echo 'window.$RefreshReg$ = () => {};';
        echo 'window.$RefreshSig$ = () => (type) => type;';
        echo 'window.__vite_plugin_react_preamble_installed__ = true;';
        echo '</script>';
    }
}
