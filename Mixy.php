<?php

use app\models\User;
use kiss\Kiss;
use app\components\mixer\Mixer;

/**
 * @property Mixy $app
 * @property Mixer $mixer Mixer API instance
 */
class Mixy extends Kiss {
    
    /** @var User the currently signed in user */
    private $user;

    /** @var User  gets the currently signed in user */
    public function getUser() { return $this->user; }

    /** @return bool is a user currently logged in */
    public function loggedIn() { return $this->user != null; }

    protected function init() {
        parent::init();

        //Find the user and update their internal cache.
        /** @var User */
        $this->user = User::findBySession()->one();
        $i = $this->user;
    }


    
} 