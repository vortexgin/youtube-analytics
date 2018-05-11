<?php

namespace Vortexgin\YoutubeAnalyticsBundle\Manager;

use Widop\HttpAdapter\HttpAdapterInterface;
use Vortexgin\YoutubeAnalyticsBundle\Exception\YoutubeAnalyticsException;

/**
 * Youtube analytics client.
 *
 * @category Manager
 * @package  Vortexgin\YoutubeAnalyticsBundle\Manager
 * @author   Gin Vortex <vortexgin@gmail.com>
 * @license  http://opensource.org/licenses/gpl-license.php GPL
 * @link     https://apigeek.id
 */
class ClientManager
{
    /** @const The google OAuth scope. */
    const SCOPE = 'https://www.googleapis.com/auth/yt-analytics.readonly';

    /** @var string */
    protected $clientId;

    /** @var string */
    protected $privateKeyFile;

    /** @var \Widop\HttpAdapterBundle\Model\HttpAdapterInterface */
    protected $httpAdapter;

    /** @var string */
    protected $url;

    /** @var string */
    protected $accessToken;

    /**
     * Creates a client.
     *
     * @param string                                              $clientId       The client ID.
     * @param string                                              $privateKeyFile The absolute private key file path.
     * @param \Widop\HttpAdapterBundle\Model\HttpAdapterInterface $httpAdapter    The http adapter.
     * @param string                                              $url            The google service url.
     */
    public function __construct(
        $clientId,
        $privateKeyFile,
        HttpAdapterInterface $httpAdapter,
        $url = 'https://accounts.google.com/o/oauth2/token'
    ) {
        $this->setClientId($clientId);
        $this->setPrivateKeyFile($privateKeyFile);
        $this->setHttpAdapter($httpAdapter);
        $this->setUrl($url);
    }

    /**
     * Gets the client ID.
     *
     * @return string The client ID.
     */
    public function getClientId()
    {
        return $this->clientId;
    }

    /**
     * Sets the client ID.
     *
     * @param string $clientId The client ID.
     *
     * @return self The client.
     */
    public function setClientId($clientId)
    {
        $this->clientId = $clientId;

        return $this;
    }

    /**
     * Gets the absolute private key file path.
     *
     * @return string The absolute private key file path.
     */
    public function getPrivateKeyFile()
    {
        return $this->privateKeyFile;
    }

    /**
     * Sets the absolute private key file path.
     *
     * @param string $privateKeyFile The absolute private key file path.
     *
     * @throws \Vortexgin\YoutubeAnalyticsBundle\Exception\YoutubeAnalyticsException If the private key file does not exist.
     *
     * @return self The client.
     */
    public function setPrivateKeyFile($privateKeyFile)
    {
        if (!file_exists($privateKeyFile)) {
            throw YoutubeAnalyticsException::invalidPrivateKeyFile($privateKeyFile);
        }

        $this->privateKeyFile = $privateKeyFile;

        return $this;
    }

    /**
     * Gets the http adapter.
     *
     * @return \Widop\HttpAdapterBundle\Model\HttpAdapterInterface The http adapter.
     */
    public function getHttpAdapter()
    {
        return $this->httpAdapter;
    }

    /**
     * Sets the http adapter.
     *
     * @param \Widop\HttpAdapterBundle\Model\HttpAdapterInterface $httpAdapter The http adapter.
     *
     * @return self The client.
     */
    public function setHttpAdapter(HttpAdapterInterface $httpAdapter)
    {
        $this->httpAdapter = $httpAdapter;

        return $this;
    }

    /**
     * Gets the google service url.
     *
     * @return string The google service url.
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Sets the google service url.
     *
     * @param string $url The google service url.
     *
     * @return self The client.
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Gets the google OAuth access token.
     *
     * @throws \Vortexgin\YoutubeAnalyticsBundle\Exception\YoutubeAnalyticsException If the access token can not be retrieved.
     *
     * @return string The access token.
     */
    public function getAccessToken()
    {
        if ($this->accessToken === null) {
            $headers = array('Content-Type' => 'application/x-www-form-urlencoded');
            $content = array(
                'grant_type'     => 'assertion',
                'assertion_type' => 'http://oauth.net/grant_type/jwt/1.0/bearer',
                'assertion'      => $this->generateJsonWebToken(),
            );

            $response = json_decode($this->httpAdapter->postContent($this->url, $headers, $content)->getBody());

            if (isset($response->error)) {
                throw YoutubeAnalyticsException::invalidAccessToken($response->error);
            }

            $this->accessToken = $response->access_token;
        }

        return $this->accessToken;
    }

    /**
     * Sets the access token.
     *
     * @param string $accessToken The access token.
     *
     * @return self The client.
     */
    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    /**
     * Generates the JWT in order to get the access token.
     *
     * @return string The Json Web Token (JWT).
     */
    protected function generateJsonWebToken()
    {
        $exp = new \DateTime('+1 hours');
        $iat = new \DateTime();

        $jwtHeader = base64_encode(json_encode(array('alg' => 'RS256', 'typ' => 'JWT')));

        $jwtClaimSet = base64_encode(
            json_encode(
                array(
                    'iss'   => $this->clientId,
                    'scope' => self::SCOPE,
                    'aud'   => $this->url,
                    'exp'   => $exp->getTimestamp(),
                    'iat'   => $iat->getTimestamp(),
                )
            )
        );

        $jwtSignature = base64_encode($this->generateSignature($jwtHeader.'.'.$jwtClaimSet));

        return sprintf('%s.%s.%s', $jwtHeader, $jwtClaimSet, $jwtSignature);
    }

    /**
     * Generates the JWT signature according to the private key file and the JWT content.
     *
     * @param string $jsonWebToken The JWT content.
     *
     * @throws \Vortexgin\YoutubeAnalyticsBundle\Exception\YoutubeAnalyticsException If an error occured when generating the signature.
     *
     * @return string The JWT signature.
     */
    protected function generateSignature($jsonWebToken)
    {
        if (!function_exists('openssl_x509_read')) {
            throw YoutubeAnalyticsException::invalidOpenSslExtension();
        }

        $certificate = file_get_contents($this->privateKeyFile);

        $certificates = array();
        if (!openssl_pkcs12_read($certificate, $certificates, 'notasecret')) {
            throw YoutubeAnalyticsException::invalidPKCS12File();
        }

        if (!isset($certificates['pkey']) || !$certificates['pkey']) {
            throw YoutubeAnalyticsException::invalidPKCS12Format();
        }

        $ressource = openssl_pkey_get_private($certificates['pkey']);

        if (!$ressource) {
            throw YoutubeAnalyticsException::invalidPKCS12PKey();
        }

        $signature = null;
        if (!openssl_sign($jsonWebToken, $signature, $ressource, 'sha256')) {
            throw YoutubeAnalyticsException::invalidPKCS12Signature();
        }

        openssl_pkey_free($ressource);

        return $signature;
    }
}