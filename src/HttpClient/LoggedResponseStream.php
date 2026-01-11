<?php

namespace Pantono\Logger\HttpClient;

use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\ResponseStreamInterface;
use Symfony\Contracts\HttpClient\ChunkInterface;

class LoggedResponseStream implements ResponseStreamInterface
{
    private ResponseStreamInterface $stream;
    private \SplObjectStorage $mapping;
    private \Iterator $iterator;

    public function __construct(ResponseStreamInterface $stream, \SplObjectStorage $mapping)
    {
        $this->stream = $stream;
        $this->mapping = $mapping;
        $this->iterator = $this->createIterator($stream);
    }

    private function createIterator(ResponseStreamInterface $stream): \Generator
    {
        foreach ($stream as $response => $chunk) {
            if ($this->mapping->contains($response)) {
                yield $this->mapping[$response] => $chunk;
            } else {
                yield $response => $chunk;
            }
        }
    }

    public function key(): ResponseInterface
    {
        return $this->iterator->key();
    }

    public function current(): ChunkInterface
    {
        return $this->iterator->current();
    }

    public function next(): void
    {
        $this->iterator->next();
    }

    public function rewind(): void
    {
        // Generators cannot be rewound, but Symfony's ResponseStreamInterface doesn't usually require it
        // as it's often used in a single foreach.
        // However, if the inner stream is an Iterator, we might need to handle it.
        // Symfony's ResponseStream is usually a generator or a ResponseStream object.
    }

    public function valid(): bool
    {
        return $this->iterator->valid();
    }
}
