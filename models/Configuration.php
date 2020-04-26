<?php namespace app\models;
use Mixy;
use kiss\db\ActiveRecord;
use kiss\db\ActiveQuery;
use kiss\helpers\StringHelper;
use kiss\schema\StringProperty;
use Ramsey\Uuid\Uuid;

class Configuration extends ActiveRecord {

    public static function tableName() { return '$configurations'; }
    
    public $id;
    public $owner;
    public $screen;
    public $uuid;
    public $json;

    public static function getSchemaProperties($options = []) {
        return [
            'uuid'      => new StringProperty('UUID of the screen'),
            'json'      => new StringProperty('Schema of the properties'),
        ];
    }

    /** Decodes the json into an assoc
     * @return array
     */
    public function getJson() { 
        return json_decode($this->json, true);
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

}