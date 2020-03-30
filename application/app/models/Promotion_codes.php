<?php

use Phalcon\Mvc\Model;

class Promotion_codes extends Model
{
    public $code;
    public $is_active;
    public $user_id;
}

?>