#!/usr/bin/php
<?php

/**
 * An example on how to use this library to fetch a css file and its resources
 */

require 'autoload.php';

use TBela\CSS\Parser;
use TBela\CSS\Traverser;
use \TBela\CSS\Value;
use TBela\CSS\Value\Set;

$localName = './remote_files/vue.css';

$parser = (new Parser())->load(is_file($localName) ? $localName : 'https://unpkg.com/docsify/lib/themes/vue.css');

$stylesheet = (new Traverser())->on('enter', function ($node) {

    if ($node->getType() == 'Declaration') {

        /**
         * @var \TBela\CSS\Element\Declaration $node
         */

        $node->getValue()->map(function ($value) {

            if ($value->type == 'css-url') {

                $url = $value->value;
                $parts = explode('/', parse_url($url)['path']);

                $localName = './remote_files/'.crc32($url).'-'.end($parts);

                if (!is_file($localName)) {

                    $data = Parser\Helper::fetchContent($url);

                    if ($data !== false) {

                        file_put_contents($localName, $data);
                    }
                }

                if (is_file($localName)) {

                    return Value::getInstance((object) [
                            'name' => 'url',
                            'type' => 'css-url',
                            'arguments' => new Set([
                                (object) [
                                        'type' => 'css-string',
                                        'value' => $localName
                                ]])
                        ]);
                }
            }

            return $value;
        });
    }

})->traverse($parser->parse());

file_put_contents('./remote_files/vue-transformed.css', $stylesheet);