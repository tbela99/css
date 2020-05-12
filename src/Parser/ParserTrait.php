<?php

namespace TBela\CSS\Parser;

trait ParserTrait
{

    /**
     * @param string $string
     * @param bool $force_removal
     * @return false|string
     */
    public static function stripQuotes($string, $force_removal = false) {

        $q = substr($string, 0, 1);

        if (($q == '"' || $q == "'") && strlen($string) > 2 && substr($string, -1) == $q && ($force_removal || !preg_match('#[\s]#', $string))) {

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

        return false;
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