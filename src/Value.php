<?php

namespace TBela\CSS;

use InvalidArgumentException;
use stdClass;
use TBela\CSS\Value\Number;
use TBela\CSS\Value\Set;
use TBela\CSS\Parser\ParserTrait;

/**
 * CSS value base class
 * @package CSS
 * @property-read string $value
 * @property-read Set $arguments
 */
abstract class Value
{
    use ParserTrait;

    /**
     * var stdClass;
     * @ignore
     */
    protected $data = null;

    protected static array $defaults = [];

    protected static array $keywords = [];

    /**
     * @var array
     * @ignore
     */
    protected static array $cache = [];

    /**
     * Value constructor.
     * @param stdClass $data
     */
    protected function __construct($data)
    {

        $this->data = $data;
    }

    /**
     * Cleanup cache
     * @ignore
     */
    public function __destruct()
    {
        unset (static::$cache[spl_object_hash($this)]);
    }

    /**
     * get property
     * @param string $name
     * @return mixed|null
     * @ignore
     */
    public function __get($name)
    {
        if (isset($this->data->{$name})) {

            return $this->data->{$name};
        }

        if (is_callable([$this, 'get' . $name])) {

            return call_user_func([$this, 'get' . $name]);
        }

        return null;
    }

    /**
     * @param $name
     * @return bool
     * @ignore
     */
    public function __isset ($name)  {

        return isset($this->data->{$name});
    }

    /**
     * test if this object matches the specified type
     * @param string $type
     * @return bool
     */
    public function match(string $type): bool
    {

        return strtolower($this->data->type) == $type;
    }

    public static function getClassName(string $type): string {

        static $classNames = [];

        if (!isset($classNames[$type])) {

            $classNames[$type] = Value::class.'\\'.preg_replace_callback('#(^|-)([a-z])#', function ($matches) {

                return strtoupper($matches[2]);
            }, $type);
        }

        return $classNames[$type];
    }

    protected static function type(): string {

        static $types = [];

        if (!isset($types[static::class])) {

            $name = explode('\\', static::class);

            $types[static::class] = preg_replace_callback('#(^|[^A-Z])([A-Z])#', function ($matches) {

                return (empty($matches[1]) ? '' : $matches[1].'-').strtolower($matches[2]);
            }, end($name));
        }

        return $types[static::class];
    }

    /**
     * @param object $token
     * @return bool
     */
    protected static function matchDefaults ($token) : bool {

        return isset($token->value) && in_array(strtolower($token->value), static::$defaults);
    }

    /**
     * @param object $token
     * @param object $previousToken
     * @param object $previousValue
     * @return bool
     */
    public static function matchToken ($token, $previousToken = null, $previousValue = null): bool {

        return $token->type == static::type() || isset($token->value) && static::matchKeyword($token->value);
    }

    /**
     * test if $data matches this class
     * @param stdClass $data
     * @return bool
     */
    protected static function validate($data): bool
    {

        return isset($data->value);
    }

    /**
     * create an instance
     * @param stdClass $data
     * @return Value
     */
    public static function getInstance($data): Value
    {

        if ($data instanceof Value) {

            return $data;
        }

        if (!isset($data->type)) {

            throw new InvalidArgumentException('Type property is required: ' . gettype($data) . ':' . var_export($data, true), 400);
        }

        $className = static::getClassName($data->type);

        if (!class_exists($className)) {

            error_log(__METHOD__ . ' missing data type? ' . $className);
            $className = static::class;
        }

        if (!$className::validate($data)) {

            throw new InvalidArgumentException('Invalid argument: $className:' . $className . ' data:' . var_export($data, true), 400);
        }

        return new $className($data);
    }

    /**
     * convert this object to string
     * @param array $options
     * @return string
     */
    public function render(array $options = []): string
    {

        return $this->data->value;
    }

    /**
     * parse a css value
     * @param string $string
     * @param string $property
     * @param bool $capture_whitespace
     * @return Set
     */
    public static function parse(string $string, ?string $property = null, bool $capture_whitespace = true): Set
    {
        if ($string instanceof Set) {

            return $string;
        }

        $string = trim($string);
        $property = strtolower($property);

        if ($property !== '') {

            $className = static::getClassName($property);

            if (is_callable([$className, 'doParse'])) {

                return call_user_func([$className, 'doParse'], $string, $capture_whitespace);
            }
        }

        return static::doParse($string, $capture_whitespace);
    }

