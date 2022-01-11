<?php

namespace TBela\CSS\Event;

interface EventInterface {

    public function on(string $event, callable $callable);

    public function off(string $event, callable $callable);

    public function emit(string $event, ...$args): array;
}