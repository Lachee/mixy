<?php namespace app\components;

use kiss\models\BaseObject;

class MixerUser extends BaseObject {
    /** @var Mixer mixer instance */ 
    public $mixer;

    /** Accesse Tokens */
    public $tokens;

    public $id;
    public $level;
    public $social;
    public $username;
    public $email;
    public $verified;
    public $experience;
    public $sparks;
    public $avatarUrl;
    public $bio;
    public $primaryTeam;

    public $channel;
    public $twoFactor;

}