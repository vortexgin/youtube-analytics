<?php

namespace Vortexgin\YoutubeAnalyticsBundle\Manager;

use Widop\HttpAdapter\HttpAdapterInterface;
use Vortexgin\YoutubeAnalyticsBundle\Exception\YoutubeAnalyticsException;

/**
 * Google OAuth 2.0 Manager.
 *
 * @category Manager
 * @package  Vortexgin\YoutubeAnalyticsBundle\Manager
 * @author   Gin Vortex <vortexgin@gmail.com>
 * @license  http://opensource.org/licenses/gpl-license.php GPL
 * @link     https://apigeek.id
 */
class OAuthManager
{

    private $_configFile;

    private $_accessType = 'offline';

    private $_includeGrantedScopes = true;

    private $_scopes = [\Google_Service_YouTubeAnalytics::YT_ANALYTICS_READONLY];
    
    private $_callbackUrl;

    function __construct($configFile, $callbackUrl)
    {
        $this->_configFile = $configFile;
        $this->_callbackUrl = $callbackUrl;
    }

    public function getClient()
    {
        $client = new \Google_Client();
        $client->setAuthConfig($this->_configFile);
        $client->setAccessType($this->_accessType);
        $client->setApprovalPrompt("force");
        $client->setIncludeGrantedScopes($this->_includeGrantedScopes);
        foreach ($this->_scopes as $scope) {
            $client->addScope($scope);
        }
        $client->setRedirectUri($this->_callbackUrl);

        return $client;
    }

    public function getAuthUrl()
    {
        return $this->getClient()->createAuthUrl();
    }
}