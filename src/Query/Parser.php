<?php

namespace TBela\CSS\Query;

use TBela\CSS\Parser\ParserTrait;
use TBela\CSS\Parser\SyntaxError;


class Parser
{
    /**
     * @var int
     */
    protected int $position;
    /**
     * @var int
     */
    protected int $last;
    /**
     * @var string
     */
    protected string $string;
    /**
     * @var array
     */
    protected array $tokens;

    /**
     * @param $string
     * @return TokenInterface[]
     * @throws SyntaxError
     */
    public function parse(string $string): array
    {

        $string = trim($string);

        $i = -1;
        $j = strlen($string) - 1;

        $this->position = -1;
        $this->last = $j;
        $this->string = $string;
        $this->tokens = [];

        if (substr($string, 0, 2) == '//') {

            $this->tokens[] = (object)['type' => 'select', 'node' => '*', 'context' => 'root'];
            $i += 2;
        } else if (substr($string, 0, 1) == '/') {

            $this->tokens[] = (object)['type' => 'select', 'node' => '>', 'context' => 'root'];
            $i += 1;
        }

        $this->position = $i;

        while ($this->position < $this->last) {

            $this->parse_selectors();
            $this->parse_path();
        }

        $i = $j = count($this->tokens);

        while ($i-- >= 1) {

            if (isset($this->tokens[$i + 1]->type) && $this->tokens[$i + 1]->type == 'select' && in_array($this->tokens[$i + 1]->node, ['.', '..'])) {

                $token = $this->tokens[$i + 1];

                if (in_array($token->node, ['.', '..'])) {

                    array_splice($this->tokens, $i, $token->node == '.' ? 2 : 1);
                    $i--;
                    continue;
                }
            }

            if ($this->tokens[$i]->type == 'select' && isset($this->tokens[$i]->node) && $this->tokens[$i]->node == '.') {

                array_splice($this->tokens, $i, 1);
                continue;
            }

            if ($this->tokens[$i]->type == 'selector') {

                $k = count($this->tokens[$i]->value);

                while ($k--) {

                    if ($this->tokens[$i]->value[$k]->type == 'operator' && !in_array($this->tokens[$i]->value[$k]->value, [','])) {

                        throw new SyntaxError(sprintf('%s is allowed only inside attribute. ex [@name %s "text"]. input: %s', $this->tokens[$i]->value[$k]->value, $this->tokens[$i]->value[$k]->value, $string), 400);
                    }

                    if ($this->tokens[$i]->value[$k]->type == 'string' && in_array($this->tokens[$i]->value[$k]->value, ['', '*'])) {

                        array_splice($this->tokens[$i]->value, $k, 1);
                    }
                }

                if (empty($this->tokens[$i]->value)) {

                    array_splice($this->tokens, $i, 1);
                }
            }
        }

        // set default context
        if (isset($this->tokens[0]) && $this->tokens[0]->type != 'select') {

            array_unshift($this->tokens, (object) ['type' => 'select', 'node' => 'self_or_descendants']);
        }

        return array_map([Token::class, 'getInstance'], $this->tokens);
    }

    /**
     * @throws SyntaxError
     */
    protected function parse_selectors()
    {

        $j = $this->last;
        $i = $this->position;
        $string = $this->string;

        $buffer = '';

        while ($i < $j && $this->is_whitespace($string[$i + 1])) {

            $i++;
        }

        if (substr($string, $i + 1, 1) == '/') {

            throw new SyntaxError(sprintf('unexpected character %s at position %d', '/', $i + 1), 400);
        }

        while ($i++ < $j) {

            switch ($string[$i]) {

                case '"':
                case "'":

                    $q = $string[$i];
                    $buffer .= $string[$i];

                    while ($i++ < $j) {

                        $buffer .= $string[$i];

                        if ($string[$i] == '\\') {

                            if (isset($string[$i + 1])) {

                                $buffer .= $string[++$i];
                            }
                        } else if ($string[$i] == $q) {

                            break;
                        }
                    }

                    break;

                case '\\':

                    $buffer .= $string[$i];

                    if (isset($string[$i + 1])) {

                        $buffer .= $string[++$i];
                    }

                    break;

                case '/':

                    break 2;

                default:

                    $buffer .= $string[$i];
                    break;
            }
        }

        $this->position = $i;

        if ($buffer !== '') {

            array_splice($this->tokens, count($this->tokens), 0, $this->parse_selector($buffer));
        }
    }