    /**
     * remove unnecessary tokens
     * @param array $tokens
     * @param array $options
     * @return array
     */
    public static function reduce(array $tokens, array $options = []): array
    {
        $count = count($tokens) - 1;

        if ($count > 1) {

            $j = $count;

            while ($j-- >= 1) {

                $token = $tokens[$j];

                if ($token->type == 'whitespace' && ($tokens[$j + 1]->type == 'separator' || ($tokens[$j + 1]->type == 'css-string' && $tokens[$j + 1]->value == '!important'))) {

                    array_splice($tokens, $j, 1);
                } else if ($token->type == 'separator' && $tokens[$j + 1]->type == 'whitespace') {

                    array_splice($tokens, $j + 1, 1);
                }

                else if (!empty($options['remove_defaults']) && !in_array($token->type, ['whitespace', 'separator'])) {

                    $className = static::getClassName($token->type);

                    if (is_callable($className.'::matchDefaults') && call_user_func($className.'::matchDefaults', $token)) {

                        // remove item
                        array_splice($tokens, $j, 1);

                        if (isset($tokens[$j]) && $tokens[$j]->type == 'whitespace') {

                            // remove whitespace after the item removed
                            array_splice($tokens, $j, 1);
                        }
                    }
                }
            }
        }

        return $tokens;
    }

    /**
     * parse a css value
     * @param string $string
     * @param bool $capture_whitespace
     * @return Set
     */
    protected static function doParse(string $string, bool $capture_whitespace = true): Set
    {

        return new Set(static::reduce(static::getTokens($string, $capture_whitespace)));
    }

    /**
     * parse a css value
     * @param Set|string $string
     * @param bool $capture_whitespace
     * @return array|null
     */
    protected static function getTokens(string $string, $capture_whitespace = true)
    {

        $string = trim($string);

        $i = -1;
        $j = strlen($string) - 1;

        $buffer = '';
        $tokens = [];

        while (++$i <= $j) {

            switch ($string[$i]) {

                case ' ':
                case "\t":
                case "\n":
                case "\r":

                    if ($buffer !== '') {

                        $tokens[] = static::getType($buffer);
                        $buffer = '';
                    }

                    if ($capture_whitespace) {

                        $k = $i;

                        while (++$k <= $j) {

                            if (preg_match('#\s#', $string[$k])) {

                                continue;
                            }

                            $buffer = '';

                            if (!static::is_separator($string[$k]) || ($j >= $k + 1 && !static::is_whitespace($string[$k + 1]))) {

                                $token = new stdClass;
                                $token->type = 'whitespace';
                                $tokens[] = $token;
                            }

                            $i = $k - 1;
                            break 2;
                        }
                    }

                    break;

                case '"':
                case "'":

                    $next = $i;

                    while (true) {

                        $next = strpos($string, $string[$i], $next + 1);

                        if ($next !== false) {

                            if ($string[$next - 1] != '\\') {

                                break;
                            }
                        } else {

                            break;
                        }
                    }

                    $token = new stdClass;

                    $token->type = 'css-string';
                    $token->value = substr($string, $i, $next === false ? $j + 1 : $next - $i + 1);

                    $tokens[] = $token;
                    $buffer = '';


                    if ($next === false) {

                        $i = $j;
                        continue 2;
                    }

                    $i = $next;

                    break;

                case '(':

                    if ($string[$i - 1] != '\\') {

                        $params = static::_close($string, ')', '(', $i, $j);

                        if ($params !== false) {

                            $token = new stdClass;

                            if (preg_match('#^(-([a-zA-Z]+)-(\S+))#i', $buffer, $matches)) {

                                $token->name = $matches[3];
                                $token->vendor = $matches[2];
                            } else {

                                $token->name = $buffer;
                            }

                            if (in_array(strtolower($token->name), ['rgb', 'rgba', 'hsl', 'hsla', 'hwb', 'device-cmyk'])) {

                                $token->type = 'color';
                            } else {

                                $token->type = $token->name === '' ? 'css-parenthesis-expression' : 'css-function';
                            }

                            $str = substr($params, 1, -1);

                            if ($buffer == 'url') {

                                $t = new stdClass;

                                $t->type = 'css-string';
                                $t->value = $str;

                                $token->arguments = new Set([$t]);
                            }

                            else {

                                $token->arguments = static::parse($str, true, $capture_whitespace);
                            }

                            $tokens[] = $token;

                            $buffer = '';
                            $i += strlen($params) - 1;
                        } else {

                            $tokens[] = static::getType($buffer . $params);
                            $i = $j;
                        }

                        break;
                    }

                case ',':
                case '/':
                case '+':
                case ':':

                    if ($i < $j && $string[$i + 1] == '*' && $string[$i] == '/') {

                        $params = static::match_comment($string, $i, $j);

                        if ($params !== false) {

                            $token = new stdClass;

                            $token->type = 'comment';
                            $token->value = $params;

                            $tokens[] = $token;

                            $i += strlen($params) - 1;
                            $buffer = '';
                            break;
                        }
                    }

                    if ($buffer !== '') {

                        $tokens[] = static::getType($buffer);
                        $buffer = '';
                    }

                    if (!empty($tokens) && in_array($string[$i], ['-', '+'])) {

                        $token = end($tokens);

                        if (in_array($token->type, ['separator', 'whitespace'])) {

                            $buffer .= $string[$i];
                            break;
                        }
                    }

                    $token = new stdClass;
                    $token->type = 'separator';
                    $token->value = $string[$i];
                    $tokens[] = $token;

                    break;

                default:

                    if ($string[$i] == '!') {

                        if ($buffer !== '') {

                            $tokens[] = static::getType($buffer);
                            $buffer = '';
                        }
                    }

                    $buffer .= $string[$i];
            }
        }

        if ($buffer !== '') {

            $tokens[] = static::getType($buffer);
        }

        return $tokens;
    }

