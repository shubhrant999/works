<?php
require_once '../server.php';
error_reporting(E_ALL); 
ini_set("display_errors", 0); 

  $token = isset($_REQUEST['access_token']) ? $_REQUEST['access_token']: '';

  if(isset($token) && !empty($token)){

    // Handle a request to a resource and authenticate the access token
    if (!$server->verifyResourceRequest(OAuth2\Request::createFromGlobals())) {
        $server->getResponse()->send();
        die;
    }
    

    // get the HTTP method, path and body of the request
    $method = $_SERVER['REQUEST_METHOD'];
    $request = explode('/', trim($_SERVER['PATH_INFO'],'/'));
    $input = file_get_contents('php://input');
    

    $resprint = array('request'=>$request);
    echo json_encode($resprint);
    
    die;

    $limit = (isset($_REQUEST['limit']) && $_REQUEST['limit'] <= 100 ) ? $_REQUEST['limit']: 100;

    $page = isset($_REQUEST['page']) ? $_REQUEST['page']: 1;
    $offset = ( $limit*($page-1) > 0 ) ? $limit*($page-1) : 0;

    // connect to the mysql database
    $link = mysqli_connect('localhost', 'root', '', 'api_db');
    mysqli_set_charset($link,'utf8');

    // retrieve the table and key from the path
    $table = preg_replace('/[^a-z0-9_]+/i','',array_shift($request));


    if($method == 'GET'){
      $key = array_shift($request)+0;
    }else{
      $action = array_shift($request);
      $key = array_shift($request)+0;
    }

    

    // escape the columns and values from the input object
    $columns = preg_replace('/[^a-z0-9_]+/i','',array_keys($input));
    $values = array_map(function ($value) use ($link) {
      if ($value===null) return null;
      return mysqli_real_escape_string($link,(string)$value);
    },array_values($input));


    // build the SET part of the SQL command
    $set = '';
    for ($i=0;$i<count($columns);$i++) {
      $set.=($i>0?',':'').'`'.$columns[$i].'`=';
      $set.=($values[$i]===null?'NULL':'"'.$values[$i].'"');
    }


    switch ($method) { 
      case 'GET':
        $sqlCount = "select id from `$table`".($key ? " WHERE id=$key" : '');
        $sql = "select * from `$table`".($key ? " WHERE id=$key" : ' LIMIT '.$offset.', '.$limit); break;
      case 'POST':
        if($action == 'add'){
          $sql = "INSERT into `$table` set $set"; break;
        }else if($action == 'delete'){
          $sql = "DELETE FROM `$table` where id=$key"; break;
        }
      case 'PUT':
        if($action == 'update'){
          $sql = "UPDATE `$table` set $set where id=$key"; break;
        }        
    }

    // excecute SQL statement
    $result = mysqli_query($link,$sql);
    $resultCount = mysqli_query($link,$sqlCount);
    $resCount = mysqli_num_rows($resultCount);
    $totalPages = ceil($resCount / $limit);
    $nextpage = ($totalPages > $page) ? $page + 1 :'';

    if(!$result) {  
      $errCode = mysqli_errno($link);
      switch ($errCode)
      {
        case 1146:
          echo '{ "status" : 1, "result" : { "response" : "Error: Bad Request", "code" : 0001146x }}'; 
          die;
        default:
          echo '{ "status" : 1, "result" : { "response" : "Error: Bad Request", "code" : 0001000x }}';  
          die;
      } 
    }

    // die if SQL statement failed
    if ((!$result) AND ($result->num_rows == 0)) {
      echo '{ "status" : 1, "result" : { "response" : "Error: Bad Request", "code" : 0002000x }}'; 
      http_response_code(404);
      die(mysqli_error());
    }

    $arrne = array(
      "status" => 2,
      "result" => array(),
      "pagination" => array()
    );
    $arrne['pagination']['per_page'] = $limit;
    $arrne['pagination']['total_count'] = $resCount;
    $arrne['pagination']['Current Page'] = ($resCount > 1) ? $page : 1;
    $arrne['pagination']['Next Page'] = $nextpage;

    if ($method == 'GET'){ 
      for ($i=0;$i<mysqli_num_rows($result);$i++) { 
        array_push( $arrne['result'],mysqli_fetch_assoc($result));    
      }
      echo json_encode($arrne, FALSE);
    }elseif($method == 'POST'){
      echo '{ "status" : 2, "result" : { "response" : "Successfull" } }'; 
    } else {
      echo mysqli_affected_rows($link);
    }

    // close mysql connection
    mysqli_close($link);
    
  }else{
    echo '{ "status" : 1, "result" : { "response" : "Error: Bad Request", "code" : 0000008x }}'; 
    die;
  }

  
die;


