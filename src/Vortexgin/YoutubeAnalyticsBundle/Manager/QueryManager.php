<?php

namespace Vortexgin\YoutubeAnalyticsBundle\Manager;

/**
 * Youtube Analytics Query.
 *
 * @category Manager
 * @package  Vortexgin\YoutubeAnalyticsBundle\Manager
 * @author   Gin Vortex <vortexgin@gmail.com>
 * @license  http://opensource.org/licenses/gpl-license.php GPL
 * @link     https://apigeek.id
 */
class QueryManager
{
    /** @const The Google analytics service URL. */
    const URL = 'https://youtubeanalytics.googleapis.com/v2/reports';

    /** @var string */
    protected $ids;

    /** @var \DateTime */
    protected $startDate;

    /** @var \DateTime */
    protected $endDate;

    /** @var array */
    protected $metrics;

    /** @var array */
    protected $dimensions;

    /** @var array */
    protected $sorts;

    /** @var array */
    protected $filters;

    /** @var string */
    protected $segment;

    /** @var integer */
    protected $startIndex;

    /** @var integer */
    protected $maxResults;

    /** @var boolean */
    protected $prettyPrint;

    /** @var string */
    protected $callback;

    /**
     * Creates a youtube analytics query.
     *
     * @param string $ids The youtube analytics query ids.
     */
    public function __construct($ids)
    {
        $this->setIds($ids);

        $this->metrics = array();
        $this->dimensions = array();
        $this->sorts = array();
        $this->filters = array();
        $this->startIndex = 1;
        $this->maxResults = 10000;
        $this->prettyPrint = false;
    }

    /**
     * Gets the youtube analytics query ids.
     *
     * @return string The youtube analytics query ids.
     */
    public function getIds()
    {
        return $this->ids;
    }

    /**
     * Sets the youtube analytics query ids.
     *
     * @param string $ids The youtube analytics query ids.
     *
     * @return self The query.
     */
    public function setIds($ids)
    {
        $this->ids = $ids;

        return $this;
    }

    /**
     * Checks if the youtube analytics query has a start date.
     *
     * @return boolean TRUE if the youtube analytics query has a start date.
     */
    public function hasStartDate()
    {
        return $this->startDate !== null;
    }

    /**
     * Gets the youtube analytics query start date.
     *
     * @return \DateTime The youtube analytics query start date.
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * Sets the youtube analytics query start date.
     *
     * @param \DateTime $startDate The youtube analytics query start date.
     *
     * @return self The query.
     */
    public function setStartDate(\DateTime $startDate = null)
    {
        $this->startDate = $startDate;
        
        return $this;
    }

    /**
     * Checks if the youtube analytics query has an end date.
     *
     * @return boolean TRUE if the youtube analytics query has an ende date else FALSE.
     */
    public function hasEndDate()
    {
        return $this->endDate !== null;
    }

    /**
     * Gets the youtube analytics query end date.
     *
     * @return \DateTime The youtube analytics query end date.
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * Sets the youtube analytics query end date.
     *
     * @param \DateTime $endDate The youtube analytics query end date.
     *
     * @return self The query.
     */
    public function setEndDate(\DateTime $endDate = null)
    {
        $this->endDate = $endDate;
        
        return $this;
    }

    /**
     * Checks if the youtube analytics query has metrics.
     *
     * @return boolean TRUE if the youtube analytics query has metrics else FALSE.
     */
    public function hasMetrics()
    {
        return !empty($this->metrics);
    }

    /**
     * Gets the youtube analytics query metrics.
     *
     * @return array The youtube analytics query metrics.
     */
    public function getMetrics()
    {
        return $this->metrics;
    }

    /**
     * Sets the youtube analytics query metrics.
     *
     * @param array $metrics The youtube analytics query metrics.
     *
     * @return self The query.
     */
    public function setMetrics(array $metrics)
    {
        $this->metrics = array();

        foreach ($metrics as $metric) {
            $this->addMetric($metric);
        }
        
        return $this;
    }

    /**
     * Adds a the youtube analytics metric to the query.
     *
     * @param string $metric The youtube analytics metric to add.
     *
     * @return self The query.
     */
    public function addMetric($metric)
    {
        $this->metrics[] = $metric;
        
        return $this;
    }

    /**
     * Checks if the youtube analytics query has dimensions.
     *
     * @return boolean TRUE if the youtube analytics query has a dimensions else FALSE.
     */
    public function hasDimensions()
    {
        return !empty($this->dimensions);
    }

    /**
     * Gets the youtube analytics query dimensions.
     *
     * @return array The youtube analytics query dimensions.
     */
    public function getDimensions()
    {
        return $this->dimensions;
    }

    /**
     * Sets the youtube analytics query dimensions.
     *
     * @param array $dimensions The youtube analytics query dimensions.
     *
     * @return self The query.
     */
    public function setDimensions(array $dimensions)
    {
        $this->dimensions = array();

        foreach ($dimensions as $dimension) {
            $this->addDimension($dimension);
        }
        
        return $this;
    }

    /**
     * Adds a youtube analytics query dimension.
     *
     * @param string $dimension the youtube analytics dimension to add.
     *
     * @return self The query.
     */
    public function addDimension($dimension)
    {
        $this->dimensions[] = $dimension;
        
        return $this;
    }

    /**
     * Checks if the youtube analytics query is ordered.
     *
     * @return boolean TRUE if the youtube analytics query is ordered else FALSE.
     */
    public function hasSorts()
    {
        return !empty($this->sorts);
    }

