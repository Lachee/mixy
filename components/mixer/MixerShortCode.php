<?php namespace app\components\mixer;

use kiss\exception\ArgumentException;
use kiss\models\BaseObject;

class MixerShortCode extends BaseObject {

    public const STATUS_OK = 1;
    public const STATUS_WAITING = 0;
    public const STATUS_FORBIDDEN = -1;
    public const STATUS_INVALID = -2;

    protected $mixer;
    protected $code;
    protected $expires_in;
    protected $handle;

    public function getMixer() { return $this->mixer; }
    public function getCode() { return $this->code; }
    public function getHandle() { return $this->handle; }

    /** @return int checks if the code has been used */
    public function check() {
        if ($this->mixer == null) throw new ArgumentException();
        $response = $this->mixer->guzzle->request('GET', 'oauth/shortcode/check/' . $this->handle);
        switch($response->getStatusCode()) {
            case 200:
                return self::STATUS_OK;
            case 204:
                return self::STATUS_WAITING;
            case 403:
                return self::STATUS_FORBIDDEN;
                
            default:
            case 404:
                return self::STATUS_INVALID;
        }
    }

    /** @return string URL to redirect the user too */
    public function getRedirect() { 
        if (empty($this->code)) return "https://mixer.com/go";
        return "https://mixer.com/go?code={$this->code}";
    }
}