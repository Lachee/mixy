<?php namespace app\models;
use Mixy;
use kiss\db\ActiveRecord;
use kiss\db\ActiveQuery;
use kiss\helpers\StringHelper;
use kiss\schema\StringProperty;
use Ramsey\Uuid\Uuid;
use app\models\Configuration;

class Screen extends ActiveRecord {

    public static function tableName() { return '$screens'; }
    
    public $id;
    public $uuid;

    public $html;
    public $js;
    public $css;
    public $json;
    private $_compiledDefaults = null;

    public static function getSchemaProperties($options = []) {
        return [
            'uuid'      => new StringProperty('UUID of the screen'),
            'html'      => new StringProperty('HTML code of the screen'),
            'js'        => new StringProperty('JS code of the screen'),
            'css'       => new StringProperty('CSS code of the screen'),
            'json'      => new StringProperty('Schema of the properties'),
        ];
    }

    /** Configures the screen
     * @param Configuration $configuration
     * @return Screen self
     */
    public function configure($configuration) {
        $defaults = $this->getJsonDefaults();
        $config = $configuration->getJson();
        $this->_compiledDefaults = array_merge($defaults, $config);
        return $this;
    }

    /** Decodes the Json data and prepares the defaults.
     * @return array The resolved JSON array 
    */
    public function getJsonDefaults() {
        if ($this->_compiledDefaults !== null) return $this->_compiledDefaults;
        $this->_compiledDefaults = json_decode($this->json, true);
        $this->_compiledDefaults = $this->cleanJSON($this->_compiledDefaults);
        return $this->_compiledDefaults;
    }

    /** @return string The compiled HTML */
    public function compileHTML() {
        $json = $this->getJsonDefaults();
        $values = $this->flatten($json, "{{options.", ".", "}}");        
        $result = str_replace(array_keys($values), array_values($values), $this->html);
        return $result;
    }

    /** @return string The compiled CSS */
    public function compileCSS() {
        $json = $this->getJsonDefaults();
        $values = $this->flatten($json, "var(--options-", "-", ")");
        $result = str_replace(array_keys($values), array_values($values), $this->css);
        return $result;
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

    private function flatten($array, $path="", $seperator=".", $suffix = ")"){
        $output = array();
        foreach($array as $key => $value) {
            if(is_array($value)) {
                $output = array_merge($output, $this->flatten($value, (!empty($path)) ? $path.$key.$seperator : $key.$seperator));
            }
            else $output[$path.$key.$suffix] = $value;
        }
        return $output ;
    }
    
    private function cleanJSON($arr) {

        if (is_array($arr)) {
            if (isset($arr['$default'])) 
            {
                return $arr['$default'];
            } 
            else
            {
                foreach($arr as $key => $value) {
                    if (StringHelper::startsWith($key, '$')) continue;
                    $arr[$key] = $this->cleanJSON($value);
                }
            }
        }

        return $arr;
    }

}