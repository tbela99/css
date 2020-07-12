<?php

namespace TBela\CSS\Parser;

use JsonSerializable;

/**
 * Class Location
 * @package TBela\CSS\Parser
 *
 * @property Position $start
 * @property Position $end
 */

class SourceLocation implements JsonSerializable {

    use AccessTrait;

    protected $start;
    protected $end;

    public function __construct(Position $start, Position $end) {

        $this->start = $start;
        $this->end = $end;
    }

    public static function getInstance($location)
    {

        return new static(Position::getInstance($location->start), Position::getInstance($location->end));
    }

    public function getStart() {

        return $this->start;
    }

    public function getEnd() {

        return $this->end;
    }

    public function setStart(Position $start) {

        $this->start = $start;
        return $this;
    }

    public function setEnd(Position $end) {

        $this->end = $end;
        return $this;
    }
}