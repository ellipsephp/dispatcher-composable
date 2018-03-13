<?php

use function Eloquent\Phony\Kahlan\mock;

use Ellipse\Dispatcher;
use Ellipse\DispatcherFactoryInterface;
use Ellipse\Dispatcher\ComposableResolver;

describe('ComposableResolver', function () {

    beforeEach(function () {

        $this->delegate = mock(DispatcherFactoryInterface::class);

    });

    it('should implement DispatcherFactoryInterface', function () {

        $test = new ComposableResolver($this->delegate->get());

        expect($test)->toBeAnInstanceOf(DispatcherFactoryInterface::class);

    });

    describe('->with()', function () {

        context('when the middleware queue is empty', function () {

            it ('should return a new ComposableResolver using the delegate and the given middleware queue', function () {

                $middleware = ['middleware1', 'middleware2'];

                $resolver1 = new ComposableResolver($this->delegate->get());

                $test = $resolver1->with($middleware);

                $resolver2 = new ComposableResolver($this->delegate->get(), $middleware);

                expect($test)->toEqual($resolver2);

            });

        });

        context('when the middleware queue is not empty', function () {

            it ('should return a new ComposableResolver using the delegate and the middleware queue merged with the given one', function () {

                $middleware1 = ['middleware1', 'middleware2'];
                $middleware2 = ['middleware3', 'middleware4'];
                $middleware3 = ['middleware1', 'middleware2', 'middleware3', 'middleware4'];

                $resolver1 = new ComposableResolver($this->delegate->get(), $middleware1);

                $test = $resolver1->with($middleware2);

                $resolver2 = new ComposableResolver($this->delegate->get(), $middleware3);

                expect($test)->toEqual($resolver2);

            });

        });

    });

    describe('->__invoke()', function () {

        beforeEach(function () {

            $this->dispatcher = mock(Dispatcher::class)->get();

        });

        context('when no middleware queue is given', function () {

            it('should proxy the delegate with the given request handler and the middleware queue', function () {

                $middleware = ['middleware1', 'middleware2'];

                $resolver = new ComposableResolver($this->delegate->get(), $middleware);

                $this->delegate->__invoke->with('handler', $middleware)->returns($this->dispatcher);

                $test = $resolver('handler');

                expect($test)->toBe($this->dispatcher);

            });

        });

        context('when an middleware queue is given', function () {

            it('should proxy the delegate with the given request handler and the middleware queue merged with the given one', function () {

                $middleware1 = ['middleware1', 'middleware2'];
                $middleware2 = ['middleware3', 'middleware4'];
                $middleware3 = ['middleware1', 'middleware2', 'middleware3', 'middleware4'];

                $resolver = new ComposableResolver($this->delegate->get(), $middleware1);

                $this->delegate->__invoke->with('handler', $middleware3)->returns($this->dispatcher);

                $test = $resolver('handler', $middleware2);

                expect($test)->toBe($this->dispatcher);

            });

        });

    });

});
