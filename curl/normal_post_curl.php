<?php
	
	function get_spring_serve_token() {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://video.springserve.com/api/v0/auth",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS => "email=rohit@xapads.com&password=ApiPass123#",
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POST => 1,
            CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache",
                "contenttype: application/json"
            ),
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            return json_decode($response);
        }
    }
    


    $token = get_spring_serve_token();
    $date = date('Y-m-d');
    $page = 1;
    $curl = curl_init();
    $dimensions = 'supply_tag_id';

    curl_setopt_array($curl, array(
        CURLOPT_URL => "https://video.springserve.com/api/v0/report",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POSTFIELDS => "start_date=".$date."&end_date=".$date."&timezone=UTC&interval=day&page=".$page."&dimensions[]=supply_tag_id&dimensions[]=app_bundle",
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POST => 1,
        CURLOPT_HTTPHEADER => array(
            'Authorization: '.$token,
            "contenttype: application/json"
        ),
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);
    if ($err) {
        echo "cURL Error #:" . $err;
    } else {
       $data = json_decode($response);
    }


    // echo '<pre>';
    // print_r($data);
    // die;

   