<?php namespace app\components\mixer;

use kiss\models\BaseObject;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use kiss\Kiss;

class Mixer extends BaseObject {
    const USER_AGENT = 'KISS Client/1';

    /** @var \GuzzleHttp\Client guzzle client */
    public $guzzle;
    
    /** @var string client secret */
    public $clientSecret;
    
    /** @var string client id */
    public $clientId;

    /** @var string[] scopes */
    public $scopes;

    /** {@inheritdoc} */
    protected function init() {
        if ($this->guzzle == null) {
            $this->guzzle = new \GuzzleHttp\Client([
                'base_uri'  => 'https://mixer.com/api/v1/'
            ]);
            //$this->guzzle->setUserAgent(self::USER_AGENT);
        }
    }

    /** @return MixerShortCode short code */
    public function requestShortCode() {
        $payload = [
            'client_id' => $this->clientId,
            'scope'    => join(' ', $this->scopes)
        ];

        if (!empty($this->clientSecret)) 
            $payload['client_secret'] = $this->clientSecret;

 
        $response = $this->guzzle->request('POST', 'oauth/shortcode', [ 
            'headers'   => [
                'content-type' => 'application/json'
            ],
            'json'      => $payload,
        ]);


        $json = json_decode($response->getBody()->getContents(), true);
        $json['mixer'] = $this;
        return BaseObject::create(MixerShortCode::class, $json);
    }

    /** Creates a new oauth user with the access tokens */
    public function requestCurrentUser($tokens) {
        $response = $this->guzzle->request('GET', 'users/current', [ 
            'headers'   => [
                'content-type' => 'application/json',
                'Authorization' => "Bearer {$tokens['accessToken']}",
            ]
        ]);

        $json = json_decode($response->getBody()->getContents(), true);
        $json['mixer'] = $this;
        $json['tokens'] = $tokens;
        return BaseObject::create(MixerUser::class, $json);
    }

    public function debugGetUser() { 
        if (($tokens = Kiss::$app->session->get('mixer_tokens', null)) != null) {
            return $this->requestCurrentUser($tokens);
        }
        return null;
    }

}