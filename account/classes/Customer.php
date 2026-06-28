<?php

require_once 'User.php';

class Customer extends User
{
    public function __construct($id, $username)
    {
        parent::__construct($id, $username, 'customer');
    }

    // Override
    public function getDashboard()
    {
        return "Dashboard Customer";
    }
}