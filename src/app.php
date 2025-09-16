<?php
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing;

function is_leap_year(?int $year = null): bool
{
    if (null === $year) {
        $year = (int)date('Y');
    }

    return 0 === $year % 400 || (0 === $year % 4 && 0 !== $year % 100);
}

$routes = new Routing\RouteCollection();
$routes->add('leap_year', new Routing\Route('/is_leap_year/{year}', [
    'year' => null,
    '_controller' => function (Request $request): Response {
        if (is_leap_year($request->attributes->get('year'))) {
            return new Response('Yep, this is a leap year!');
        }

        return new Response('Nope, this is not a leap year.');
    }
]));
$routes->add('bye', new Routing\Route('/bye', [
    '_controller' => function (Request $request): Response {
        return render_template($request);
    }
]));
$routes->add('hello', new Routing\Route('/hello/{name}', [
    'name' => 'world',
    '_controller' => function (Request $request): Response {
        // $foo will be available in the template
        $request->attributes->set('foo', 'testing a new value');
        $response = render_template($request);
        // change some header
        $response->headers->set('Content-Type', 'text/plain');

        return $response;
    }
]));

return $routes;
