<?php
namespace Customizations\Apis\Api;

interface CustomerLoginInterface
{
    /**
     * Login customer and return token
     * @param string $email
     * @param string $password
     * @param string $machineIp
     * @return string
     */
    public function login($email, $password, $machineIp);
}

