<?php
set_time_limit(0);
ini_set("memory_limit","-1");
$passPages = 'debug-click';
error_reporting(E_ALL);
function get_redirect_target($url, $agent)
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HEADER, 1);
    curl_setopt($ch, CURLOPT_NOBODY, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt( $ch, CURLOPT_HTTPHEADER, array("REMOTE_ADDR: $ip", "HTTP_X_FORWARDED_FOR: $ip"));
    curl_setopt($ch, CURLOPT_USERAGENT, $agent);
    $headers = curl_exec($ch);
    curl_close($ch);
    if (preg_match('/^Location: (.+)$/im', $headers, $matches)){
    return trim($matches[1]);
  }    
    return $url;
}

function get_redirect_url($url, $agent){
    
    $redirect_url = null; 

    $url_parts = @parse_url($url);
    if (!$url_parts) return false;
    if (!isset($url_parts['host'])) return false; //can't process relative URLs
    if (!isset($url_parts['path'])) $url_parts['path'] = '/';

    $sock = fsockopen($url_parts['host'], (isset($url_parts['port']) ? (int)$url_parts['port'] : 80), $errno, $errstr, 30);
    if (!$sock) return false;

    $request = "HEAD " . $url_parts['path'] . (isset($url_parts['query']) ? '?'.$url_parts['query'] : '') . " HTTP/1.1\r\n"; 
    $request .= 'Host: ' . $url_parts['host'] . "\r\n"; 
  $request .= 'User-Agent: ' . $agent . "\r\n"; 
    $request .= "Connection: Close\r\n\r\n"; 
    fwrite($sock, $request);
    $response = '';
    while(!feof($sock)) $response .= fread($sock, 8192);
    fclose($sock);
    
    print_r($response);

    if (preg_match('/^Location: (.+?)$/m', $response, $matches)){
        if ( substr($matches[1], 0, 1) == "/" )
            return $url_parts['scheme'] . "://" . $url_parts['host'] . trim($matches[1]);
        else
            return trim($matches[1]);

    } else {
        return false;
    }

}

function get_meta_redirect_target($url, $agent, $http_code = 0)
{
  // echo get_redirect_url($url, $agent); die;
  global $arraylinks; 
  $arraylinks[] = array($http_code => $url);
  $baseurl_host = '';
  $baseurl_parts = @parse_url($url);
  if (!$baseurl_parts) return false;
  if (isset($baseurl_parts['host'])){
    $baseurl_host = $baseurl_parts['host'];
  }
  
  $ip  = '18.197.83.249'; // trying to spoof ip..

  $header[0]  = "Accept: text/xml,application/xml,application/xhtml+xml,"; 
  $header[0] .= "text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5";

  $header[] = "Cache-Control: max-age=0"; 
  $header[] = "Connection: keep-alive"; 
  $header[] = "Keep-Alive: 300"; 
  $header[] = "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7"; 
  $header[] = "Accept-Language: en-us,en;q=0.5"; 
  $header[] = "Pragma: "; // browsers = blank
  $header[] = "X_FORWARDED_FOR: " . $ip;
  $header[] = "REMOTE_ADDR: " . $ip;
  // $header[] = "Host: affilitest.com";

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_HEADER, 0);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt( $ch, CURLOPT_HTTPHEADER, $header);
  curl_setopt($ch, CURLOPT_USERAGENT, $agent);    
  
  $contents = curl_exec($ch);
  $resp = curl_getinfo($ch);
  // print_r($resp);

  if(!empty($resp['redirect_url'])){
    $errorCode = $resp['http_code']. ' Redirect';
    get_meta_redirect_target($resp['redirect_url'], $agent, $errorCode);
  }else{  
    if (isset($contents) && is_string($contents))
    {
      preg_match_all('/<[\s]*meta[\s]*HTTP-EQUIV="?REFRESH"?' . '[\s]*content="?[0-9]*;[\s]*URL[\s]*=[\s]*([^>"]*)"?' . '[\s]*[\/]?[\s]*>/si', $contents, $match);

      if(isset($match) && is_array($match) && count($match) == 2 && count($match[1]) == 1)
      {
        $redURL = '';
        $url_parts = @parse_url($match[1][0]);
        if (!$url_parts) return false;
        if (!isset($url_parts['host'])){
          $redURL = $baseurl_parts['scheme'] . "://" . $baseurl_parts['host'] .   str_replace("'", '' ,$match[1][0]);
        }else{
          $redURL = $match[1][0];
        }
        $redURL = str_replace('&amp;', '&', $redURL);
        return get_meta_redirect_target($redURL, $agent, 'Meta-Refresh');
      }
    } 
  }
    curl_close($ch);
}

  if(isset($_POST['submit'])){   
    $url = trim($_POST['url']);
    $device = trim($_POST['device']);

    if(strpos($url, 'http') !== false){
      $http_user_agent = $_SERVER['HTTP_USER_AGENT']; 

      $deviceOption = array(
        'android' => 'Mozilla/5.0 (Linux; Android 5.0; SM-G900P Build/LRX21T) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/72.0.3626.121 Mobile Safari/537.36',
        'window' => $http_user_agent,
        'iphone' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 11_0 like Mac OS X) AppleWebKit/604.1.38 (KHTML, like Gecko) Version/11.0 Mobile/15A372 Safari/604.1'
      );
      if($device != ''){
        $agent = $deviceOption[$device];
      }else{
        $agent = $http_user_agent;
      }
      get_meta_redirect_target($url, $agent);    
    }else{
      $error = "Given URL is not valid!";
    }
    unset($_POST['submit']);
  }

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title> Redirect Tracer </title>
  <link rel="shortcut icon" href="<?php echo 'http://'.$_SERVER["HTTP_HOST"].'/Report01/login/images/favaarth.png'; ?>" title="Favicon"/>
  <link rel="stylesheet" href="../tools/style.css" type="text/css" />
  <!-- Add jQuery library -->
  <script type="text/javascript" src="../tools/fancyBox/lib/jquery-1.10.1.min.js"></script>
  <script type="text/javascript" src="../tools/js/jquery-ui-1.8.2.custom.min.js"></script>
  <!-- Add fancyBox main JS and CSS files -->
  <script type="text/javascript" src="../tools/fancyBox/source/jquery.fancybox.js?v=2.1.5"></script>
  <link rel="stylesheet" type="text/css" href="../tools/fancyBox/source/jquery.fancybox.css?v=2.1.5" media="screen" />
  <link href="../tools/css/css.css" rel="stylesheet" type="text/css" />
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">
  <link href="../tools/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css" rel="stylesheet" />
  <link href="../tools/css/site.css" rel="stylesheet">
  <style type="text/css">
    .alert {
      padding: 6px 0 6px 18px !important;
    }
  </style>
