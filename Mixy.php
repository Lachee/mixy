<?php

use app\models\User;
use kiss\Kiss;

/**
 * @property Mixy $app
 */
class Mixy extends Kiss {
    
    /** @var User the currently signed in user */
    private $user;

    /** @var User  gets the currently signed in user */
    public function getUser() { return $this->user; }

    protected function init() {
        parent::init();

        /** @var User */
        $this->user = User::findBySession()->one();
    }


} 