<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license.
 */

namespace Belazor\RateLimit\Options;

use Zend\Stdlib\AbstractOptions;

/**
 * RateLimitOptions
 *
 * @license MIT
 * @author Fillip Hannisdal <fillip@dragonbyte-tech.com>
 */
class RateLimitOptions extends AbstractOptions
{
    /**
     * @var string
     */
    protected $storage = null;

    /**
     * @var string
     */
    protected $storageConfig = null;

    /**
     * @var array
     */
    protected $routes = [];

    /**
     * @var array
     */
    protected $route_specific_limits = [];

    /**
     * @var int
     */
    protected $limit = 0;

    /**
     * @var int
     */
    protected $period = 0;


    /**
     * Constructor
     *
     * @param  array|Traversable|null $options
     */
    public function __construct($options = null)
    {
        parent::__construct($options);
    }

    /**
     * @return string
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * @param string $storage
     */
    public function setStorage($storage)
    {
        $this->storage = $storage;
    }

    /**
     * @return string
     */
    public function getStorageConfig()
    {
        return $this->storageConfig;
    }

    /**
     * @param string $storageConfig
     */
    public function setStorageConfig($storageConfig)
    {
        $this->storageConfig = $storageConfig;
    }

    /**
     * @return array
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * @param array $routes
     */
    public function setRoutes($routes)
    {
        $this->routes = $routes;
    }

    /**
     * @param array $routeSpecificLimits
     */
    public function setRouteSpecificLimits($routeSpecificLimits)
    {
        $this->route_specific_limits = $routeSpecificLimits;
    }

    /**
     * @return array
     */
    public function getRouteSpecificLimits()
    {
        return $this->route_specific_limits;
    }

    /**
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @param int $limit
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
    }

    /**
     * @return int
     */
    public function getPeriod()
    {
        return $this->period;
    }

    /**
     * @param int $period
     */
    public function setPeriod($period)
    {
        $this->period = $period;
    }

    /**
     * @param string $route
     */
    public function setRouteSpecificLimitsFromRoute($route)
    {
        $routeSpecificLimits = $this->getRouteSpecificLimits();
        if (!$route OR !isset($routeSpecificLimits[$route]))
        {
            return;
        }

        $options = $routeSpecificLimits[$route];

        if (!is_array($options) AND !$options instanceof Traversable)
        {
            return;
        }

        foreach ($options as $key => $value) {
            $this->__set($key, $value);
        }
    }
}
