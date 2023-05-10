<?php

namespace App\Services;

use App\Exception\BillingUnavailableException;

class BaseApiService
{
    public static function get($url = null, $params = null, $headers = null)
    {
        $curl_settings = curl_init();
        $contentTypeArray = ['Content-Type: application/json'];
        $stringedParam = $params ? '?' . http_build_query($params) : '';
        curl_setopt($curl_settings, CURLOPT_URL, "{$_ENV['BILLING_ADDRESS']}{$url}{$stringedParam}");
        curl_setopt(
            $curl_settings,
            CURLOPT_HTTPHEADER,
            $headers ? array_merge($contentTypeArray, $headers) : $contentTypeArray
        );
        curl_setopt($curl_settings, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl_settings);
        if (!$response) {
            return new BillingUnavailableException();
        }
        curl_close($curl_settings);
        return $response;
    }

    public static function post($url = null, $params = null, $headers = null)
    {
        $curl_settings = curl_init();
        $contentTypeArray = ['Content-Type: application/json'];
        curl_setopt($curl_settings, CURLOPT_URL, "{$_ENV['BILLING_ADDRESS']}{$url}");
        curl_setopt($curl_settings, CURLOPT_POST, true);
        curl_setopt(
            $curl_settings,
            CURLOPT_HTTPHEADER,
            $headers ? array_merge($contentTypeArray, $headers) : $contentTypeArray
        );
        curl_setopt($curl_settings, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl_settings, CURLOPT_POSTFIELDS, json_encode($params));
        $response = curl_exec($curl_settings);
        if (!$response) {
            return new BillingUnavailableException();
        }
        curl_close($curl_settings);
        return $response;
    }
}
