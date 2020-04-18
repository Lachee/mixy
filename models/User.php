<?php namespace app\models;

use kiss\db\ActiveRecord;
use kiss\exception\NotYetImplementedException;

class User extends ActiveRecord {

    public static function tableName() { return '$users'; }

    public $id;
    public $uuid;
    public $username;
    public $email;
    public $refreshToken;


    //Update exiting values
    public function updateFromMixerUser($mixerUser) {
        throw new NotYetImplementedException();
    }


    public function login() {
        throw new NotYetImplementedException();
    }


}