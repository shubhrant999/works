<?php


  $ch = curl_init('https://xml-console.xapads.com/admin/auth?login=girish_xapads&password=Xapads@282');
  curl_setopt($ch,CURLOPT_HTTPHEADER,array('Content-Type: application/json'));
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $token = curl_exec($ch);
  curl_close($ch);


  echo $token;


