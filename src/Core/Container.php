<?php

declare(strict_types=1);

namespace App\Core;

final class Container
{
    private array $services = [];
    private array $instances = [];

    public function set(string $id, callable|object $concrete): void
    {
        $this->services[$id] = $concrete;
        unset($this->instances[$id]);
    }

    public function singleton(string $id, callable|object $concrete): void
    {
        $this->services[$id] = $concrete;
    }

    public function has(string $id): bool
    {
        return isset($this->services[$id]) || isset($this->instances[$id]);
    }

    public function get(string $id): object
    {
        if (isset($this->instances[$id])) {
            return $this->instances[$id];
        }

        if (!isset($this->services[$id])) {
            if (class_exists($id)) {
                return $this->resolveClass($id);
            }
            throw new \RuntimeException("Service or class '{$id}' not found in container.");
        }

        $service = $this->services[$id];

        if (is_callable($service)) {
            $instance = $service($this);
            $this->instances[$id] = $instance;
            return $instance;
        }

        if (is_object($service)) {
            $this->instances[$id] = $service;
            return $service;
        }

        throw new \RuntimeException("Invalid service definition for '{$id}'.");
    }

    private function resolveClass(string $className): object
    {
        $reflection = new \ReflectionClass($className);

        if (!$reflection->isInstantiable()) {
            throw new \RuntimeException("Class '{$className}' is not instantiable.");
        }

        $constructor = $reflection->getConstructor();
        if ($constructor === null) {
            return new $className();
        }

        $parameters = $constructor->getParameters();
        $dependencies = [];

        foreach ($parameters as $parameter) {
            $type = $parameter->getType();

            if ($type instanceof \ReflectionNamedType && !$type->isBuiltin()) {
                $dependencyClass = $type->getName();
                $dependencies[] = $this->get($dependencyClass);
            } elseif ($parameter->isDefaultValueAvailable()) {
                $dependencies[] = $parameter->getDefaultValue();
            } else {
                throw new \RuntimeException(
                    "Cannot resolve parameter '{$parameter->getName()}' of class '{$className}'."
                );
            }
        }

        return $reflection->newInstanceArgs($dependencies);
    }
}
