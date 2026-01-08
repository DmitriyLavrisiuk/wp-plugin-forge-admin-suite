<?php

namespace Forge_Admin_Suite;

use WP_Error;
use WP_REST_Request;

defined('ABSPATH') || exit;

class Rest
{
    private const OPTION_KEY = 'forge_admin_suite_options';

    public function init()
    {
        add_action('rest_api_init', array($this, 'register_routes'));
    }

    public function register_routes()
    {
        register_rest_route(
            'forge-admin-suite/v1',
            '/ping',
            array(
                'methods' => 'GET',
                'callback' => array($this, 'handle_ping'),
                'permission_callback' => array($this, 'check_permissions'),
            )
        );

        register_rest_route(
            'forge-admin-suite/v1',
            '/settings',
            array(
                array(
                    'methods' => 'GET',
                    'callback' => array($this, 'get_settings'),
                    'permission_callback' => array($this, 'check_permissions'),
                ),
                array(
                    'methods' => 'POST',
                    'callback' => array($this, 'update_settings'),
                    'permission_callback' => array($this, 'check_permissions'),
                ),
            )
        );
    }

    public function check_permissions(WP_REST_Request $request)
    {
        if (!current_user_can('manage_options')) {
            return new WP_Error(
                'forge_admin_suite_forbidden',
                __('You are not allowed to access this resource.', 'forge-admin-suite'),
                array('status' => 403)
            );
        }

        $nonce = $request->get_header('X-WP-Nonce');
        if (!$nonce || !wp_verify_nonce($nonce, 'wp_rest')) {
            return new WP_Error(
                'forge_admin_suite_invalid_nonce',
                __('Invalid or missing REST nonce.', 'forge-admin-suite'),
                array('status' => 401)
            );
        }

        return true;
    }

    public function handle_ping()
    {
        return array(
            'ok' => true,
            'version' => FORGE_ADMIN_SUITE_VERSION,
        );
    }

    private function get_default_settings()
    {
        return array(
            'apiEndpoint' => '',
            'enableDebug' => false,
            'loadFrontendAssets' => false,
        );
    }

    public function get_settings()
    {
        $stored = get_option(self::OPTION_KEY, array());
        if (!is_array($stored)) {
            $stored = array();
        }

        return array_merge($this->get_default_settings(), $stored);
    }

    public function update_settings(WP_REST_Request $request)
    {
        $params = $request->get_json_params();
        if (!is_array($params)) {
            $params = array();
        }

        $api_endpoint_raw = isset($params['apiEndpoint'])
            ? sanitize_text_field($params['apiEndpoint'])
            : '';
        $api_endpoint = '';

        if ($api_endpoint_raw !== '') {
            $api_endpoint = esc_url_raw($api_endpoint_raw);
            if ($api_endpoint === '' || !filter_var($api_endpoint, FILTER_VALIDATE_URL)) {
                return new WP_Error(
                    'forge_admin_suite_invalid_api_endpoint',
                    __('API Endpoint must be a valid URL.', 'forge-admin-suite'),
                    array('status' => 400)
                );
            }
        }

        $enable_debug = isset($params['enableDebug'])
            ? (bool) rest_sanitize_boolean($params['enableDebug'])
            : false;

        $load_frontend_assets = isset($params['loadFrontendAssets'])
            ? (bool) rest_sanitize_boolean($params['loadFrontendAssets'])
            : false;

        $settings = array(
            'apiEndpoint' => $api_endpoint,
            'enableDebug' => $enable_debug,
            'loadFrontendAssets' => $load_frontend_assets,
        );

        update_option(self::OPTION_KEY, $settings, false);

        return array_merge($this->get_default_settings(), $settings);
    }
}
