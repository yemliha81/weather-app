<?php

use Phalcon\Mvc\Model;

class Users extends Model
{
    public $email;
    public $password;
    public $city_id;
    public $language;
    public $opr_system;
    public $premium_user;
    public $auth_token;
    public $inserted_date;
    public $last_login;
}

?>