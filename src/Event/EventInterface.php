<?php

namespace TBela\CSS\Event;

interface EventInterface {

    public function on(string $event, callable $callable): static;

    public function off(string $event, callable $callable): static;

    public function emit(string $event, ...$args): array;
}