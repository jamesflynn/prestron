<?php  
function ifttt_webhook($fire = true, $event = 'event'){

if ($fire){
    $key = getenv('IFTTT_MAKER_KEY');

    $url ='https://maker.ifttt.com/trigger/'.$event.'/with/key/'.$key;
    $data = array("value1" => "payload" );
    $data_json = json_encode($data);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Content-Length: ' . strlen($data_json)));
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    curl_setopt($ch, CURLOPT_POSTFIELDS,$data_json);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response  = curl_exec($ch);
    curl_close($ch);
}
else {
        echo "(Would have) fired webook ".$event."</br></br>";
        $response = '';
    }

return $response ;

}

function smartthings_webhook($fire = true, $event = 'event'){

if ($fire){
    $key = getenv('SMARTTHINGS_KEY');
    $device = getenv('AUGUST_DEVICE_ID');

    $url = 'https://api.smartthings.com/v1/devices/'.$device.'/commands';

    $data = array(
        "commands" => array(array(
            "component" => "main",
            "capability" => "lock",
            "command" => $event
        ))
    );

    $data_json = json_encode($data);

    //    echo "<br>Key: ".$key."</br>";
    //	echo "<br>Device: ".$device."</br>";
    //	echo "<br>url: ".$url."</br>";

    //    $json_output = json_decode($data, JSON_PRETTY_PRINT); 
    //    print_r($data_json);    
    //    echo "<br><br>";

    $curl = curl_init();

    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_ENCODING, "");
    curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
    curl_setopt($curl, CURLOPT_TIMEOUT, 30);
    curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data_json);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array(
        "Accept: */*",
        "Authorization: Bearer ".$key,
        "Content-Type: application/json",
        "Cache-Control: no-cache",
        "Connection: keep-alive",
        "Host: api.smartthings.com",
        "accept-encoding: gzip, deflate",
        "cache-control: no-cache",
        'content-length: ' . strlen($data_json)
        ));

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
      echo "cURL Error #:" . $err;
    } else {
      echo $response;
    }

}
else {
        echo "(Would have) sent ".$event." command to lock</br></br>";
        $response = '';
    }

return $response ;


}    
?>

