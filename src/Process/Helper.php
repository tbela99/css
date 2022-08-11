<?php

namespace TBela\CSS\Process;

class Helper {

    public static function getCPUCount():int {

        if ('WIN' == strtoupper(substr(PHP_OS, 0, 3)))
        {

            return (int) getenv('NUMBER_OF_PROCESSORS');
        }

        if (!is_callable('\\exec')) {

            return 1;
        }

        $result = exec(is_file('/proc/cpuinfo') ? 'cat /proc/cpuinfo | grep -c processor' : 'sysctl -a | awk \'$0 ~ "hw.ncpu" {print $2}\'');

        return $result === false ? 1 : (int) $result;
    }
}


