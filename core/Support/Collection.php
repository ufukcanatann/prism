<?php

namespace Core\Support;

class Collection implements \ArrayAccess, \Countable, \IteratorAggregate
{
    protected array $items = [];

    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    public function all(): array
    {
        return $this->items;
    }

    public function get($key, $default = null)
    {
        return $this->items[$key] ?? $default;
    }

    public function set($key, $value): self
    {
        $this->items[$key] = $value;
        return $this;
    }

    public function has($key): bool
    {
        return isset($this->items[$key]);
    }

    public function remove($key): self
    {
        unset($this->items[$key]);
        return $this;
    }

    public function first()
    {
        return reset($this->items);
    }

    public function last()
    {
        return end($this->items);
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function offsetExists($offset): bool
    {
        return isset($this->items[$offset]);
    }

    public function offsetGet($offset): mixed
    {
        return $this->items[$offset];
    }

    public function offsetSet($offset, $value): void
    {
        if (is_null($offset)) {
            $this->items[] = $value;
        } else {
            $this->items[$offset] = $value;
        }
    }

    public function offsetUnset($offset): void
    {
        unset($this->items[$offset]);
    }

    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->items);
    }

    public function map(callable $callback): self
    {
        return new static(array_map($callback, $this->items));
    }

    public function filter(callable $callback): self
    {
        return new static(array_filter($this->items, $callback));
    }

    public function reduce(callable $callback, $initial = null)
    {
        return array_reduce($this->items, $callback, $initial);
    }

    public function each(callable $callback): self
    {
        foreach ($this->items as $key => $item) {
            $callback($item, $key);
        }
        return $this;
    }

    public function toArray(): array
    {
        return $this->items;
    }

    public function toJson(): string
    {
        return json_encode($this->items);
    }
}