</head>
<body>
    <?php 
      $pageTitle="Redirect Tracer"; 
      
    ?> 
    <div class="container">
      <div class="panel panel-default" style="margin-top: 15px;">
          <div class="panel-heading" style="padding: 10px; background-color: #fff;"> 
              <i class="fa fa-line-chart"></i> Redirect Tracer
          </div>
          <div class="panel-body" style="padding: 20px"> 
            
            <form name="myForm" class="form-inline" action="" method="post" enctype="multipart/form-data" style="margin-bottom: 45px;">              
              <div class="form-group col-md-12">      
                   <select name="device" style="width: 12%;">
                    <option value=""> Choose Device </option>
                    <option value="android" <?=(isset($device) && $device== 'android') ? 'selected': '';?> > Android </option>
                    <option value="window" <?=(isset($device) && $device== 'window') ? 'selected': '';?>> Window </option>
                    <option value="iphone" <?=(isset($device) && $device== 'iphone') ? 'selected': '';?>> iPhone </option>
                  </select>            
                  <input type="text" class="form-control" name="url" value="<?=isset($_POST['url'])?$_POST['url'] : '';?>" style="width: 80%;" placeholder="Please enter url" />
                  <button type="submit" name="submit" class="btn btn-primary" style="margin-top: 2px;"> Submit </button>  
              </div>             
            </form>
            <?php 
            if(isset($arraylinks) && !empty($arraylinks)) { ?>
              <div class="row" style="padding: 20px;">
                <div class="col-md-12">                 
                  <div class="alert alert-info">
                    <h2> Trace Results:  </h2>
                    <?php 
                      foreach ($arraylinks as $key => $value) {
                        
                        foreach ($value as $k => $v) {
                          if($k != '0'){
                            echo '<br/><strong>'. $k .' <i class="fa fa-2x fa-long-arrow-down" aria-hidden="true"></i> </strong>';
                          }
                          echo '<p style="word-break: break-all; font-size: 16px; color: #514d6a; font-family: Nunito Sans, sans-serif!important; line-height: 1.7">'. $v .'<p>';
                          
                        }
                      }
                      echo '<p><button class="btn btn-primary">Finished</button></p>';
                    ?>
                  </div>
                </div>
              </div>  
            <?php } ?>
            <?php if(isset($error)){ ?>
                <div class="alert alert-danger ">
                <button type="button" class="close close-sm" data-dismiss="alert">
                  <i class="fa fa-times"></i>
                </button>
                <?php echo $error;?>
              </div>
            <?php } ?>
        </div>
      </div>
  </div>
</div>
</body>
</html>


