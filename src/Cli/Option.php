<?php

namespace TBela\CSS\Cli;

class Option
{
    const AUTO = 'auto';
    const BOOL = 'bool';
    const INT = 'int';
    const FLOAT = 'float';
    const STRING = 'string';
    const NUMBER = 'number';

    protected bool $isset = false;
    protected string $type;
    protected bool $multiple;
    protected bool $required;
    protected mixed $defaultValue;
    protected array $options;

    protected mixed $value = null;

    public function __construct(string $type = Option::AUTO, bool $multiple = true, bool $required = false, $defaultValue = null, array $options = [])
    {

        if (!in_array($type, ['bool', 'boolean', 'int', 'integer', 'number', 'float', 'string', 'auto'])) {

            throw new \UnexpectedValueException(sprintf("unsupported type: '%s'", $type));
        }

        $this->type = $type == 'boolean' ? 'bool' : $type;
        $this->multiple = $multiple;
        $this->required = $required;
        $this->defaultValue = $defaultValue;
        $this->options = $options;

        if ($required) {

            $this->value = [];
        }
    }

    public function getType(): string
    {

        return $this->type;
    }

    /**
     * @param $value
     * @return $this
     */
    public function addValue($value): static
    {

        switch ($this->type) {

            case 'bool':

                $value = match (strtolower($value)) {
                    '0', 'n', 'no', 'off' => false,
                    default => (bool)$value
                };
                break;

            case 'int':
            case 'integer':

                if (!is_numeric($value)) {

                    throw new \UnexpectedValueException(sprintf("expected numeric value\nfound: '%s'", $value));
                }

                $value = intval($value);
                break;

            case 'float':
            case 'number':

                if (!is_numeric($value)) {

                    throw new \UnexpectedValueException(sprintf("expected numeric value\nfound: '%s'", $value));
                }

                $value = floatval($value);
                break;

            default:

                if (!is_string($value)) {

                    throw new \InvalidArgumentException(sprintf("expected string value\nfound: %s", is_null($value) ? '(bool)' : gettype($value)));
                }

                break;
        }

        if (!empty($this->options) && !in_array($value, $this->options)) {

            throw new \ValueError(sprintf("found: '%s'\nexpected any of: [%s]", $value, implode(', ', array_map('json_encode', $this->options))));
        }

        if ((is_bool($value) && $this->type == 'auto') || $this->type == 'bool') {

            $this->value = $value;
        } else if ($this->multiple) {

            if (!isset($this->value)) {

                $this->value = [];
            }

            if (!is_array($this->value)) {

                $this->value = [$this->value];
            }

            $this->value[] = $value;
        } else {

            $this->value = $value;
        }

        $this->isset = true;

        return $this;
    }

    public function isSet(): bool
    {

        return $this->isset || isset($this->defaultValue);
    }

    public function isRequired(): bool
    {

        return $this->required;
    }

    public function getValue()
    {

        if ($this->required && !$this->isset && !isset($this->defaultValue)) {

            throw new \ValueError('required value not set');
        }

        if ($this->multiple && is_array($this->value) && count($this->value) == 1) {

            return $this->value[0];
        }

        return $this->value ?? $this->defaultValue;
    }

    public function isMultiple(): bool
    {

        return $this->multiple;
    }

    public function getDefaultValue() {

        return $this->defaultValue;
    }

    public function getOptions(): array
    {

        return $this->options;
    }
}