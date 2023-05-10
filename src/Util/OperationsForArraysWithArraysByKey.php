<?php

namespace App\Util;

class OperationsForArraysWithArraysByKey
{
    public static function leftJoin($leftArray, $rightArray, $key)
    {
        $keys = [];
        $leftArrayByKey = array_reduce($leftArray, function ($result, $item) use ($key) {
            $result[$item[$key]] = $item;
            return $result;
        }, []);

        $rightArrayArrayByKey = array_reduce($rightArray, function ($result, $item) use ($key) {
            $result[$item[$key]] = $item;
            return $result;
        }, []);

        $result = [];
        foreach ($leftArrayByKey as $leftKey => $item) {
            $result[] = array_merge($item, $rightArrayArrayByKey[$leftKey]);
        }

        return $result;
    }
}
