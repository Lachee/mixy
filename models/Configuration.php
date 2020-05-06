<?php namespace app\models;
use Mixy;
use kiss\db\ActiveRecord;
use kiss\db\ActiveQuery;
use kiss\helpers\StringHelper;
use kiss\Kiss;
use kiss\schema\StringProperty;
use Ramsey\Uuid\Uuid;

class Configuration extends ActiveRecord {

    const EXPIRY = 30 * 24 * 60 * 60;

    public static function tableName() { return '$configurations'; }
    
    public $id;
    public $owner;
    public $screen;
    public $uuid;
    public $json;
    public $token;

    public static function getSchemaProperties($options = []) {
        return [
            'uuid'      => new StringProperty('UUID of the screen'),
            'json'      => new StringProperty('Schema of the properties'),
            'token'     => new StringProperty('Randomly generated string for JWT')
        ];
    }

    /** Decodes the json into an assoc
     * @return array
     */
    public function getJson() { 
        return json_decode($this->json, true);
    }

    /** Sets the json configuration
     * @return Configuration this
    */
    public function setJson($obj) {
        $this->json = json_encode($obj);
        return $this;
    }

    /** @return ActiveQuery|Screen finds a screen */
    public function getScreen() {
        return Screen::findByKey($this->screen);
    }

    /** @return ActiveQuery|Configuration finds the screen that matches the uuid */
    public static function findByUuid($uuid) {
        return self::find()->where(['uuid', $uuid]);
    }


    /** @return ActiveQuery|Configuration finds the configurations that belong to a user */
    public static function findByOwner($user) {
        if ($user instanceof User) $user = $user->id;
        return self::find()->where(['owner', $user]);
    }

    /** @return ActiveQuery|Configuration finds the configuration from a JWT token */
    public static function findByJWT($jwt) {
        $uuid = ''; $token = '';
        if (is_array($jwt)) {
            $uuid = $jwt['uuid'];
            $token = $jwt['token'];
        } else {
            $uuid = $jwt->uuid;
            $token = $jwt->token;
        }
        return self::find()->where([['uuid', $uuid], ['token', $token ]]);
    }

    /** Creates a new JWT 
     * @param User|null the current user to encode the token with.
     * @return string the JWT
     */
    public function jwt($user = null) {
        //Prepare the payload
        $payload = [
            'src'   => 'configuration',
            'uuid'  => $this->uuid,
            'token' => $this->token
        ];

        //Encode using the user if available, otherwise encode using the standard provider
        if ($user != null) return $user->jwt($payload);
        return Kiss::$app->jwtProvider->encode($payload, self::EXPIRY);
    }


}