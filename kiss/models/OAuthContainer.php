<?php namespace kiss\models;

use DateTime;
use kiss\exception\ExpiredOauthException;
use kiss\exception\MissingOauthException;
use kiss\Kiss;

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
     * @throws ExpiredOauthException thrown when there is no access token.
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

        //Set the values
        Kiss::$app->redis()->set($keyAccess, $tokens['accessToken']);
        Kiss::$app->redis()->hmset($keyRefresh, [
            'refreshToken'  => $tokens['refreshToken'],
            'expiresAt'     => $tokens['expiresAt'],
            'scopes'        => join(' ', $tokens['scopes'])
        ]);

        //TTL the values
        $accessExpiry = strtotime($tokens['expiresAt']) - time();
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
        $this->expiresAt    = new DateTime($meta['expiresAt']);
        $this->scopes       = explode(' ', $meta['scopes']);
        return $this;
    }

    //We cannot do this because we need to know the identity
    //public static function fromTokens($tokens) {
    //    return (new OAuthContainer())->setTokens($tokens);
    //}

}