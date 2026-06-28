<?php

class User
{
    protected $id;
    protected $username;
    protected $role;

    public function __construct($id, $username, $role)
    {
        $this->id = $id;
        $this->username = $username;
        $this->role = $role;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function getRole()
    {
        return $this->role;
    }

    // Method yang akan dioverride
    public function getDashboard()
    {
        return "Dashboard User";
    }
}