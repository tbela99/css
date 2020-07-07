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

    protected Position $start;
    protected Position $end;

    public function __construct(Position $start, Position $end) {

        $this->start = $start;
        $this->end = $end;
    }

    public static function getInstance($location): SourceLocation
    {

        return new static(Position::getInstance($location->start), Position::getInstance($location->end));
    }

    public function getStart(): Position {

        return $this->start;
    }

    public function getEnd(): Position {

        return $this->end;
    }

    public function setStart(Position $start): SourceLocation {

        $this->start = $start;
        return $this;
    }

    public function setEnd(Position $end): SourceLocation {

        $this->end = $end;
        return $this;
    }
}