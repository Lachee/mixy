<?php namespace kiss\models;

use DateTime;
use kiss\exception\ExpiredOauthException;
use kiss\exception\MissingOauthException;
use kiss\Kiss;
use GuzzleHttp\Client as Guzzle;

class OAuthContainer extends BaseObject {
    
    /** @var string namespace of the oauth tokens */
    private const REDIS_NAMESPACE = 'oauth';

    /** @var string Application the token is from */
    public $application = 'GenericOauthProvider';
    
    /** @var string Identifier of the owner */
    public $identity;

    /** @var int Duration (in seconds) that refresh tokens last for. */
    public $refreshDuration = 365 * 24 * 60 * 60;

    /** @var int Duration (in seconds) that access tokens last for. */
    public $accessDuration  = 6 * 60  * 60;

    protected   $refreshToken;
    protected   $expiresAt;
    protected   $scopes;
    
    /** @var string the refresh token. */
    public function getRefreshToken() { return $this->refreshToken; }

    /** @var DateTime the expiry date */
    public function getExpiry() { return $this->expiresAt; }

    /** @var string[] the requested scopes */
    public function getScopes() { return $this->scopes; }

    /** Checks the cahce for the current access token. If none is available, then a ExpiredOauthException will be thrown.
     * @throws ExpiredOauthException thrown when there is no access token. Its recommended to call [[refresh]] if th is occures.
     *  @return string the current access token.  */
    public function getAccessToken() {        
        $keyAccess  = self::REDIS_NAMESPACE . ":{$this->identity}:{$this->application}:access";
        $accessToken = Kiss::$app->redis()->get($keyAccess);
        if (empty($accessToken)) throw new ExpiredOauthException('Access token was not found in the cache');
        return $accessToken;
    }

    /** Sets the current tokens.
     * token paramater expects the following:
     * [
     *      refreshToken
     *      accessToken
     *      expiresAt
     *      scopes
     * ]
     * @return OAuthContainer this.
     */
    public function setTokens($tokens) {
        $keyRefresh = self::REDIS_NAMESPACE . ":{$this->identity}:{$this->application}:meta";
        $keyAccess  = self::REDIS_NAMESPACE . ":{$this->identity}:{$this->application}:access";

        $expiresAt = isset($tokens['expires_at']) ? strtotime($tokens['expires_at']) 
                                                    : ($tokens['expires_in'] + time());

        $set = [
            'refreshToken'  => $tokens['refresh_token'],
            'expiresAt'     => $expiresAt,
        ];

        if (!empty($tokens['scopes'])) 
            $set['scopes'] = join(' ', $tokens['scopes'] ?? []);
            
        //Set the values
        Kiss::$app->redis()->set($keyAccess, $tokens['access_token']);
        Kiss::$app->redis()->hmset($keyRefresh, $set);

        //TTL the values
        $accessExpiry = $expiresAt - time();
        Kiss::$app->redis()->expire($keyAccess, $accessExpiry);

        $refreshExpiry = $this->refreshDuration;
        Kiss::$app->redis()->expire($keyRefresh, $refreshExpiry);

        //Load the values back again
        $this->loadRedis();
        return $this;
    }

    /** {@inheritdoc} */
    protected function init() {
        parent::init();
        
        //try to load teh refresh from the cache
        try { $this->loadRedis(); } catch(MissingOauthException $moe) {}
    }

    /** Loads the metadata from the cache.
     * @throws MissingOauthException thrown when there is no valid meta data.
     * @return OAuthContainer this.
     */
    public function loadRedis() {        

        //Check the cache
        $keyRefresh = self::REDIS_NAMESPACE . ":{$this->identity}:{$this->application}:meta";
        $meta = Kiss::$app->redis()->hgetall($keyRefresh);
        if ($meta == null || empty($meta['refreshToken'])) throw new MissingOauthException('There is no available refresh token');

        $this->refreshToken = $meta['refreshToken'];
        $this->expiresAt    = $meta['expiresAt'] ?? -1;
        $this->scopes       = explode(' ', $meta['scopes']);
        return $this;
    }

    /** Creates a refresh request using the cached refreshToken.
     * @param Guzzle $guzzle the guzzle client to make the request. If null, a new client will be used and the $endpoint has to be absolute.
     * @param string[] $options collection of additional options. All that are listed here are required
     * [ 
     *      string      client_id       The client ID of the oAuth2 application
     *      string?     client_secret   The client secret. Don't supply if you dont got.
     *      string      redirect_uri    Where to redirect back too
     *      string      endpoint        The endpoint. By default it is oauth/token. Dont supply if you dont need to change it. This has to be absolute if no guzzle client is provided.
     * ]
     *  
     * If the $guzzle is null, then this must be absolute.
     * @throws MissingOauthException thrown when there is no valid meta data.
     * @return OAuthContainer the updated container.
     */
    public function refresh($guzzle, $options = []) {
        
        //Update the cache and prepare the token
        $this->loadRedis();
        $refreshToken = $this->getRefreshToken();

        //Prepare the payload
        $body = [
            'client_id'     => $options['client_id'],
            'redirect_uri'  => $options['redirect_uri'],
            'refresh_token' => $refreshToken,
            'grant_type'    => 'refresh_token',
        ];

        //set the client secret
        if (isset($options['client_secret']))
            $body['client_secret'] = $options['client_secret'];

        //prepare t he endpoint and the guzzle
        $endpoint = $options['endpoint'] ?? 'oauth/token';
        if ($guzzle == null) $guzzle = new Guzzle();

        //Creat ethe resposne
        $response = $guzzle->request('POST', $endpoint, [
            'json'      => $body,
            'headers'   => [
                'content-type' => 'application/json'
            ],
        ]);

        //Decode and set the tokens.
        $json = json_decode($response->getBody()->getContents(), true);
        return $this->setTokens($json);
    }
}