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

function u_perm($usertype){

    switch ($usertype) {
        case 1:
            return "28 Preston User";
        case 2:
            return "26 Preston User";
        case 3:
            return "Ellery User";
        case 4:
            return "Ellery Admin";
        case 5:
            return "28 Preston Admin";
        case 6:
            return "26 Preston Admin";
        case 8:
            return "Superuser";
    }
}


function smartthings_webhook($fire = true, $which_lock = 1, $event = 'event'){

if ($fire){
    $key = getenv('SMARTTHINGS_KEY');
    $device1 = getenv('AUGUST_DEVICE_ID');
    $device2 = getenv('AIRBNB_DEVICE_ID');
    $device3 = getenv('ELLERY_DEVICE_ID');

    if ($which_lock == 3)
        $url = 'https://api.smartthings.com/v1/devices/'.$device3.'/commands';
    else if ($which_lock == 2)
        $url = 'https://api.smartthings.com/v1/devices/'.$device2.'/commands';
    else
        $url = 'https://api.smartthings.com/v1/devices/'.$device1.'/commands';

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
    if ($which_lock == 1){
        echo "(Would have) sent ".$event." command to 28 Preston lock</br></br>";
        $response = '';
    }
    else if ($which_lock == 2){
        echo "(Would have) sent ".$event." command to 26 Preston lock</br></br>";
        $response = '';
    }
    else if ($which_lock == 3){
        echo "(Would have) sent ".$event." command to Ellery lock</br></br>";
        $response = '';
    }
    else {
        echo "Lock selection error</br></br>";
        $response = '';
    }
    }

return $response ;

}    
?>