    /**
     * @param string $selector
     * @param string $context
     * @return object
     * @throws SyntaxError
     */
    protected function parse_selector(string $selector, string $context = 'selector')
    {

        $selector = trim($selector);

        $buffer = '';

        $in_str = false;
        $in_attribute = false;
        $result = [];

        if ($selector == '.') {

            return [(object)['type' => 'select', 'node' => '.']];
        }

        if ($selector == '..') {

            return [(object)['type' => 'select', 'node' => '..']];
        } else {

            $i = -1;
            $j = strlen($selector) - 1;
            $q = '';

            while ($i++ < $j) {

                if ($in_str) {

                    throw new SyntaxError(sprintf('Expected character %s at position %d', $q, $i - 1));
                }

                switch ($selector[$i]) {

                    case '"':
                    case "'":

                        $match = $this->match_token($selector, $selector[$i], $i);

                        if ($match === false) {

                            throw new SyntaxError(sprintf('missing %s at position %d', $selector[$i], $j));
                        }

                        $buffer .= ParserTrait::stripQuotes($match);
                        $i += strlen($match) - 1;
                        break;

                    case ',':

                        if ($buffer !== '') {

                            $result[] = $this->getTokenType($buffer, $context);
                        }

                        $result[] = (object)['type' => 'separator', 'value' => ','];
                        $buffer = '';
                        break;

                    case '+':
                    case '~':
                    case '>':
                    case '=':
                    case '^':
                    case '*':
                    case '$':
                    case '!':

                        if ($context == 'attribute') {

                            if ($selector[$i] == '=' || (in_array($selector[$i], ['*', '^', '$', '!']) && $i < $j && $selector[$i + 1] == '=')) {

                                if ($buffer !== '') {

                                    $result[] = $this->getTokenType($buffer, $context);
                                    $buffer = '';
                                }

                                if ($i < $j && $selector[$i + 1] == '=') {

                                    $result[] = (object)['type' => 'operator', 'value' => $selector[$i] . $selector[++$i]];
                                    $buffer = '';
                                }
                                else {

                                    $result[] = (object)['type' => 'operator', 'value' => $selector[$i]];
                                }
                            }

                             else {

                                $buffer .= $selector[$i];
                            }

                            break;
                        }

                        else if(in_array($selector[$i], ['*', '^', '$', '!'])) {

                            $buffer .= $selector[$i];
                            break;
                        }

                        if ($buffer !== '') {

                            $result[] = $this->getTokenType($buffer, $context);
                        }

                        $result[] = (object)['type' => 'operator', 'value' => $selector[$i]];
                        $buffer = '';
                        break;

                    case ' ':
                    case "\t":
                    case "\n":
                    case "\r":

                        if ($buffer !== '') {

                            $result[] = $this->getTokenType($buffer, $context);
                            $buffer = '';
                        }

                        while ($i < $j && $this->is_whitespace($selector[++$i])) ;

                        if ($selector[$i] == '/') {

                            break 2;
                        }

                        $i--;
                        //    $result[] = (object)['type' => 'whitespace', 'value' => ' '];
                        break;

                    case '[':

                        if ($context == 'attribute') {

                            throw new SyntaxError(sprintf('Unexpected character %s at position %d in "%s"', $selector[$i], $i, $selector));
                        }

                        $in_attribute = true;
                        $match = $this->match_token($selector, ']', $i);

                        if ($match === false) {

                            throw new SyntaxError(sprintf('missing %s in "%s"', '"]"', $selector));
                        }

                        $result[] = $this->getTokenType($buffer, $context);
                        $data = $this->parse_selector(substr($match, 1, -1), 'attribute');

                        if (isset($data[0])) {

                            $data = $data[0];

                            if (isset($data->value) &&
                                count($data->value) == 1 &&
                                isset($data->value[0]->value) &&
                                is_numeric($data->value[0]->value)) {

                                $data->value[0]->type = 'index';
                                $data->value[0]->value = +$data->value[0]->value;
                            }

                            $result[] = $data;
                        }

                        $in_attribute = false;
                        $buffer = '';
                        $i += strlen($match) - 1;
                        break;

                    case '(':

                        if ($context == 'function') {

                            throw new SyntaxError(sprintf('Unexpected character %s at position %d in "%s"', $selector[$i], $i, $selector));
                        }

                        $in_attribute = true;
                        $match = $this->match_token($selector, ')', $i);

                        if ($match === false) {

                            throw new SyntaxError(sprintf('missing %s in "%s"', '")"', $selector));
                        }

                        $data = $this->parse_selector(substr($match, 1, -1), 'function');

                        $data = $data[0];

                        $data->name = $buffer;
                        $data->arguments = $data->value;

                        unset($data->value);

                        $result[] = $data;

                        $in_attribute = false;
                        $buffer = '';
                        $i += strlen($match) - 1;
                        break;
                    case '\\':

                        if (isset($selector[$i + 1])) {

                            if (!in_array($selector[$i + 1], ['[', ']'])) {

                                $buffer .= $selector[$i];
                            }

                            $buffer .= $selector[++$i];
                        } else {

                            $buffer .= $selector[$i];
                        }

                        break;

                    default:

                        $buffer .= $selector[$i];
                        break;
                }
            }
        }

        if ($in_attribute) {

            throw new SyntaxError('Expected character %s at position %d', ']', $i - 1);
        }

        if ($in_str) {

            throw new SyntaxError('Expected character %s at position %d', $q, $i - 1);
        }

        if ($buffer !== '') {

            $result[] = $this->getTokenType($buffer, $context);
        }

        return [(object)['type' => $context, 'value' => $result]];
    }

