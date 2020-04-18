<?php namespace app\models;

use kiss\db\ActiveRecord;
use kiss\exception\NotYetImplementedException;
use kiss\db\ActiveQuery;
use app\components\mixer\MixerUser;
use kiss\Kiss;
use Ramsey\Uuid\Uuid;

class User extends ActiveRecord {

    public static function tableName() { return '$users'; }

    public $id;
    public $uuid;
    public $mixerId;
    public $username;
    public $email;

    protected $refreshToken;
    protected $sessionId;

    private $_uuid;

    protected function init() {
        if ($this->uuid == null) 
            $this->uuid = $this->_uuid = Uuid::uuid1(Kiss::$app->uuidNodeProvider->getNode());
    }
    protected function beforeSave() { 
        parent::beforeSave(); 
        $this->uuid = $this->_uuid->toString();
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

    /** Updates values from a mixer user
     * @param MixerUser $mixerUser
     */
    public function updateFromMixerUser($mixerUser) {
        $this->mixerId = $mixerUser->id;
        $this->username = $mixerUser->username;
        $this->email = $mixerUser->email;
        $this->refreshToken = $mixerUser->tokens['refreshToken'];
    }

    /** Logs the user in and generates a new JWT */
    public function login() {

        //Store the session ID and create a JWT
        $this->sessionId = Kiss::$app->session->getSessionId();

        //Create a new JWT for the user
        $jwt = $this->jwt([
            'type'      => 'login',
            'username'  => $this->username,
            'sid'       => $this->sessionId,
        ]);

        //Set the JWT
        Kiss::$app->session->setJWT($jwt);
    }

    /** Logs the user out */
    public function logout() {
        $this->sessionId = null;
        Kiss::$app->session->stop()->start();
    }

    /** Creates a new JWT for this user 
     * @return string
    */
    public function jwt($payload = [], $expiry = null) {
        if (!is_array($payload)) $payload = json_encode($payload);
        $payload['sub'] = $this->id;
        $payload['uid'] = $this->uuid;
        return Kiss::$app->jwtProvider->encode($payload, $expiry);
    }

}