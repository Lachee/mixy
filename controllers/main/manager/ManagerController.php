<?php
namespace controllers\main\manager;

use App;
use core\controllers\Controller;
use helpers\HTTP;
use models\TestModel;

class ManagerController extends Controller {


    function actionIndex() {
        return $this->render('index', [
        ]);
    }

    function actionExample() {
        
        //Prepare the test model
        $testModel = new TestModel();

        if (HTTP::hasPost()) {
            $data = HTTP::post('root', null);
            $success = $testModel->load($data);
            if ($success) {

                //Success!
                App::$xve->session->addNotification('Loaded the model from the form', 'success');
            } else {

                //Failure!
                foreach($testModel->errors() as $error) {                    
                    App::$xve->session->addNotification($error, 'danger');
                }
            }
        } 

        return $this->render('example', [
            'model' => $testModel,
            'schema' => $testModel->getJsonSchema([ 'xve' => App::$xve->getXveConfigurator() ])
        ]);
    }

    
    function actionFontawesome() {
        \lachee\fontawesome\Updater::update();
    }
}