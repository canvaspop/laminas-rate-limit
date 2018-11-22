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

namespace Belazor\RateLimit\Mvc;

use Belazor\RateLimit\Exception\TooManyRequestsHttpException;
use Belazor\RateLimit\Service\RateLimitService;
use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use Zend\Mvc\MvcEvent;
use Zend\Http\Response as HttpResponse;
use Zend\Http\Request as HttpRequest;
use Zend\Mvc\Router\RouteMatch;

/**
 * RateLimitRequestListener
 *
 * @license MIT
 * @author  Fillip Hannisdal <fillip@dragonbyte-tech.com>
 */
class RateLimitRequestListener extends AbstractListenerAggregate
{
    /**
     * @var RateLimitService
     */
    private $rateLimitService;

    /**
     * @param RateLimitService $rateLimitService
     */
    public function __construct(RateLimitService $rateLimitService)
    {
        $this->rateLimitService = $rateLimitService;
    }

    /**
     * Attach to an event manager
     *
     * @param  EventManagerInterface $events
     * @param  int                   $priority
     *
     * @return void
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_ROUTE, [$this, 'onRoute']);
    }

    /**
     * Listen to the "route" event and attempt to intercept the request
     *
     * If no matches are returned, triggers "dispatch.error" in order to
     * create a 404 response.
     *
     * Seeds the event with the route match on completion.
     *
     * @param  MvcEvent $event
     *
     * @return null|RouteMatch|HttpResponse
     */
    public function onRoute(MvcEvent $event)
    {
        $request    = $event->getRequest();
        $routeMatch = $event->getRouter()->match($request);

        if (!$request instanceof HttpRequest) {
            return;
        }

        if (!method_exists($request, 'getHeaders')) {
            // Extra safety
            return;
        }

        if (!$routeMatch instanceof RouteMatch || !$this->hasRoute($routeMatch)) {
            return;
        }

        if (!$request->isPost()) {
            return;
        }

        try {
            // Check if we're within the limit
            $this->rateLimitService->rateLimitHandler($routeMatch->getMatchedRouteName());

            // Update the response
            $response = $event->getResponse();

            // Add the headers to the response
            $this->ensureHeaders($response);

            // Set the response back
            $event->setResponse($response);

        } catch (TooManyRequestsHttpException $exception) {
            $response = new HttpResponse();
            $response->setStatusCode(429)
                ->setReasonPhrase($exception->getMessage());

            // Add the headers so clients will know when they can try again
            $this->ensureHeaders($response);

            // And we're done here
            return $response;
        }
    }

    /**
     * @param HttpResponse $response
     *
     * @return \Zend\Http\Headers
     */
    public function ensureHeaders(HttpResponse $response)
    {
        $headers = $response->getHeaders();

        $headers->addHeaderLine('X-RateLimit-Limit', $this->rateLimitService->getLimit());
        $headers->addHeaderLine('X-RateLimit-Remaining', $this->rateLimitService->getRemainingCalls());
        $headers->addHeaderLine('X-RateLimit-Reset', $this->rateLimitService->getTimeToReset());

        return $headers;
    }

    /**
     * @param RouteMatch $routeMatch
     *
     * @return bool
     */
    private function hasRoute(RouteMatch $routeMatch)
    {
        $routes       = $this->rateLimitService->getRoutes();
        $currentRoute = $routeMatch->getMatchedRouteName();

        if (!$routes) {
            return false;
        }

        foreach ($routes as $route) {
            if (fnmatch($route, $currentRoute)) {
                return true;
            }
        }

        return false;
    }
}
