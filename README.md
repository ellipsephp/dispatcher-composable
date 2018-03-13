# Composable resolver

This package provides a composable factory decorator for objects implementing `Ellipse\DispatcherFactoryInterface` from [ellipse/dispatcher](https://github.com/ellipsephp/dispatcher) package.

**Require** php >= 7.0

**Installation** `composer require ellipse/dispatcher-composable`

**Run tests** `./vendor/bin/kahlan`

- [Composing a dispatcher](#composing-a-dispatcher)

## Composing a dispatcher

Sometimes a dispatcher needs to be composed at runtime according to certain conditions. When routing for example: a solution is needed to build a dispatcher using different middleware queues and request handlers according to the matched route.

For this purpose this package provides an `Ellipse\Dispatcher\ComposableResolver` class which takes an implementation of `DispatcherFactoryInterface` and a middleware queue as constructor parameters. Dispatchers produced by this resolver are wrapped inside this middleware queue. It also have a `->with()` method taking a middleware queue as parameter and returning a new `ComposableResolver` with those middleware added to the queue.

Let's have an example using [FastRoute](https://github.com/nikic/FastRoute):

```php
<?php

namespace App;

use FastRoute\RouteCollector;

use Ellipse\DispatcherFactory;
use Ellipse\Dispatcher\ComposableResolver;

// Create a new ComposableResolver.
$factory = new ComposableResolver(new DispatcherFactory);

// Create a new FastRoute route collector.
$r = new RouteCollector(...);

// Those middleware will be wrapped around all the dispatchers.
$factory = $factory->with([
    new SomeMiddleware1,
    new SomeMiddleware2,
]);

// The dispatcher matching the GET / route will use SomeMiddleware1, SomeMiddleware2 and RequestHandler1.
$r->get('/', $factory(new RequestHandler1));

// Let's have a first route group.
$r->group('/group1', function ($r) use ($factory) {

    // SomeMiddleware3 is specific to this route group.
    $factory = $factory->with([new SomeMiddleware3]);

    // The dispatcher matching the GET /group1/route1 route will use SomeMiddleware1, SomeMiddleware2, SomeMiddleware3 and RequestHandler2.
    $r->get('/route1', $factory(new RequestHandler2));

    // The dispatcher matching the GET /group1/route2 route will use SomeMiddleware1, SomeMiddleware2, SomeMiddleware3 and RequestHandler3.
    $r->get('/route2', $factory(new RequestHandler3));

});

// And a second route group.
$r->group('/group2', function ($r) use ($factory) {

    // SomeMiddleware4 is specific to this route group.
    $factory = $factory->with([new SomeMiddleware4]);

    // The dispatcher matching the GET /group2/route1 route will use SomeMiddleware1, SomeMiddleware2, SomeMiddleware4 and RequestHandler4.
    $r->get('/route1', $factory(new RequestHandler4));

    // Also middleware can be added on a per route basis.
    $r->get('/route2', $factory(new RequestHandler5, [
        new SomeMiddleware5,
    ]));

});
```

Of course, `ComposableResolver` can decorate any implementation of `DispatcherFactoryInterface`. For example the `CallableResolver` class from the [ellipse/dispatcher-callable](https://github.com/ellipsephp/dispatcher-callable) package:

```php
<?php

namespace App;

use FastRoute\RouteCollector;

use Ellipse\DispatcherFactory;
use Ellipse\Dispatcher\ComposableResolver;
use Ellipse\Dispatcher\CallableResolver;

// Create a new ComposableResolver resolving callables.
$factory = new ComposableResolver(
    new CallableResolver(
        new DispatcherFactory
    )
);

// Create a new FastRoute route collector.
$r = new RouteCollector(...);

// Callables can be used as Psr-15 middleware.
$factory = $factory->with([
    function ($request, $handler) {

        // This callable behave like a Psr-15 middleware.

    },
]);

// Callables can be used as request handlers too.
$r->get('/', $factory(function ($request) {

    // This callable behave like a Psr-15 request handler.

}))
```
