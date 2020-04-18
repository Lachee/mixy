<?php namespace app\components\mixer;

use kiss\models\BaseObject;
use kiss\schema\RefProperty;

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

    /** {@inheritdoc} */
    public static function getSchemaProperties($options = []) {
        $schema = parent::getSchemaProperties($options);
        $schema['channel'] = new RefProperty(MixerChannel::class);
        return $schema;
    }
}