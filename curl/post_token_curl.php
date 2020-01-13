<?php

    function get_cedato_token() {
        define('CEDATO_API_ENDPOINT_URL', 'https://api.cedato.com/api/');

        define('CEDATO_API_ID_CLIENT', 'c5aafe3766fe294e76a20e93471a41a6176abc6f');
        define('CEDATO_API_ID_SECRET', '8fe95aea288c58fba37fb41f3cd5dc613e7bf77b');

        $res = curl_init();
        curl_setopt_array($res, array(
            CURLOPT_URL => CEDATO_API_ENDPOINT_URL . 'token',
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_POST => TRUE,
            CURLINFO_HEADER_OUT => true,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => http_build_query(array(
                'grant_type' => 'client_credentials'
            )),
            CURLOPT_HTTPHEADER => array(
                'accept: application/json',
                'api-version: 1',
                'authorization: Basic ' . base64_encode(CEDATO_API_ID_CLIENT . ':' . CEDATO_API_ID_SECRET),
                'content-type: application/x-www-form-urlencoded'
            ),
            CURLOPT_SSL_VERIFYHOST => FALSE,
            CURLOPT_SSL_VERIFYPEER => FALSE
        ));
        $response = curl_exec($res);
        $response = json_decode($response);
        if (isset($response->status) && $response->status == 'OK') {
            return $response->data->token_type . " " . $response->data->access_token;
        }
        return FALSE;
    }      
       
    $token = get_cedato_token();            
    if ($token == FALSE) {
        echo 'Token is not valid', $token;
        return FALSE;
    }

    $res = curl_init();           
    curl_setopt_array($res, array(
        CURLOPT_URL => 'url' ,
        CURLOPT_RETURNTRANSFER => TRUE,
        CURLOPT_POST => TRUE,
        CURLINFO_HEADER_OUT => true,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => array(
            'accept: application/json',
            'api-version: 1',
            'authorization: ' . $token
        ),
        CURLOPT_SSL_VERIFYHOST => FALSE,
        CURLOPT_SSL_VERIFYPEER => FALSE
    ));
    $response = curl_exec($res);
    $err = curl_error($curl);
    curl_close($res);
    $response = json_decode($response);                 
    // echo '<pre>';
    // print_r($response);
    // die;               
   

       