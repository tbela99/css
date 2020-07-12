<?php

namespace TBela\CSS\Parser;

trait ParserTrait
{

    /**
     * @param string $string
     * @return false|string
     */
    public static function stripQuotes($string, $force = false) {

        $q = substr($string, 0, 1);

        if (($q == '"' || $q == "'") && strlen($string) > 2 && substr($string, -1) == $q && ($force || preg_match('#^[\w_-]+$#', $string))) {

            return substr($string, 1, -1);
        }

        return $string;
    }

    protected static function is_separator($char)
    {

        switch ($char) {

            case ',':
            case '/':
            case '+':
            case '-':
            case '>':
            case '~':
            case ':':

                return true;
        }

        return false;
    }

    protected static function match_comment($string, $start, $end)
    {

        $i = $start + 1;

        while ($i++ < $end) {

            switch ($string[$i]) {

                case '*':

                    if ($string[$i + 1] == '/') {

                        return substr($string, $start, $i + 2 - $start);
                    }

                    break;
            }
        }

        // unterminated comment is still a valid comment
        return false;
    }

    /**
     * read a string until it encounter any of the $char_stop characters and return the corresponding substring
     * @param string $string
     * @param int $startPosition
     * @param int $endPosition
     * @param array $char_stop
     * @return false|string
     */
    protected static function substr($string, $startPosition, $endPosition, array $char_stop) {

        if ($startPosition < 0 || substr($string, $startPosition, 1) === false) {

            return false;
        }

        if ($startPosition >= strlen($string)) {

            return '';
        }

        $buffer = $string[$startPosition];

        while ($startPosition + 1 <= $endPosition) {

            if(!isset($string[++$startPosition])) {

                break;
            }

            if (in_array($string[$startPosition], $char_stop)) {

                // do not capture empty statement
                if ($string[$startPosition] == ';' && trim($buffer) === '') {

                    $buffer .= ' ';
                    $startPosition++;
                    continue;
                }

                $buffer.= $string[$startPosition];
                return $buffer;
            }

            switch ($string[$startPosition]) {

                case '\\':

                    $buffer .= $string[$startPosition];

                    if (isset($string[$startPosition + 1])) {

                        $buffer .= $string[++$startPosition];
                    }

                    break;

                case '/':

                    if (isset($string[$startPosition + 1]) && $string[$startPosition + 1] == '*') {

                        // capture comments
                        $comment = static::match_comment($string, $startPosition, $endPosition);

                        $buffer .= $comment;
                        $startPosition += strlen($comment) - 1;
                    }

                    else {

                        $buffer .= $string[$startPosition];
                    }

                    break;

                case '"':
                case "'":

                    $substr = static::_close($string, $string[$startPosition], $string[$startPosition], $startPosition, $endPosition);

                    if($substr === false) {

                        return false;
                    }

                    $buffer .= $substr;
                    $startPosition += strlen($substr) - 1;
                    break;

                default:

                    $buffer .= $string[$startPosition];
                    break;
            }
        }

        return $buffer;
    }

    protected static function _close($string, $search, $reset, $start, $end)
    {

        $count = 1;
        $i = $start;

        while (++$i <= $end) {

            switch ($string[$i]) {

                case $search:

                    if ($string[$i - 1] != '\\') {

                        $count--;
                    }

                    break;

                case $reset:

                    if ($string[$i - 1] != '\\') {

                        $count++;
                    }

                    break;

                // in string matching
                case '"':
                case "'":

                    $match = static::_close($string, $string[$i], $string[$i], $i, $end);

                    if ($match === false) {

                        return false;
                    }

                    $i += strlen($match) - 1;
                    break;
            }

            if ($count == 0) {

                break;
            }
        }

        if ($count == 0) {

            return substr($string, $start, $i - $start + 1);
        }

        return false;
    }

    protected static function is_whitespace($char)
    {

        return preg_match("#^\s$#", $char);
    }
}