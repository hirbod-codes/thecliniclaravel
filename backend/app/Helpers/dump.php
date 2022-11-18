<?php

if (!function_exists('myDD')) {
    /**
     * @return never
     */
    function myDD(...$vars): void
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1);
        array_unshift($vars, ['__FILE__' => isset($trace[0]['file']) ? $trace[0]['file'] : '']);
        array_unshift($vars, ['__LINE__' => isset($trace[0]['line']) ? $trace[0]['line'] : 0]);
        dd(...$vars);
    }
}
