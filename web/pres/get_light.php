<?php
/*
function get_light(){
$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => "https://api.sunrise-sunset.org/json?lat=42.389118&lng=-71.097153&date=today&formatted=0",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "GET",
));

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);
$decode = json_decode($response,true);


if ($err) 
  $output = "unknown";
 else 
  $output = $decode;

    return $output;
}
*/
function get_sunrise(){
    $lines = file('pres/sunrise.txt');
    $day = date('z');
    $hhmm = $lines[$day];
    $hh = substr($hhmm, 0, 2);
    $mm = substr($hhmm, -3,2);
    return date("H:i:s",mktime($hh,$mm,00));
}

function get_sunset(){
    $lines = file('pres/sunset.txt');
    $day = date('z');
    $hhmm = $lines[$day];
    $hh = substr($hhmm, 0, 2);
    $mm = substr($hhmm, -3,2);
    return date("H:i:s",mktime($hh,$mm,00));
}

?>