<?php

namespace Vortexgin\YoutubeAnalyticsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

class DefaultController extends Controller
{

    public function oauthAction(Request $request)
    {
        try {
            /* @var $oauthManager \Vortexgin\YoutubeAnalyticsBundle\Manager\OAuthManager */
            $oauthManager = $this->container->get('vortexgin.youtube.analytics.oauth');

            $oauthUrl = $oauthManager->getAuthUrl();
        
            return new RedirectResponse($oauthUrl);
        } catch(\Exception $e) {
            $this->container->get('logger')->error(sprintf($e->getMessage()));
            return new JsonResponse(
                array(
                    'message' => 'Auth failed, Please try again later. '.$e->getMessage(),
                    'success' => false,
                    'timestamp' => new \DateTime()
                ), 412
            );
        }
    }

    public function oauthCallbackAction(Request $request)
    {
        try {
            $get = $request->query->all();

            if (!array_key_exists('code', $get) || empty($get['code'])) {
                return new JsonResponse(
                    array(
                        'message' => 'Access token not found',
                        'success' => false,
                        'timestamp' => new \DateTime()
                    ), 400
                );
            }
            
            /* @var $oauthManager \Vortexgin\YoutubeAnalyticsBundle\Manager\OAuthManager */
            $oauthManager = $this->container->get('vortexgin.youtube.analytics.oauth');

            $client = $oauthManager->getClient();
            if (!$client->authenticate($get['code'])) {
                return new JsonResponse(
                    array(
                        'message' => 'Forbidden',
                        'success' => false,
                        'timestamp' => new \DateTime()
                    ), 413
                );
            } 

            file_put_contents($this->container->getParameter('vortexgin.youtube.analytics.auth_file'), json_encode($client->getAccessToken()));
            
            return new JsonResponse($client->getAccessToken(), 200);
        } catch(\Exception $e) {
            $this->container->get('logger')->error(sprintf($e->getMessage()));
            return new JsonResponse(
                array(
                    'message' => 'Auth failed, Please try again later. '.$e->getMessage(),
                    'success' => false,
                    'timestamp' => new \DateTime()
                ), 412
            );
        }
    }

