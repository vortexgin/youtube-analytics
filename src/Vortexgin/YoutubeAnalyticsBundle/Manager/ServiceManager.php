<?php

namespace Vortexgin\YoutubeAnalyticsBundle\Manager;

use Widop\HttpAdapter\CurlHttpAdapter;
use Vortexgin\YoutubeAnalyticsBundle\Exception\YoutubeAnalyticsException;

/**
 * Youtube Analytics service.
 *
 * @category Manager
 * @package  Vortexgin\YoutubeAnalyticsBundle\Manager
 * @author   Gin Vortex <vortexgin@gmail.com>
 * @license  http://opensource.org/licenses/gpl-license.php GPL
 * @link     https://apigeek.id
 */
class ServiceManager
{
    /**
     * Queries the google analytics service.
     *
     * @param \Vortexgin\YoutubeAnalyticsBundle\Manager\QueryManager $query       The google analytics query.
     * @param string                                                 $accessToken The Access Token.
     *
     * @throws \Vortexgin\YoutubeAnalytics\Exception\YoutubeAnalyticsException If an error occured when querying the google analytics service.
     *
     * @return \Vortexgin\YoutubeAnalytics\Manager\ResponseManager The google analytics response.
     */
    public function query(QueryManager $query, $accessToken)
    {
        $httpAdapter = new CurlHttpAdapter();
        $uri = $query->build($accessToken);
        $content = $httpAdapter->getContent($uri)->getBody();
        $json = json_decode($content, true);

        if (!is_array($json) || isset($json['error'])) {
            throw YoutubeAnalyticsException::invalidQuery(isset($json['error']) ? $json['error']['message'] : 'Invalid json');
        }

        return new ResponseManager($json);
    }
}