    /**
     * Gets the youtube analytics query sorts.
     *
     * @return array The youtube analytics query sorts.
     */
    public function getSorts()
    {
        return $this->sorts;
    }

    /**
     * Sets the youtube analytics query sorts.
     *
     * @param array $sorts The youtube analytics query sorts.
     *
     * @return self The query.
     */
    public function setSorts(array $sorts)
    {
        $this->sorts = array();

        foreach ($sorts as $sort) {
            $this->addSort($sort);
        }
        
        return $this;
    }

    /**
     * Adds a youtube analytics query sort.
     *
     * @param string $sort A youtube analytics query sort to add.
     *
     * @return self The query.
     */
    public function addSort($sort)
    {
        $this->sorts[] = $sort;
        
        return $this;
    }

    /**
     * Checks if the youtube analytics query has filters.
     *
     * @return boolean TRUE if the youtube analytics query has filters else FALSE.
     */
    public function hasFilters()
    {
        return !empty($this->filters);
    }

    /**
     * Gets the youtube analytics query filters.
     *
     * @return array The youtube analytics query filters.
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * Sets the youtube analytics query filters.
     *
     * @param array $filters The youtube analytics query filters.
     *
     * @return self The query.
     */
    public function setFilters(array $filters)
    {
        $this->filters = array();

        foreach ($filters as $filter) {
            $this->addFilter($filter);
        }
        
        return $this;
    }

    /**
     * Adds the youtube analytics filter.
     *
     * @param string $filter the youtube analytics filter to add.
     *
     * @return self The query.
     */
    public function addFilter($filter)
    {
        $this->filters[] = $filter;
        
        return $this;
    }

    /**
     * Checks of the youtube analytics query has a segment.
     *
     * @return boolean TRUE if the youtube analytics query has a segment else FALSE.
     */
    public function hasSegment()
    {
        return $this->segment !== null;
    }

    /**
     * Gets the youtube analytics query segment.
     *
     * @return string The youtube analytics query segment.
     */
    public function getSegment()
    {
        return $this->segment;
    }

    /**
     * Sets the youtube analytics query segment.
     *
     * @param string $segment The youtube analytics query segment.
     *
     * @return self The query.
     */
    public function setSegment($segment)
    {
        $this->segment = $segment;
        
        return $this;
    }

    /**
     * Gets the youtube analytics query start index.
     *
     * @return integer The youtube analytics query start index.
     */
    public function getStartIndex()
    {
        return $this->startIndex;
    }

    /**
     * Sets the youtube analytics query start index.
     *
     * @param integer $startIndex The youtube analytics start index.
     *
     * @return self The query.
     */
    public function setStartIndex($startIndex)
    {
        $this->startIndex = $startIndex;
        
        return $this;
    }

    /**
     * Gets the youtube analytics query max result count.
     *
     * @return integer The youtube analytics query max result count.
     */
    public function getMaxResults()
    {
        return $this->maxResults;
    }

    /**
     * Sets the youtube analytics query max result count.
     *
     * @param integer $maxResults The youtube analytics query max result count.
     *
     * @return self The query.
     */
    public function setMaxResults($maxResults)
    {
        $this->maxResults = $maxResults;
        
        return $this;
    }

    /**
     * Gets the youtube analytics query prettyPrint option.
     *
     * @return boolean The youtube analytics query prettyPrint option.
     */
    public function getPrettyPrint()
    {
        return $this->prettyPrint;
    }

    /**
     * Sets the youtube analytics query prettyPrint option.
     *
     * @param boolean $prettyPrint The youtube analytics query pretty print option.
     *
     * @return self The query.
     */
    public function setPrettyPrint($prettyPrint)
    {
        $this->prettyPrint = $prettyPrint;
        
        return $this;
    }

    /**
     * Checks the youtube analytics query for a callback.
     *
     * @return boolean TRUE if the youtube analytics query has a callback else FALSE.
     */
    public function hasCallback()
    {
        return !empty($this->callback);
    }

    /**
     * Gets the youtube analytics query callback.
     *
     * @return string The youtube analytics query callback.
     */
    public function getCallback()
    {
        return $this->callback;
    }

    /**
     * Sets the youtube analytics query callback.
     *
     * @param string The youtube analytics query callback.
     *
     * @return self The query.
     */
    public function setCallback($callback)
    {
        $this->callback = $callback;
        
        return $this;
    }

    /**
     * Builds the query.
     *
     * @param string $accessToken The access token used to build the query.
     *
     * @return string The builded query.
     */
    public function build($accessToken)
    {
        $query = array(
            'dimensions'   => implode(',', $this->getDimensions()),
            'endDate'      => $this->getEndDate()->format('Y-m-d'),
            'ids'          => $this->getIds(),
            'maxResults'  => $this->getMaxResults(),
            'metrics'      => implode(',', $this->getMetrics()),
            'startDate'    => $this->getStartDate()->format('Y-m-d'),
            'access_token' => $accessToken,
            'start-index'  => $this->getStartIndex(),
        );

        if ($this->hasSegment()) {
            $query['segment'] = $this->getSegment();
        }

        if ($this->hasFilters()) {
            $query['filters'] = implode(',', $this->getFilters());
        }

        if ($this->hasSorts()) {
            $query['sort'] = implode(',', $this->getSorts());
        }

        if ($this->getPrettyPrint()) {
            $query['prettyPrint'] = 'true';
        }

        if ($this->hasCallback()) {
            $query['callback'] = $this->getCallback();
        }

        return sprintf('%s?%s', self::URL, http_build_query($query));
    }
}
