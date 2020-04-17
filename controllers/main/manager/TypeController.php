<?php
namespace controllers\main\manager;

use App;
use core\controllers\Controller;
use Exception;
use exception\HttpException;
use helpers\HTTP;
use helpers\Response;
use models\StoredDefinition;
use models\StoredGraph;
use models\StoredType;
use xve\definition\ActionDefinition;
use xve\definition\CallDefinition;
use xve\definition\Definition;
use xve\definition\EventDefinition;
use xve\definition\PropertyDefinition;
use xve\type\ValueType;

class TypeController extends Controller {

    /** Index that lists all types */
    function actionIndex() {
        $types = StoredType::find()->all();

        $precalcIcons = [];
        foreach($types as $t) {
            if (!empty($t->icon) && !isset($precalcIcons[$t->icon])) {
                $ico = \lachee\fontawesome\Cheatsheet::fromName($t->icon);
                if ($ico == null) {
                    $precalcIcons[$t->icon] = ".fa-{$t->icon}::before { content: '\\{$t->icon}';}";
                }
            }
        }

        //Render the page
        return $this->render('index', [
            'types'         => $types,
            'precalcIcons'  => join("", $precalcIcons),
        ]);
    }

    function actionCreate() {

        if (HTTP::hasPost()) {

            $return = HTTP::post('return', '');
            $data = HTTP::post('root', null);
            $name = $data['name'];

            if ($data != null) {
                
                $model = StoredType::findByName($data['name'])->one();
                if ($model == false) {
                
                    //Create the new model
                    $model = new StoredType();
                    $model->name = $name;

                    //Load the data and save it
                    if ($model->load($data) && $model->save()) {                    
                        App::$xve->session->addNotification("Created {$name}", 'success');
                        
                        if ($return == 'list')  return Response::redirect(['index' ]);
                        return Response::redirect(['edit', 'type' => $name ]);

                    } else {
                        App::$xve->session->addNotification($model->errors(), 'danger');
                    }
                } else {
                    App::$xve->session->addNotification("{$name} is already being used", 'danger');
                }
            } else {
                App::$xve->session->addNotification("Failed because there is no data", 'danger');
            }
        }
                      
        //Create a blank version of the definition
        $schema = StoredType::getJsonSchema();
        
        //Render the page
        return $this->render('edit', [    
            'model'         => null,
            'schema'        => $schema,
            'title'         => 'Create'
        ]);
    }

    function actionEdit() {
        $name = HTTP::get('type', '');                  //Current Type Name
        $model = StoredType::findByName($name)->one();   //Current Type
        if ($model === false || $model == null) throw new HttpException(HTTP::NOT_FOUND);

        if (HTTP::hasPost()) {
            $return = HTTP::post('return', '');
            $data = HTTP::post('root', null);
            if ($data != null) {
                if ($model->load($data) && $model->save()) {                    
                    App::$xve->session->addNotification("Updated {$name}", 'success');
                    
                    if ($return == 'list')  return Response::redirect(['index' ]);
                    return Response::redirect(['edit', 'type' => $name ]);

                } else {
                    App::$xve->session->addNotification($model->errors(), 'danger');
                }
            }
        }
                      
        //Create a blank version of the definition
        $schema = StoredType::getJsonSchema([
            'additional_types' => $model->getAllTypes()
        ]);
        
        //Render the page
        return $this->render('edit', [    
            'model'         => $model,
            'schema'        => $schema,
            'title'         => 'Edit'
        ]);
    }

    /** Preimports the values */
    function actionImport() {
        $json = file_get_contents(App::$xve->baseDir() . "/xve/type/_types.json");
        $types = json_decode($json, true);
        foreach($types as $name => $type) {
            //Convert the unicode character
            if (!empty($type['icon'])) {
                $k = mb_convert_encoding($type['icon'], 'UCS-2LE', 'UTF-8');
                $k1 = ord(substr($k, 0, 1));
                $k2 = ord(substr($k, 1, 1));
                $val = $k2 * 256 + $k1;
                $type['icon'] = dechex($val);
            }

            //Store the values
            $st = new StoredType(array_merge($type, [ 'name' => $name ]));
            $st->setFields($type['fields']);
            $st->save();
        }
        return 'done';
    }

    /** @return string formatted value in K & Ms */
    private function formatCount($value) {
        if ($value > 999 && $value <= 999999) {
            $result = floor($value / 1000) . ' K';
        } elseif ($value > 999999) {
            $result = floor($value / 1000000) . ' M';
        } else {
            $result = $value;
        }

        return $result;
    }

}