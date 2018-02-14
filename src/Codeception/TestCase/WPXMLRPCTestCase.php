<?php

namespace Codeception\TestCase;

include_once ABSPATH.'wp-admin/includes/admin.php';
include_once ABSPATH.WPINC.'/class-IXR.php';
include_once ABSPATH.WPINC.'/class-wp-xmlrpc-server.php';

class WPXMLRPCTestCase extends WPTestCase
{
    protected $myxmlrpcserver;

    public function setUp()
    {
        parent::setUp();

        add_filter('pre_option_enable_xmlrpc', '__return_true');

        $this->myxmlrpcserver = new \wp_xmlrpc_server();
    }

    public function tearDown()
    {
        remove_filter('pre_option_enable_xmlrpc', '__return_true');

        $this->remove_added_uploads();

        parent::tearDown();
    }

    protected function make_user_by_role($role)
    {
        return self::factory()->user->create([
            'user_login' => $role,
            'user_pass'  => $role,
            'role'       => $role,
        ]);
    }
}
