<?php
namespace Simplex\Tests;


use Simplex\EventsListener\StringResponseListener;
use Simplex\Framework;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\HttpKernel\EventListener\RouterListener;
use Symfony\Component\Routing;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;

class FrameworkTest extends TestCase
{
    public function testNotFoundHandling(): void
    {
        $framework = $this->getFramework();

        $response = $framework->handle(new Request());

        $this->assertEquals(404, $response->getStatusCode());
    }

    private function getFramework(RouteCollection $collection = null): Framework
    {
        $requestStack = new RequestStack();
        $routes = $collection ?? new RouteCollection();
        $context = new RequestContext();
        $matcher = new Routing\Matcher\UrlMatcher( $routes, $context);
        $controllerResolver = new ControllerResolver();
        $argumentResolver = new ArgumentResolver();
        $dispatcher = new EventDispatcher();
        $dispatcher->addSubscriber(new RouterListener($matcher, $requestStack));
        $dispatcher->addSubscriber(new StringResponseListener());
        return new Framework($dispatcher, $controllerResolver, $requestStack, $argumentResolver);
    }


    public function testControllerResponse(): void
    {

        $framework = $this->getFramework($this->getRouteCollection());
        $response = $framework->handle($this->generateRequestForLeapYear());

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('Yep, this is a leap year!', $response->getContent());
        //verifing that works' with not leap year too!
        $response = $framework->handle($this->generateRequestForNotLeapYear());
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('Nope, this is not a leap year.', $response->getContent());
    }

    private function getRouteCollection(): RouteCollection
    {
        $routes = new Routing\RouteCollection();
        $routes->add('leap_year', new Routing\Route('/is_leap_year/{year}', [
            'year' => null,
            '_controller' => 'Calendar\Controller\LeapYearController::index'
        ]));

        return $routes;
    }

    private function generateRequestForLeapYear(): Request
    {
       return Request::create('/is_leap_year/2024' , 'GET');
    }

    private function generateRequestForNotLeapYear(): Request
    {
        return Request::create('/is_leap_year/2023' , 'GET');
    }
}