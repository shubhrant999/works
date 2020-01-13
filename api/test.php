<?php
function getToken(){
	$token_url = 'http://localhost/api/token.php';

	$data = array(
		'client_id' => 'testclient',
		'client_secret' => 'testpass',
		'grant_type' => 'client_credentials'
	 );

	$curl = curl_init();

	curl_setopt_array($curl, array(
		CURLOPT_URL => $token_url,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => "",
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 30,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => "POST",
		CURLOPT_POSTFIELDS => $data,
		CURLOPT_HTTPHEADER => array(
			// "accept: application/json",
			// "cache-control: no-cache",
			// "content-type: application/json"
		),
	)); 
	// 
	$response = curl_exec($curl);
	$err = curl_error($curl);
	curl_close($curl);
		
	if($err) {
	 echo "cURL Error #:" . $err;
	} else {
		$result = json_decode($response); 
		if(isset($result->error) && !empty($result->error)){
			echo "Error #: " . $result->error.' => '.$result->error_description ;
			echo '{ "status" : 1, "result" : { "response" : "Error: '.$result->error_description.'", "code" : 0000009x }}'; 
			die;
		}else{				  	
	 		return $result->access_token;
		}
	}
}
$token = getToken();

//GET request
// $url = "http://localhost/api/product/rest.php/products/6";
// $url = "http://localhost/api/product/rest.php/products";
$url = "http://localhost/api/product/rest.php/products/add?access_token=".$token;

// POST request 
// $url = "http://localhost/api/product/rest.php/products/add/?access_token=".$token;
// $url = "http://localhost/api/product/rest.php/products/update/1/?limit=40&page=1";
// $url = "http://localhost/api/product/rest.php/products/delete/1/?limit=40&page=1&access_token=".$token;


$data = array(
    'name' => 'test 0511',
    'description' => 'testing product 0511',
    'price' => '11',
    'category_id' => '0511'
 );
$data = json_encode($data);

$curl = curl_init();

curl_setopt_array($curl, array(
	CURLOPT_URL => $url,
	CURLOPT_RETURNTRANSFER => true,
	CURLOPT_ENCODING => "",
	CURLOPT_MAXREDIRS => 10,
	CURLOPT_TIMEOUT => 30,
	CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	CURLOPT_CUSTOMREQUEST => "POST",
	CURLOPT_POSTFIELDS => $data,
	CURLOPT_HTTPHEADER => array(
		"accept: application/json",
		"cache-control: no-cache",
		"content-type: application/json"
	),
)); 

$response = curl_exec($curl);
$err = curl_error($curl);
curl_close($curl);

print_r($response);
echo $url;

if ($err) {
 echo "cURL Error #:" . $err;
} else {
	//echo $response;
  	$result = json_decode($response); 
 	// print_r($result);
}

