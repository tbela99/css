<?php

namespace TBela\CSS\Parser;

class Helper
{

    /**
     * @ignore
     * @return string
     * @ignore
     */
    public static function getCurrentDirectory() {

        if (isset($_SERVER['PWD'])) {

            // when executing viw the cli
            return $_SERVER['PWD'];
        }

        return dirname($_SERVER['PHP_SELF']);
    }

    /**
     * @param $file
     * @param string $path
     * @return string
     * @ignore
     */
    public static function resolvePath($file, $path = '')
    {

        if ($path !== '') {

            if (!preg_match('#^(https?:/)?/#', $file)) {

                if ($file[0] != '/' && $path !== '') {

                    if ($path[strlen($path) - 1] != '/') {

                        $path .= '/';
                    }
                }

                $file = $path.$file;
            }
        }

        if (strpos($file, '../') !== false) {

            $return = [];

            if (strpos($file, '/') === 0)
                $return[] = '/';

            foreach (explode('/', $file) as $p) {

                if ($p == '..') {

                    array_pop($return);
                    continue;

                } else if ($p == '.') {

                    continue;

                } else {

                    $return[] = $p;
                }
            }

            $file = implode('/', $return);
        }

        else {

            $file = preg_replace(['#/\./#', '#^\./#'], ['/', ''], $file);
        }

        return preg_replace('#^'.preg_quote(static::getCurrentDirectory().'/', '#').'#', '', $file);
    }

    /**
     * @param $url
     * @param array $options
     * @param array $curlOptions
     * @return bool|string
     * @ignore
     */
    public static function fetchContent($url, $options = [], $curlOptions = [])
    {

        if (strpos($url, '//') === 0) {

            $url = (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off' ? 'http' : 'https') . ':' . $url;
        }

        $ch = curl_init($url);

        if (strpos($url, 'https://') === 0) {

            // Turn on SSL certificate verfication
            curl_setopt($ch, CURLOPT_CAINFO, __DIR__ . '/cacert.pem');
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
        }

        if (!empty($curlOptions)) {

            curl_setopt_array($ch, $curlOptions);
        }

        if (!empty($options)) {

            // Tell the curl instance to talk to the server using HTTP POST
            curl_setopt($ch, CURLOPT_POST, count($options));
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($options));
        }

        // 1 second for a connection timeout with curl
        //    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
        // Try using this instead of the php set_time_limit function call
        //    curl_setopt($curl, CURLOPT_TIMEOUT, 60);
        // Causes curl to return the result on success which should help us avoid using the writeback option
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