<?php

require_once 'User.php';

class Admin extends User
{
    public function __construct($id, $username)
    {
        parent::__construct($id, $username, 'admin');
    }

    // Override
    public function getDashboard()
    {
        return "Dashboard Admin";
    }
}