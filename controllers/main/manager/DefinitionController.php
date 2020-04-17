<?php
namespace controllers\main\manager;

use App;
use core\controllers\Controller;
use Exception;
use exception\HttpException;
use helpers\HTTP;
use helpers\Response;
use models\StoredDefinition;
use xve\definition\ActionDefinition;
use xve\definition\CallDefinition;
use xve\definition\Definition;
use xve\definition\EventDefinition;
use xve\definition\MathDefinition;
use xve\definition\PropertyDefinition;

class DefinitionController extends Controller {


    function actionIndex() {
        $xve = App::$xve->getXveConfigurator();
        $definitions = $xve->getDefinitions();

        //Count all the definitions
        $counts = [
            'Definitions'   => count($definitions),
            'Events'        => 0,
            'Actions'       => 0,
            'Property'      => 0,
            'Other'         => 0
        ];
        foreach($definitions as $def) {
            if ($def instanceof PropertyDefinition)         $counts['Property']++;
            else if ($def instanceof EventDefinition)       $counts['Events']++;
            else if ($def instanceof ActionDefinition)      $counts['Actions']++;
            else                                            $counts['Other']++;
        }

        //Format the count
        foreach($counts as $key => $tally) $counts[$key] = $this->formatCount($tally);

        //Render the page
        return $this->render('index', [
            'definitions'   => $definitions,
            'counts'        => $counts,
        ]);
    }

    function actionCreate() {


        if (HTTP::hasPost()) {
            $return = HTTP::post('return', '');
            $data = HTTP::post('root', null);
            $class = HTTP::post('class', null);
            $type = HTTP::post('type', null);

            if ($data != null && !empty($class) && !empty($type)) {
                
                //Update the model
                $model = StoredDefinition::findByType($type)->one();
                if ($model == false) {
                    $model = new StoredDefinition();
                    $model->type = $type;
                    $model->class = $class;
                    $model->data = $data;

                    //Save the model
                    if ($model->save()) {
                        App::$xve->session->addNotification('Saved Succesfully', 'success');                    
                        if ($return == 'list')  return Response::redirect(['index' ]);
                        return Response::redirect(['edit', 'type' => $type ]);

                    } else {
                        App::$xve->session->addNotification('Failed to save', 'danger');
                    }
                } else {
                    App::$xve->session->addNotification('Definition already exists with that name.', 'danger');
                }
            } else {
                App::$xve->session->addNotification('Something is missing!', 'danger');
            }
        }
        //Render the page
        return $this->render('edit', [            
            'type'          => null,
            'model'         => null,
            'schema'        => MathDefinition::getJsonSchema(['xve' => App::$xve->getXveConfigurator() ]),
            'class'         => MathDefinition::class,
            'title'         => 'New'
        ]);
    }

    function actionEdit() {

        $type = HTTP::get('type', '');     

        /** @var XVEConfigurator */
        $xve = App::$xve->getXveConfigurator();
        
        /** @var StoredDefinition */
        $model = StoredDefinition::findByType($type)->one();

        if ($model === false) 
            throw new HttpException(HTTP::NOT_FOUND);

        if (HTTP::hasPost()) {
            $return = HTTP::post('return', '');
            $data = HTTP::post('root', null);
            $class = HTTP::post('class', null);
            $type = HTTP::post('type', $type);

            if ($data != null && !empty($class) && !empty($type)) {
                
                //Update the model
                $model->type = $type;
                $model->class = $class;
                $model->data = $data;

                //Save the model
                if ($model->save()) {
                    App::$xve->session->addNotification('Saved Succesfully', 'success');                    
                    if ($return == 'list')  return Response::redirect(['index' ]);
                    return Response::redirect(['edit', 'type' => $type ]);

                } else {
                    App::$xve->session->addNotification('Failed to save', 'danger');
                }
            } else {
                App::$xve->session->addNotification('Something is missing!', 'danger');
            }
        }
        
        //Create the definition and the schema
        $definition = $model->toXVE($xve);
        $schema = $definition::getJsonSchema([ 'xve' => $xve, 'additional_types' => $definition->getAllTypes() ]);
        $properties = $definition->getProperties();

        //We want to unset properties we dont have 
        foreach($properties as $name => $value) {
            if (!isset($schema->properties[$name]))
                unset($properties[$name]);
        }

        //$schema->definitions['types']->enum = array_merge($schema->definitions['types']->enum, $defobj->)
        

        //Create a blank version of the definition
        return $this->render('edit', [
            'type'          => $type,
            'model'         => $properties,
            'schema'        => $schema,
            'class'         => $definition->className(),
            'title'         => 'Edit'
        ]);
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