    protected function getTokenType(string $token, $context)
    {

        $result = (object)['type' => 'string', 'value' => $token];

        if (substr($token, 0, 1) == '@' && $context != 'selector') {

            $result->type = 'attribute_name';
            $result->value = substr($token, 1);
        }

        return $result;
    }

    /**
     * @throws SyntaxError
     */
    protected function parse_path()
    {

        $j = strlen($this->string) - 1;

        while ($this->position <= $j && $this->is_whitespace($this->string[$this->position])) {

            $this->position++;
        }

        $substr = substr($this->string, $this->position, 2);

        if ($substr == '//') {

            $this->position += 1;
            $this->tokens[] = (object)['type' => 'select', 'node' => '*'];
        } else {
            $token = substr($substr, 0, 1);

            if ($token == '/') {

                //    $this->position++;
                $this->tokens[] = (object)['type' => 'select', 'node' => '>'];
            } else if ($token !== '') {

                throw new SyntaxError(sprintf('expected "%s" at position %d', $token, $this->position));
            }
        }
    }

    protected function is_whitespace($char)
    {

        return preg_match('#^\s+$#sm', $char);
    }

    protected function match_token($string, $close, $start)
    {

        $j = strlen($string) - 1;
        $i = $start;

        $buffer = $string[$i];
        $in_str = true;

        while ($i++ < $j) {

            switch ($string[$i]) {

                case '\\':

                    $buffer .= $string[$i];

                    while ($i++ < $j) {

                        $buffer .= $string[$i];

                        if ($string[$i] == $close && $string[$i - 1] != '\\') {

                            return $buffer;
                        }
                    }

                    break;

                case $close:

                    $buffer .= $string[$i];
                    return $buffer;

                    break;

                default:

                    $buffer .= $string[$i];
                    break;
            }
        }

        if ($in_str) {

            return false;
        }

        return $buffer;
    }
}