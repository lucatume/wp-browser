<?php

class WP_UnitTest_Factory_For_User extends WP_UnitTest_Factory_For_Thing
{
    public function __construct($factory = null)
    {
        parent::__construct($factory);
        $this->default_generation_definitions = [
            'user_login' => new WP_UnitTest_Generator_Sequence('User %s'),
            'user_pass'  => 'password',
            'user_email' => new WP_UnitTest_Generator_Sequence('user_%s@example.org'),
        ];
    }

    public function create_object($args)
    {
        return wp_insert_user($args);
    }

    public function update_object($user_id, $fields)
    {
        $fields['ID'] = $user_id;

        return wp_update_user($fields);
    }

    public function get_object_by_id($user_id)
    {
        return new WP_User($user_id);
    }
}
