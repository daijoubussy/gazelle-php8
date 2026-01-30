<?php

declare(strict_types=1);

namespace Gazelle\Core\Container;

use Gazelle\Core\Config\Configuration;
use Psr\Container\ContainerInterface;

/**
 * Dependency Injection Container
 *
 * PSR-11 compliant container with auto-wiring support.
 */
final class Container implements ContainerInterface
{
    /** @var array<string, mixed> */
    private array $instances = [];

    /** @var array<string, callable> */
    private array $factories = [];

    /** @var array<string, string> */
    private array $aliases = [];

    /** @var array<ServiceProvider> */
    private array $providers = [];

    public function __construct(
        private readonly Configuration $config
    ) {
        $this->instances[Configuration::class] = $config;
        $this->instances[ContainerInterface::class] = $this;
        $this->instances[self::class] = $this;
    }

    /**
     * @template T of object
     * @param class-string<T>|string $id
     * @return T|mixed
     */
    public function get(string $id): mixed
    {
        $id = $this->aliases[$id] ?? $id;

        if (isset($this->instances[$id])) {
            return $this->instances[$id];
        }

        if (isset($this->factories[$id])) {
            $this->instances[$id] = ($this->factories[$id])($this);
            return $this->instances[$id];
        }

        if (class_exists($id)) {
            return $this->resolve($id);
        }

        throw new NotFoundException("Service not found: {$id}");
    }

    public function has(string $id): bool
    {
        $id = $this->aliases[$id] ?? $id;

        return isset($this->instances[$id])
            || isset($this->factories[$id])
            || class_exists($id);
    }

    /**
     * Bind a factory to the container
     */
    public function bind(string $id, callable $factory): void
    {
        $this->factories[$id] = $factory;
    }

    /**
     * Bind a singleton instance
     */
    public function singleton(string $id, callable|object $concrete): void
    {
        if (is_callable($concrete)) {
            $this->factories[$id] = function (Container $c) use ($id, $concrete) {
                $this->instances[$id] = $concrete($c);
                unset($this->factories[$id]);
                return $this->instances[$id];
            };
        } else {
            $this->instances[$id] = $concrete;
        }
    }

    /**
     * Create an alias for a service
     */
    public function alias(string $alias, string $id): void
    {
        $this->aliases[$alias] = $id;
    }

    /**
     * Register a service provider
     */
    public function register(ServiceProvider $provider): void
    {
        $provider->register($this);
        $this->providers[] = $provider;
    }

    /**
     * Get configuration value
     */
    public function config(string $key, mixed $default = null): mixed
    {
        return $this->config->get($key, $default);
    }

    /**
     * Auto-resolve a class with its dependencies
     *
     * @template T of object
     * @param class-string<T> $class
     * @return T
     */
    private function resolve(string $class): object
    {
        $reflector = new \ReflectionClass($class);

        if (!$reflector->isInstantiable()) {
            throw new ContainerException("Class {$class} is not instantiable");
        }

        $constructor = $reflector->getConstructor();

        if ($constructor === null) {
            $instance = new $class();
            $this->instances[$class] = $instance;
            return $instance;
        }

        $parameters = $constructor->getParameters();
        $dependencies = $this->resolveDependencies($parameters);

        $instance = $reflector->newInstanceArgs($dependencies);
        $this->instances[$class] = $instance;

        return $instance;
    }

    /**
     * Resolve constructor dependencies
     *
     * @param \ReflectionParameter[] $parameters
     * @return array<mixed>
     */
    private function resolveDependencies(array $parameters): array
    {
        $dependencies = [];

        foreach ($parameters as $parameter) {
            $type = $parameter->getType();

            if ($type === null) {
                if ($parameter->isDefaultValueAvailable()) {
                    $dependencies[] = $parameter->getDefaultValue();
                    continue;
                }
                throw new ContainerException(
                    "Cannot resolve parameter: {$parameter->getName()}"
                );
            }

            if ($type instanceof \ReflectionNamedType && !$type->isBuiltin()) {
                $dependencies[] = $this->get($type->getName());
            } elseif ($parameter->isDefaultValueAvailable()) {
                $dependencies[] = $parameter->getDefaultValue();
            } else {
                throw new ContainerException(
                    "Cannot resolve parameter: {$parameter->getName()}"
                );
            }
        }

        return $dependencies;
    }

    /**
     * Terminate all providers
     */
    public function terminate(): void
    {
        foreach ($this->providers as $provider) {
            if (method_exists($provider, 'terminate')) {
                $provider->terminate();
            }
        }
    }
}
