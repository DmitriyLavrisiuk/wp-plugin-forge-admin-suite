<?php

namespace Forge_Admin_Suite;

use WP_Error;
use WP_REST_Request;

defined('ABSPATH') || exit;

class Rest
{
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
}
