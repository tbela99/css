<?php

namespace TBela\CSS\Value;

/**
 * CSS function value
 * @package TBela\CSS\Value
 */
class CssSrcFormat extends CssFunction {

    protected static function validate($data): bool {

        return $data->name ?? null === 'format' && isset($data->arguments) && $data->arguments instanceof Set;
    }

    public function render(array $options = []): string {

        return $this->data->name.'("'. $this->data->arguments->render($options).'")';
    }

    public function getHash() {

        return $this->data->name.'("'. $this->data->arguments->getHash().'")';
    }
}
