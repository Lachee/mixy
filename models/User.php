<?php namespace app\models;

use kiss\db\ActiveRecord;
use kiss\exception\NotYetImplementedException;
use kiss\db\ActiveQuery;
use app\components\mixer\MixerUser;
use kiss\exception\ArgumentException;
use kiss\exception\InvalidOperationException;
use kiss\Kiss;
use kiss\models\BaseObject;
use kiss\models\OAuthContainer;
use Mixy;
use Ramsey\Uuid\Uuid;

class User extends ActiveRecord {

    public static function tableName() { return '$users'; }

    /** @var int DB ID of the user */
    public $id;
    
    /** @var Uuid UUID of the user */
    public $uuid;
    private $_uuid;

    /** @var string Display name of the user */
    public $username;
    
    /** @var string email of the user */
    public $email;

    /** @var string Mixer user ID */
    public $mixerId;
    private $_mixerUser;
    
    /** @var string Mixer channel ID */
    public $mixerChannelId;
    
    /** @var strings Unique key that is generated for every "logout" or account. */
    protected $accessKey;

    protected function init() {
        if (is_string($this->uuid)) $this->uuid = Uuid::fromString($this->uuid);
        $this->_uuid = $this->uuid;
    }

    protected function beforeSave() { 
        parent::beforeSave(); 

        //Make sure we have a UUID and then update our DB version to the string version.
        if ($this->_uuid == null) throw new ArgumentException('UUID cannot be null');
        $this->uuid = $this->_uuid->toString();

        //The current access key is in a illegal state, lets fix that
        if ($this->accessKey == null) $this->accessKey = substr(bin2hex(random_bytes(32)), 0, 32);
    }
    protected function afterSave() {
        parent::afterSave();
        $this->uuid = $this->_uuid;
    }
    protected function afterLoad($data, $success) {
        parent::afterLoad($data, $success);
        if (is_string($this->uuid))
            $this->uuid = $this->_uuid = Uuid::fromString($this->uuid);
    }

    /** @return ActiveQuery|User searches for users with matching email. */
    public static function findByEmail($email) {
        return self::find()->where([ 'email', $email ]);
    }
    /** @return ActiveQuery|User|null finds a user using the current session data. */
    public static function findBySession() {        
        if (Mixy::$app->session == null)
            throw new InvalidOperationException("Cannot find by session because there is currently no session available.");
        
        $sub = Mixy::$app->session->getClaim('sub', 'n/a');
        $key = Mixy::$app->session->getClaim('key', 'n/a');
        return self::find()->where([ ['uuid', $sub ], ['accessKey', $key] ]);
    }

    /** Updates values from a mixer user
     * @param MixerUser $mixerUser
     * @return User the updated user.
     */
    public function setMixerUser($mixerUser) {
        $this->_mixerUser = $mixerUser;

        $this->mixerId = $mixerUser->id;
        $this->mixerChannelId = $mixerUser->channel->id;
        $this->username = $mixerUser->username;
        $this->email = $mixerUser->email;

        return $this;
    }

    /** @return MixerUser gets the current mixer user. A cache will be updated.  */
    public function getMixerUser() {
        if ($this->_mixerUser != null) return $this->_mixerUser;
        $mixerUser = Mixy::$app->mixer->getOwner($this->getOauthContainer());
        return $this->setMixerUser($mixerUser);
    }

    /** @return OAuthContainer the current oauth container */
    public function getOauthContainer() {
        return new OAuthContainer([ 
            'application'   => 'mixer',
            'identity'      => $this->uuid->toString()
        ]);
    }

    /** Sets the current oauth tokens, storing the access token in the cache
     * @param array $tokenResponse the response from the oAuth.
     * @return OAuthContainer the newly created container
     */
    public function setOauthContainer($tokenResponse) {
        $container = $this->getOauthContainer();
        return $container->setTokens($tokenResponse);
    }

    /** Logs the user in and generates a new JWT */
    public function login() {

        //Create a new JWT for the user
        $jwt = $this->jwt([
            'src'      => 'login',
            'sid'       => Kiss::$app->session->getSessionId(),
        ]);

        //Set the JWT
        Kiss::$app->session->setJWT($jwt);
        return $this->save();
    }

    /** Logs the user out */
    public function logout() {
        $this->accessKey = null;
        Kiss::$app->session->stop()->start();
        return $this->save();
    }

    /** Creates a new JWT for this user 
     * @return string
    */
    public function jwt($payload = [], $expiry = null) {
        if (!is_array($payload)) $payload = json_encode($payload);
        $payload['sub'] = $this->uuid;
        $payload['key'] = $this->accessKey;
        return Kiss::$app->jwtProvider->encode($payload, $expiry);
    }


}