    /**
     * @ApiDoc(
     *      section="Tools",
     *      resource="Youtube Analytics",
     *      description="Youtube channel performace",
     *      parameters={
     *          {"name"="startDate", "dataType"="string", "required"=true, "description"="start of data period. Format YYYY-mm-dd"},
     *          {"name"="endDate",   "dataType"="string", "required"=true, "description"="end of data period. Format YYYY-mm-dd"},
     *      },
     *      statusCodes={
     *          200="Returned when successful",
     *          400="Bad request",
     *          500="System error",
     *      }
     * )
     */
    public function ytChannelPerformanceAction(Request $request)
    {
        try{
            $get = $request->query->all();

            /* @var $queryManager \Vortexgin\YoutubeAnalyticsBundle\Manager\QueryManager */
            $queryManager = $this->container->get('vortexgin.youtube.analytics.query');
            /* @var $serviceManager \Vortexgin\YoutubeAnalyticsBundle\Manager\ServiceManager */
            $serviceManager = $this->container->get('vortexgin.youtube.analytics.service');

            if ((array_key_exists('startDate', $get) && !empty($get['startDate'])) && (array_key_exists('endDate', $get) && !empty($get['endDate']))) {
                $queryManager->setStartDate(\DateTime::createFromFormat('Y-m-d', date("Y-m-d", strtotime($get['startDate']))));
                $queryManager->setEndDate(\DateTime::createFromFormat('Y-m-d', date("Y-m-d", strtotime($get['endDate']))));
            } else {
                $queryManager->setStartDate(\DateTime::createFromFormat('Y-m-d', date("Y-m-d", strtotime('monday last week'))));
                $queryManager->setEndDate(\DateTime::createFromFormat('Y-m-d', date("Y-m-d", strtotime('sunday last week'))));
            }

            //$queryManager->setFilters(array('isCurated==1'));
            $queryManager->setDimensions(array('day'));
            $queryManager->setMetrics(array('views', 'comments', 'likes', 'dislikes', 'estimatedMinutesWatched', 'subscribersGained', 'subscribersLost'));
            $queryManager->setMaxResults(10);
            $response = $serviceManager->query($queryManager, $this->container->getParameter('vortexgin.youtube.analytics.access_token'));
            $lastWeek = array(
                'views' => 0, 
                'comments' => 0, 
                'likes' => 0, 
                'dislikes' => 0, 
                'estimatedMinutesWatched' => 0, 
                'subscribersGained' => 0, 
                'subscribersLost' => 0, 
            );
            $dayOfWeek = array(
                'views' => array(), 
                'comments' => array(), 
                'likes' => array(), 
                'dislikes' => array(), 
                'estimatedMinutesWatched' => array(), 
                'subscribersGained' => array(), 
                'subscribersLost' => array(), 
            );
            if (!empty($response->getRows()) && is_array($response->getRows())) {
                foreach ($response->getRows() as $row) {
                    $lastWeek['views'] += $row[1];
                    $lastWeek['comments'] += $row[2];
                    $lastWeek['likes'] += $row[3];
                    $lastWeek['dislikes'] += $row[4];
                    $lastWeek['estimatedMinutesWatched'] += $row[5];
                    $lastWeek['subscribersGained'] += $row[6];
                    $lastWeek['subscribersLost'] += $row[7];
                    $dayOfWeek['views'][$row[0]] = array(
                        'total' => $row[1], 
                        'accumulate' => 0, 
                    );
                    $dayOfWeek['comments'][$row[0]] = array(
                        'total' => $row[2], 
                        'accumulate' => 0, 
                    );
                    $dayOfWeek['likes'][$row[0]] = array(
                        'total' => $row[3], 
                        'accumulate' => 0, 
                    );
                    $dayOfWeek['dislikes'][$row[0]] = array(
                        'total' => $row[4], 
                        'accumulate' => 0, 
                    );
                    $dayOfWeek['estimatedMinutesWatched'][$row[0]] = array(
                        'total' => $row[5], 
                        'accumulate' => 0, 
                    );
                    $dayOfWeek['subscribersGained'][$row[0]] = array(
                        'total' => $row[6], 
                        'accumulate' => 0, 
                    );
                    $dayOfWeek['subscribersLost'][$row[0]] = array(
                        'total' => $row[7], 
                        'accumulate' => 0, 
                    );
                }
            }
            
            if ((array_key_exists('startDate', $get) && !empty($get['startDate'])) && (array_key_exists('endDate', $get) && !empty($get['endDate']))) {
                $queryManager->setStartDate(\DateTime::createFromFormat('Y-m-d', date("Y-m-d", strtotime('-1 week', strtotime($get['startDate'])))));
                $queryManager->setEndDate(\DateTime::createFromFormat('Y-m-d', date("Y-m-d", strtotime('-1 week', strtotime($get['endDate'])))));
            } else {
                $queryManager->setStartDate(\DateTime::createFromFormat('Y-m-d', date("Y-m-d", strtotime('-1 week', strtotime('monday last week')))));
                $queryManager->setEndDate(\DateTime::createFromFormat('Y-m-d', date("Y-m-d", strtotime('-1 week', strtotime('sunday last week')))));
            }

            //$queryManager->setFilters(array('isCurated==1'));
            $queryManager->setDimensions(array('day'));
            $queryManager->setMetrics(array('views', 'comments', 'likes', 'dislikes', 'estimatedMinutesWatched', 'subscribersGained', 'subscribersLost'));
            $queryManager->setMaxResults(10);
            $response = $serviceManager->query($queryManager, $this->container->getParameter('vortexgin.youtube.analytics.access_token'));
            $last2Week = array(
                'views' => 0, 
                'comments' => 0, 
                'likes' => 0, 
                'dislikes' => 0, 
                'estimatedMinutesWatched' => 0, 
                'subscribersGained' => 0, 
                'subscribersLost' => 0, 
            );
            if (!empty($response->getRows()) && is_array($response->getRows())) {
                $index = 0;
                foreach ($response->getRows() as $row) {
                    $last2Week['views'] += $row[1];
                    $last2Week['comments'] += $row[2];
                    $last2Week['likes'] += $row[3];
                    $last2Week['dislikes'] += $row[4];
                    $last2Week['estimatedMinutesWatched'] += $row[5];
                    $last2Week['subscribersGained'] += $row[6];
                    $last2Week['subscribersLost'] += $row[7];

                    $index1 = 0;
                    foreach ($dayOfWeek['views'] as $key=>$row1) {
                        if ($index == $index1) {
                            $row[1] = !empty($row[1])?$row[1]:1;
                            $dayOfWeek['views'][$key]['total'] = !empty($dayOfWeek['views'][$key]['total'])?$dayOfWeek['views'][$key]['total']:1;

                            $dayOfWeek['views'][$key]['accumulate'] = @(($dayOfWeek['views'][$key]['total']-$row[1])/$row[1])*100;
                            break;
                        }
                        $index1++;
                    }
                    $index1 = 0;
                    foreach ($dayOfWeek['comments'] as $key=>$row1) {
                        if ($index == $index1) {
                            $row[2] = !empty($row[2])?$row[2]:1;
                            $dayOfWeek['comments'][$key]['total'] = !empty($dayOfWeek['comments'][$key]['total'])?$dayOfWeek['comments'][$key]['total']:1;

                            $dayOfWeek['comments'][$key]['accumulate'] = @(($dayOfWeek['comments'][$key]['total']-$row[2])/$row[2])*100;
                            break;
                        }
                        $index1++;
                    }
                    $index1 = 0;
                    foreach ($dayOfWeek['likes'] as $key=>$row1) {
                        if ($index == $index1) {
                            $row[3] = !empty($row[3])?$row[3]:1;
                            $dayOfWeek['likes'][$key]['total'] = !empty($dayOfWeek['likes'][$key]['total'])?$dayOfWeek['likes'][$key]['total']:1;

                            $dayOfWeek['likes'][$key]['accumulate'] = @(($dayOfWeek['likes'][$key]['total']-$row[3])/$row[3])*100;
                            break;
                        }
                        $index1++;
                    }
                    $index1 = 0;
                    foreach ($dayOfWeek['dislikes'] as $key=>$row1) {
                        if ($index == $index1) {
                            $row[4] = !empty($row[4])?$row[4]:1;
                            $dayOfWeek['dislikes'][$key]['total'] = !empty($dayOfWeek['dislikes'][$key]['total'])?$dayOfWeek['dislikes'][$key]['total']:1;

                            $dayOfWeek['dislikes'][$key]['accumulate'] = @(($dayOfWeek['dislikes'][$key]['total']-$row[4])/$row[4])*100;
                            break;
                        }
                        $index1++;
                    }
                    $index1 = 0;
                    foreach ($dayOfWeek['estimatedMinutesWatched'] as $key=>$row1) {
                        if ($index == $index1) {
                            $row[5] = !empty($row[5])?$row[5]:1;
                            $dayOfWeek['estimatedMinutesWatched'][$key]['total'] = !empty($dayOfWeek['estimatedMinutesWatched'][$key]['total'])?$dayOfWeek['estimatedMinutesWatched'][$key]['total']:1;

                            $dayOfWeek['estimatedMinutesWatched'][$key]['accumulate'] = @(($dayOfWeek['estimatedMinutesWatched'][$key]['total']-$row[5])/$row[5])*100;
                            break;
                        }
                        $index1++;
                    }
                    $index1 = 0;
                    foreach ($dayOfWeek['subscribersGained'] as $key=>$row1) {
                        if ($index == $index1) {
                            $row[6] = !empty($row[6])?$row[6]:1;
                            $dayOfWeek['subscribersGained'][$key]['total'] = !empty($dayOfWeek['subscribersGained'][$key]['total'])?$dayOfWeek['subscribersGained'][$key]['total']:1;

                            $dayOfWeek['subscribersGained'][$key]['accumulate'] = @(($dayOfWeek['subscribersGained'][$key]['total']-$row[6])/$row[6])*100;
                            break;
                        }
                        $index1++;
                    }
                    $index1 = 0;
                    foreach ($dayOfWeek['subscribersLost'] as $key=>$row1) {
                        if ($index == $index1) {
                            $row[7] = !empty($row[7])?$row[7]:1;
                            $dayOfWeek['subscribersLost'][$key]['total'] = !empty($dayOfWeek['subscribersLost'][$key]['total'])?$dayOfWeek['subscribersLost'][$key]['total']:1;

                            $dayOfWeek['subscribersLost'][$key]['accumulate'] = @(($dayOfWeek['subscribersLost'][$key]['total']-$row[7])/$row[7])*100;
                            break;
                        }
                        $index1++;
                    }
                    $index++;
                }
            }

            return new JsonResponse(
                array(
                    'data' => $dayOfWeek, 
                    'count' => array(
                        'views' => array(
                            'total' => $lastWeek['views'], 
                            'accumulate' => (($lastWeek['views']-$last2Week['views'])/$last2Week['views'])*100,
                        ),
                        'comments' => array(
                            'total' => $lastWeek['comments'], 
                            'accumulate' => (($lastWeek['comments']-$last2Week['comments'])/$last2Week['comments'])*100,
                        ),
                        'likes' => array(
                            'total' => $lastWeek['likes'], 
                            'accumulate' => (($lastWeek['likes']-$last2Week['likes'])/$last2Week['likes'])*100,
                        ),
                        'dislikes' => array(
                            'total' => $lastWeek['dislikes'], 
                            'accumulate' => (($lastWeek['dislikes']-$last2Week['dislikes'])/$last2Week['dislikes'])*100,
                        ),
                        'estimatedMinutesWatched' => array(
                            'total' => $lastWeek['estimatedMinutesWatched'], 
                            'accumulate' => (($lastWeek['estimatedMinutesWatched']-$last2Week['estimatedMinutesWatched'])/$last2Week['estimatedMinutesWatched'])*100,
                        ),
                        'subscribersGained' => array(
                            'total' => $lastWeek['subscribersGained'], 
                            'accumulate' => (($lastWeek['subscribersGained']-$last2Week['subscribersGained'])/$last2Week['subscribersGained'])*100,
                        ),
                        'subscribersLost' => array(
                            'total' => $lastWeek['subscribersLost'], 
                            'accumulate' => (($lastWeek['subscribersLost']-$last2Week['subscribersLost'])/$last2Week['subscribersLost'])*100,
                        ),
                    ), 
                ), 200
            );
        }catch(\Exception $e){
            $this->container->get('logger')->error(sprintf($e->getMessage()));
            return new JsonResponse(
                array(
                    'message' => 'Getting report failed, Please try again later. '.$e->getMessage(),
                    'success' => false,
                    'timestamp' => new \DateTime()
                ), 412
            );
        }
    }

