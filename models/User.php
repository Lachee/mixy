<?php namespace app\models;

use kiss\db\ActiveRecord;

class User extends ActiveRecord {

    public static function tableName() { return '$users'; }

    public $id;
    public $uuid;
    public $username;
    public $email;
    public $refreshToken;


}