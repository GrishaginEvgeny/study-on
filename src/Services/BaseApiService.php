<?php

namespace App\Services;

use App\Exception\BillingUnavailableException;

class BaseApiService
{

    public function get($url = null, $params= null, $headers= null){
        $curl_settings = curl_init();
        $stringedParam = $params ? '?'.http_build_query($params) : '';
        curl_setopt($curl_settings, CURLOPT_URL , "{$_ENV['BILLING_ADDRESS']}{$url}{$stringedParam}");
        curl_setopt($curl_settings, CURLOPT_HTTPHEADER, $headers ?: ['Content-Type: application/json']);
        curl_setopt($curl_settings, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl_settings);
        if(!$response){
            return new BillingUnavailableException();
        }
        curl_close($curl_settings);
        return $response;
    }

    public function post($url= null, $params= null, $headers = null){
        $curl_settings = curl_init();
        curl_setopt($curl_settings, CURLOPT_URL , "{$_ENV['BILLING_ADDRESS']}{$url}");
        curl_setopt($curl_settings, CURLOPT_POST, true);
        curl_setopt($curl_settings, CURLOPT_HTTPHEADER, $headers ?: ['Content-Type: application/json']);
        curl_setopt($curl_settings, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl_settings, CURLOPT_POSTFIELDS , json_encode($params));
        $response = curl_exec($curl_settings);
        if(!$response){
            return new BillingUnavailableException();
        }
        curl_close($curl_settings);
        return $response;
    }
}