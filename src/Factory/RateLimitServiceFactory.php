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

namespace Belazor\RateLimit\Factory;

use Zend\Cache\Storage\Adapter\Memcached;
use Zend\Cache\Storage\Adapter\MemcachedOptions;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Belazor\RateLimit\Service\RateLimitService;
use Belazor\RateLimit\Options\RateLimitOptions;
use RuntimeException;

/**
 * RateLimitServiceFactory
 *
 * @license MIT
 * @author  Fillip Hannisdal <fillip@dragonbyte-tech.com>
 */
class RateLimitServiceFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     * @return RateLimitService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /* @var RateLimitOptions $rateLimitOptions */
        $rateLimitOptions = $serviceLocator->get(RateLimitOptions::class);

        $storage = $rateLimitOptions->getStorage();

        if ($storage === 'memcached') {
            $config           = $serviceLocator->get('Config');
            $cacheOptions = $config['rate_limit']['storage_config'];
            $cacheOptions['ttl'] = $config['rate_limit']['period'];
            $storage = new Memcached(new MemcachedOptions($cacheOptions));
        } else if (!is_string($storage) || !$serviceLocator->has($storage)) {
            throw new RuntimeException('Unable to load storage.');
        } else {
            $storage = $serviceLocator->get($storage);
        }

        return new RateLimitService($storage, $rateLimitOptions);
    }
}
