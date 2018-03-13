<?php declare(strict_types=1);

namespace Ellipse\Dispatcher;

use Ellipse\Dispatcher;
use Ellipse\DispatcherFactoryInterface;

class ComposableResolver implements DispatcherFactoryInterface
{
    /**
     * The delegate.
     *
     * @var \Ellipse\DispatcherFactoryInterface
     */
    private $delegate;

    /**
     * The middleware to wrap around the request handler.
     *
     * @var array
     */
    private $middleware;

    /**
     * Set up a composable resolver with the given delegate and middleware
     * queue.
     *
     * @param \Ellipse\DispatcherFactoryInterface   $delegate
     * @param array                                 $middleware
     */
    public function __construct(DispatcherFactoryInterface $delegate, array $middleware = [])
    {
        $this->delegate = $delegate;
        $this->middleware = $middleware;
    }

    /**
     * Return a new ComposableResolver using the delegate and the middleware
     * queue merged with the give one.
     *
     * @param array $middleware
     * @return \Ellipse\Dispatcher\ComposableResolver
     */
    public function with(array $middleware): ComposableResolver
    {
        $middleware = array_merge($this->middleware, $middleware);

        return new ComposableResolver($this->delegate, $middleware);
    }

    /**
     * Proxy the delegate with the given request handler and the middleware
     * queue merged with the given one.
     *
     * @param mixed $handler
     * @param array $middleware
     * @return \Ellipse\Dispatcher
     */
    public function __invoke($handler, array $middleware = []): Dispatcher
    {
        $middleware = array_merge($this->middleware, $middleware);

        return ($this->delegate)($handler, $middleware);
    }
}
