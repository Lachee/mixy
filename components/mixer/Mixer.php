<?php namespace app\components\mixer;

use kiss\models\BaseObject;
use kiss\exception\ExpiredOauthException;
use kiss\helpers\HTML;
use kiss\models\OAuthContainer;
use Mixy;

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
        return BaseObject::new(MixerShortCode::class, $json);
    }

    /** Gets a user that owns the access tokens
     * @param OAuthContainer|string $oauth The oAuth Container, or the accessToken.
     * @return MixerUser|null the user that owns the Access Token
     */
    public function getOwner($oauth) {
    
        $accessToken = $oauth;
    
        //If the accessToken is actually a container, we need to do some pre-processing.
        if ($oauth instanceof OAuthContainer) {
            /** @var OAuthContainer */
            $container = $oauth;

            try  {
                //Try to get the access token
                $accessToken = $container->getAccessToken();
            } 
            catch(ExpiredOauthException $expiredException) 
            {
                //Refresh the token
                $accessToken = $container->refresh($this->guzzle, [
                    'client_id'     => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'redirect_uri'  => HTML::href('/auth'),
                ])->getAccessToken();
            }
        }

        $response = $this->guzzle->request('GET', 'users/current', [ 
            'headers'   => [
                'content-type' => 'application/json',
                'Authorization' => "Bearer {$accessToken}",
            ]
        ]);

        $json = json_decode($response->getBody()->getContents(), true);
        $json['mixer'] = $this;
        $json['accessToken'] = $accessToken;
        
        return BaseObject::new(MixerUser::class, $json);
    }

}