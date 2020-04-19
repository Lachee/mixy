<?php namespace app\models;

use kiss\db\ActiveRecord;
use kiss\exception\NotYetImplementedException;
use kiss\db\ActiveQuery;
use app\components\mixer\MixerUser;
use kiss\Kiss;
use kiss\models\BaseObject;
use kiss\models\OAuthContainer;
use Mixy;
use Ramsey\Uuid\Uuid;

class User extends ActiveRecord {

    public static function tableName() { return '$users'; }

    public $id;
    public $uuid;
    private $_uuid;

    public $username;
    public $email;

    public $mixerId;
    public $mixerChannelId;
    
    /** Unique key that is generated for every "logout" or account. */
    protected $accessKey;

    protected function init() {
        if ($this->uuid == null) 
            $this->uuid = $this->_uuid = Uuid::uuid1(Kiss::$app->uuidNodeProvider->getNode());
    }
    protected function beforeSave() { 
        parent::beforeSave(); 
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
         $this->uuid = $this->_uuid = Uuid::fromString($this->uuid);
    }

    /** @return ActiveQuery|User searches for users with matching email. */
    public static function findByEmail($email) {
        return self::find()->where([ 'email', $email ]);
    }

    /** @return ActiveQuery|User finds a user using the current session data. */
    public static function findBySession() {        
        $sub = Mixy::$app->session->getClaim('sub', 'n/a');
        $key = Mixy::$app->session->getClaim('key', 'n/a');
        return self::find()->where([ ['id', $sub ], ['accessKey', $key] ]);
    }

    /** Updates values from a mixer user
     * @param MixerUser $mixerUser
     */
    public function updateFromMixerUser($mixerUser) {
        $this->mixerId = $mixerUser->id;
        $this->mixerChannelId = $mixerUser->channel->id;

        $this->username = $mixerUser->username;
        $this->email = $mixerUser->email;
    }

    /** @return oAuthContainer the current oauth container */
    public function getOauthTokens() {
        return new OAuthContainer([ 
            'application'   => 'mixer',
            'identity'      => $this->uuid
        ]);
    }

    /** Sets the current oauth tokens, storing the access token in the cache */
    public function setOauthTokens($tokenResponse) {
        $container = $this->getOauthTokens();
        return $container->setTokens($tokenResponse);
    }

    /** Logs the user in and generates a new JWT */
    public function login() {

        //Create a new JWT for the user
        $jwt = $this->jwt([
            'type'      => 'login',
            'username'  => $this->username,
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
        $payload['sub'] = $this->id;
        $payload['uid'] = $this->uuid;
        $payload['key'] = $this->accessKey;
        return Kiss::$app->jwtProvider->encode($payload, $expiry);
    }

}