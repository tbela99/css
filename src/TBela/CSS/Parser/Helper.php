<?php

namespace TBela\CSS\Parser;

/**
 * Class Helper
 * @package TBela\CSS\Parser
 */
class Helper
{

    /**
     * @return string
     * @ignore
     * @ignore
     */
    public static function getCurrentDirectory()
    {

        if (isset($_SERVER['PWD'])) {

            // when executing viw the cli
            return $_SERVER['PWD'];
        }

        return dirname($_SERVER['PHP_SELF']);
    }

    /**
     * @param string $file
     * @param string $path
     * @return string
     * @ignore
     */
    public static function resolvePath($file, $path = '')
    {

        if ($path !== '') {

            if (!preg_match('#^(https?:/)?/#', $file)) {

                if ($file[0] != '/') {

                    if ($path[strlen($path) - 1] != '/') {

                        $path .= '/';
                    }
                }

                $file = $path . $file;
            }
        }

        if (strpos($file, '../') !== false) {

            $return = [];

            if (strpos($file, '/') === 0)
                $return[] = '/';

            foreach (explode('/', $file) as $p) {

                if ($p == '..') {

                    array_pop($return);
//                    continue;

                } else if ($p == '.') {

                    continue;

                } else {

                    $return[] = $p;
                }
            }

            $file = implode('/', $return);
        } else {

            $file = preg_replace(['#/\./#', '#^\./#'], ['/', ''], $file);
        }

        return preg_replace('#^' . preg_quote(static::getCurrentDirectory() . '/', '#') . '#', '', $file);
    }

    /**
     * compute relative path
     * @param string $file
     * @param string $ref
     * @return string
     */
    public static function relativePath($file, $ref) {

        // handle urls???

        if ($file !== '' && $file[0] != '/') {

            $file = static::getCurrentDirectory().'/'.$file;
        }

        if ($ref !== '' && $ref[0] != '/') {

            $ref = static::getCurrentDirectory().'/'.$ref;
        }

        $basename = basename($file);

        $ref = explode('/', dirname($ref));
        $file = explode('/', dirname($file));

        $j = count($ref);

        while ($j--) {

            if ($ref[$j] == '.') {

                array_splice($ref, $j, 1);
                continue;
            }

            if ($ref[$j] == '..' && isset($ref[$j - 1]) && $ref[$j - 1] != '..') {

                array_splice($ref, $j - 1, 2);
                $j--;
            }
        }

        $j = count($file);

        while ($j--) {

            if ($file[$j] == '.') {

                array_splice($file, $j, 1);
                continue;
            }

            if ($file[$j] == '..' && isset($file[$j - 1]) && $file[$j - 1] != '..') {

                array_splice($file, $j - 1, 2);
            }
        }

        while ($ref) {

            $r = $ref[0];

            if (!isset($file[0]) || $file[0] != $r) {

                break;
            }

            array_shift($file);
            array_shift($ref);
        }

        $result = implode('/', array_merge(array_fill(0, count($ref), '..'), $file));

        return ($result === '' ? '' : $result.'/').$basename;
    }

    /**
     * @param string $url
     * @param array $options
     * @param array $curlOptions
     * @return bool|string
     * @ignore
     */
    public static function fetchContent($url, array $options = [], array $curlOptions = [])
    {

        if (strpos($url, '//') === 0) {

            $url = (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off' ? 'http' : 'https') . ':' . $url;
        }

        $ch = curl_init($url);

        // Turn on SSL certificate verfication
        curl_setopt($ch, CURLOPT_CAINFO, __DIR__ . '/cacert.pem');
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        // enable compression
        curl_setopt($ch, CURLOPT_ENCODING, '');

        // google font sends a different response when this header is missing
        curl_setopt($ch, CURLOPT_HTTPHEADER, [

            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:80.0) Gecko/20100101 Firefox/80.0'
        ]);

        if (!empty($curlOptions)) {

            curl_setopt_array($ch, $curlOptions);
        }

        if (!empty($options)) {

            // Tell the curl instance to talk to the server using HTTP POST
            curl_setopt($ch, CURLOPT_POST, count($options));
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($options));
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($ch);

        if (curl_getinfo($ch, CURLINFO_HTTP_CODE) != 200) {

            curl_close($ch);
            return false;
        }

        curl_close($ch);
        return $result;
    }
}