    /**
     * @ApiDoc(
     *      section="Tools",
     *      resource="Youtube Analytics",
     *      description="Youtube video performace",
     *      parameters={
     *          {"name"="startDate", "dataType"="string", "required"=true,  "description"="start of data period. Format YYYY-mm-dd"},
     *          {"name"="endDate",   "dataType"="string", "required"=true,  "description"="end of data period. Format YYYY-mm-dd"},
     *          {"name"="sort",      "dataType"="string", "required"=false, "description"="Sort field. Filter: estimatedMinutesWatched, views, likes, subscribersGained"},
     *      },
     *      statusCodes={
     *          200="Returned when successful",
     *          400="Bad request",
     *          500="System error",
     *      }
     * )
     */
    public function ytVideoPerformanceAction(Request $request)
    {
        try{
            $get = $request->query->all();

            /* @var $queryManager \Vortexgin\YoutubeAnalyticsBundle\Manager\QueryManager */
            $queryManager = $this->container->get('vortexgin.youtube.analytics.query');
            /* @var $serviceManager \Vortexgin\YoutubeAnalyticsBundle\Manager\ServiceManager */
            $serviceManager = $this->container->get('vortexgin.youtube.analytics.service');

            if ((array_key_exists('startDate', $get) && !empty($get['startDate'])) && (array_key_exists('endDate', $get) && !empty($get['endDate']))) {
                $queryManager->setStartDate(\DateTime::createFromFormat('Y-m-d', date("Y-m-d", strtotime($get['startDate']))));
                $queryManager->setEndDate(\DateTime::createFromFormat('Y-m-d', date("Y-m-d", strtotime($get['endDate']))));
            } else {
                $queryManager->setStartDate(\DateTime::createFromFormat('Y-m-d', date("Y-m-d", strtotime('monday last week'))));
                $queryManager->setEndDate(\DateTime::createFromFormat('Y-m-d', date("Y-m-d", strtotime('sunday last week'))));
            }

            $sort = (array_key_exists('sort', $get) && !empty($get['sort']))?$get['sort']:'-estimatedMinutesWatched';

            //$queryManager->setFilters(array('isCurated==1'));
            $queryManager->setDimensions(array('video'));
            $queryManager->setMetrics(array('estimatedMinutesWatched', 'views', 'likes', 'subscribersGained'));
            $queryManager->setMaxResults(10);
            $queryManager->setSorts(array($sort));
            $response = $serviceManager->query($queryManager, $this->container->getParameter('vortexgin.youtube.analytics.access_token'));
            $lastWeek = array(
                'estimatedMinutesWatched' => 0, 
                'views' => 0, 
                'likes' => 0, 
                'subscribersGained' => 0, 
            );
            $dayOfWeek = array(
                'estimatedMinutesWatched' => array(), 
                'views' => array(), 
                'likes' => array(), 
                'subscribersGained' => array(), 
            );
            if (!empty($response->getRows()) && is_array($response->getRows())) {
                foreach ($response->getRows() as $row) {
                    $lastWeek['estimatedMinutesWatched'] += $row[1];
                    $lastWeek['views'] += $row[2];
                    $lastWeek['likes'] += $row[3];
                    $lastWeek['subscribersGained'] += $row[4];
                    $dayOfWeek['estimatedMinutesWatched'][$row[0]] = array(
                        'total' => $row[1], 
                        'accumulate' => 0, 
                    );
                    $dayOfWeek['views'][$row[0]] = array(
                        'total' => $row[2], 
                        'accumulate' => 0, 
                    );
                    $dayOfWeek['likes'][$row[0]] = array(
                        'total' => $row[3], 
                        'accumulate' => 0, 
                    );
                    $dayOfWeek['subscribersGained'][$row[0]] = array(
                        'total' => $row[4], 
                        'accumulate' => 0, 
                    );
                }
            }
            
            if ((array_key_exists('startDate', $get) && !empty($get['startDate'])) && (array_key_exists('endDate', $get) && !empty($get['endDate']))) {
                $queryManager->setStartDate(\DateTime::createFromFormat('Y-m-d', date("Y-m-d", strtotime('-1 week', strtotime($get['startDate'])))));
                $queryManager->setEndDate(\DateTime::createFromFormat('Y-m-d', date("Y-m-d", strtotime('-1 week', strtotime($get['endDate'])))));
            } else {
                $queryManager->setStartDate(\DateTime::createFromFormat('Y-m-d', date("Y-m-d", strtotime('-1 week', strtotime('monday last week')))));
                $queryManager->setEndDate(\DateTime::createFromFormat('Y-m-d', date("Y-m-d", strtotime('-1 week', strtotime('sunday last week')))));
            }

            //$queryManager->setFilters(array('isCurated==1'));
            $queryManager->setDimensions(array('video'));
            $queryManager->setMetrics(array('estimatedMinutesWatched', 'views', 'likes', 'subscribersGained'));
            $queryManager->setMaxResults(10);
            $queryManager->setSorts(array($sort));
            $response = $serviceManager->query($queryManager, $this->container->getParameter('vortexgin.youtube.analytics.access_token'));
            $last2Week = array(
                'estimatedMinutesWatched' => 0, 
                'views' => 0, 
                'likes' => 0, 
                'subscribersGained' => 0, 
            );
            if (!empty($response->getRows()) && is_array($response->getRows())) {
                foreach ($response->getRows() as $row) {
                    $last2Week['estimatedMinutesWatched'] += $row[1];
                    $last2Week['views'] += $row[2];
                    $last2Week['likes'] += $row[3];
                    $last2Week['subscribersGained'] += $row[4];

                    foreach ($dayOfWeek['estimatedMinutesWatched'] as $key=>$row1) {
                        if ($key == $row[0]) {
                            $row[1] = !empty($row[1])?$row[1]:1;
                            $dayOfWeek['estimatedMinutesWatched'][$key]['total'] = !empty($dayOfWeek['estimatedMinutesWatched'][$key]['total'])?$dayOfWeek['estimatedMinutesWatched'][$key]['total']:1;

                            $dayOfWeek['estimatedMinutesWatched'][$key]['accumulate'] = @(($dayOfWeek['estimatedMinutesWatched'][$key]['total']-$row[1])/$row[1])*100;
                            break;
                        }
                    }
                    foreach ($dayOfWeek['views'] as $key=>$row1) {
                        if ($key == $row[0]) {
                            $row[2] = !empty($row[2])?$row[2]:1;
                            $dayOfWeek['views'][$key]['total'] = !empty($dayOfWeek['views'][$key]['total'])?$dayOfWeek['views'][$key]['total']:1;

                            $dayOfWeek['views'][$key]['accumulate'] = @(($dayOfWeek['views'][$key]['total']-$row[2])/$row[2])*100;
                            break;
                        }
                    }
                    foreach ($dayOfWeek['likes'] as $key=>$row1) {
                        if ($key == $row[0]) {
                            $row[3] = !empty($row[3])?$row[3]:1;
                            $dayOfWeek['likes'][$key]['total'] = !empty($dayOfWeek['likes'][$key]['total'])?$dayOfWeek['likes'][$key]['total']:1;

                            $dayOfWeek['likes'][$key]['accumulate'] = @(($dayOfWeek['likes'][$key]['total']-$row[3])/$row[3])*100;
                            break;
                        }
                    }
                    foreach ($dayOfWeek['subscribersGained'] as $key=>$row1) {
                        if ($key == $row[0]) {
                            $row[4] = !empty($row[4])?$row[4]:1;
                            $dayOfWeek['subscribersGained'][$key]['total'] = !empty($dayOfWeek['subscribersGained'][$key]['total'])?$dayOfWeek['subscribersGained'][$key]['total']:1;

                            $dayOfWeek['subscribersGained'][$key]['accumulate'] = @(($dayOfWeek['subscribersGained'][$key]['total']-$row[4])/$row[4])*100;
                            break;
                        }
                    }
                }
            }

            return new JsonResponse(
                array(
                    'data' => $dayOfWeek, 
                    'count' => array(
                        'estimatedMinutesWatched' => array(
                            'total' => $lastWeek['estimatedMinutesWatched'], 
                            'accumulate' => (($lastWeek['estimatedMinutesWatched']-$last2Week['estimatedMinutesWatched'])/$last2Week['estimatedMinutesWatched'])*100,
                        ),
                        'views' => array(
                            'total' => $lastWeek['views'], 
                            'accumulate' => (($lastWeek['views']-$last2Week['views'])/$last2Week['views'])*100,
                        ),
                        'likes' => array(
                            'total' => $lastWeek['likes'], 
                            'accumulate' => (($lastWeek['likes']-$last2Week['likes'])/$last2Week['likes'])*100,
                        ),
                        'subscribersGained' => array(
                            'total' => $lastWeek['subscribersGained'], 
                            'accumulate' => (($lastWeek['subscribersGained']-$last2Week['subscribersGained'])/$last2Week['subscribersGained'])*100,
                        ),
                    ), 
                ), 200
            );
        }catch(\Exception $e){
            var_dump($e->getTraceAsString());
            $this->container->get('logger')->error(sprintf($e->getMessage()));
            return new JsonResponse(
                array(
                    'message' => 'Getting report failed, Please try again later. '.$e->getMessage(),
                    'success' => false,
                    'timestamp' => new \DateTime()
                ), 412
            );
        }
    }
}