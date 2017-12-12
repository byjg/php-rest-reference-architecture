<?php

namespace Test\Functional\Rest;

class Credentials
{
    public static function getAdminUser()
    {
        return [
            'username' => (getenv('TEST_ADMIN_USER') ? getenv('TEST_ADMIN_USER') : 'admin@example.com'),
            'password' => (getenv('TEST_ADMIN_PASSWORD') ? getenv('TEST_ADMIN_PASSWORD') : 'pwd'),
        ];
    }

    public static function getRegularUser()
    {
        return [
            'username' => (getenv('TEST_REGULAR_USER') ? getenv('TEST_REGULAR_USER') : 'user@example.com'),
            'password' => (getenv('TEST_REGULAR_PASSWORD') ? getenv('TEST_REGULAR_PASSWORD') : 'pwd'),
        ];
    }
}