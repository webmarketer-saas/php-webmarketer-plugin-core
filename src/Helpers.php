<?php

namespace WebmarketerPluginCore;

class Helpers
{
    /**
     * @template T
     * @param $array T[]
     * @param $callback callable
     * @return T|null
     */
    public static function arrayFirst($array, $callback)
    {
        $match_array = array_filter($array, $callback);

        if (count($match_array) === 0) {
            return null;
        }

        return array_values($match_array)[0];
    }

}