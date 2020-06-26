<?php

namespace TBela\CSS\Event;

use Closure;

trait EventTrait {

    /**
     * @var callable[]
     */
    protected array $events = [];

    public function on(string $event, callable $callable) {

        $this->events[strtolower($event)][] = $callable;
        return $this;
    }

    public function off(string $event, callable $callable) {

        $event = strtolower($event);

        if (isset($this->events[$event])) {

            foreach ($this->events[$event] as $key => $value) {

                if ($value === $callable) {

                    array_splice($this->events[$event], $key, 1);
                    break;
                }
            }
        }

        return $this;
    }

    public function emit(string $event, ...$args): array {

        $result = [];
        $event = strtolower($event);

        if (!isset($this->events[$event])) {

            return $result;
        }

        foreach ($this->events[$event] as $callable) {

            $result[] = call_user_func_array($callable, $args);
        }

        return $result;
    }
}