<?php namespace app\components\mixer;

use kiss\models\BaseObject;
use kiss\schema\RefProperty;

class MixerUser extends BaseObject {
    /** @var Mixer mixer instance */ 
    public $mixer;

    /** Internal cache of the access token, to be able to make future requests */
    protected $accessToken;

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

    /** @var MixerChannel channel */
    public $channel;
    public $twoFactor;

    /** {@inheritdoc} */
    public static function getSchemaProperties($options = []) {
        $schema = parent::getSchemaProperties($options);
        $schema['channel'] = new RefProperty(MixerChannel::class);
        return $schema;
    }
}