<?php

/**
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Labrador\Router\Resolver;

use Labrador\Router\HandlerResolver;

class ResolverChain implements HandlerResolver {

    /**
     * @property HandlerResolver[]
     */
    private $resolvers = [];

    function resolve($handler) {
        foreach ($this->resolvers as $resolver) {
            $cb = $resolver->resolve($handler);
            if ($cb) {
                return $cb;
            }
        }

        return false;
    }

    function add(HandlerResolver $resolver) {
        $this->resolvers[] = $resolver;
        return $this;
    }

    function remove(HandlerResolver $resolver) {
        foreach($this->resolvers as $index => $stored) {
            if ($resolver === $stored) {
                unset($this->resolvers[$index]);
            }
        }

        return $this;
    }

}
