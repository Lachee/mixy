<?php namespace app\models;
use Mixy;
use kiss\db\ActiveRecord;
use kiss\db\ActiveQuery;
use kiss\schema\StringProperty;
use Ramsey\Uuid\Uuid;

class Screen extends ActiveRecord {

    public static function tableName() { return '$screens'; }
    
    public $id;
    public $uuid;

    public $html;
    public $js;
    public $css;

    public static function getSchemaProperties($options = []) {
        return [
            'uuid'  => new StringProperty('UUID of the screen'),
            'html'  => new StringProperty('HTML code of the screen'),
            'js'    => new StringProperty('JS code of the screen'),
            'css'   => new StringProperty('CSS code of the screen'),

        ];
    }

    protected function init() {
        //Set a default uuid
        if ($this->uuid == null)
            $this->uuid = Uuid::uuid1(Mixy::$app->uuidNodeProvider->getNode())->toString();
    }

    /** @return ActiveQuery|Screen finds the screen that matches the uuid */
    public static function findByUuid($uuid) {
        return self::find()->where(['uuid', $uuid]);
    }

}