    /**
     * @param $token
     * @return stdClass
     */
    protected static function getType(string $token)
    {

        $type = new stdClass;

        $type->value = $token;

        if (substr($token, 0, 1) != '#' && is_numeric($token)) {

            $type->type = 'number';
        } else if ($token == 'currentcolor' || isset(Color::COLORS_NAMES[$token]) || preg_match('#^\#([a-f0-9]{8}|[a-f0-9]{6}|[a-f0-9]{4}|[a-f0-9]{3})$#i', $token)) {

            $type->type = 'color';
            $type->colorType = $token == 'currentcolor' ? 'keyword' : 'hex';
        } else if (preg_match('#^(((\+|-)?(?=\d*[.eE])([0-9]+\.?[0-9]*|\.[0-9]+)([eE](\+|-)?[0-9]+)?)|(\d+|(\d*\.\d+)))([a-zA-Z]+|%)$#', $token, $matches)) {

            $type->type = 'unit';
            $type->value = $matches[1];
            $type->unit = $matches[9];
        } else {

            $type->type = 'css-string';
        }

        return $type;
    }


    /**
     * return the list of keywords
     * @return array
     * @ignore
     */
    public static function keywords(): array
    {

        return static::$keywords;
    }

    /**
     * @param string $string
     * @param array|null $keywords
     * @return string|null
     * @ignore
     */
    public static function matchKeyword($string, array $keywords = null): ?string
    {

        if (is_null($keywords)) {

            $keywords = static::keywords();
        }

        $string = static::stripQuotes($string, true);

        foreach ($keywords as $keyword) {

            if (strcasecmp($string, $keyword) === 0) {

                return $keyword;
            }
        }

        return null;
    }

    /**
     * @param Value|null $value
     * @param array $options
     * @return string
     */
    public static function getNumericValue (?Value $value, array $options = []): ?string {

        if (is_null($value) || $value->value === '') {

            return null;
        }

        return Number::compress($value->unit == '%' ? $value->value / 100 : $value->render($options));
    }

    /**
     * @param Value $value
     * @return string
     */
    public static function getRGBValue (Value $value): string {

        return Number::compress($value->unit == '%' ? 255 * $value->value / 100 : $value->value);
    }

    /**
     * @param Value|null $value
     * @param array $options
     * @return string
     */
    public static function getAngleValue (?Value $value, array $options = []): ?string {

        if (is_null($value) || $value->value === '') {

            return null;
        }

        switch ($value->unit) {

            case 'rad':

                return floatval((string)$value->value) / (2 * pi());

            case 'grad':

                return floatval((string)$value->value) / 400;
            case 'turn':
                // do nothing
                return floatval((string)$value->value);

        //    case 'deg':
        //    default:

        //        break;
        }

        return floatval((string)$value->value) / 360;
    }

    /**
     * convert to string
     * @return string
     */
    public function __toString()
    {
        return $this->render();
    }
}