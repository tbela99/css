<?php

# change test directory to ./test
chdir(__DIR__);


// because git changes \n to \r\n at some point, this causes test failure
function get_content($file)
{

    return str_replace("\r\n", "\n", file_get_contents($file));
}
