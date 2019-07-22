<?php

ini_set("auto_detect_line_endings", true);
	date_default_timezone_set('America/New_York');
    $timenow = date('H:i:s',time());

    require('pres/webhook.php');
    require('pres/get_light.php');

    $url = getenv('JAWSDB_URL');
    $dbparts = parse_url($url);

    $hostname = $dbparts['host'];
    $username = $dbparts['user'];
    $password = $dbparts['pass'];
    $database = ltrim($dbparts['path'],'/');
    $database = ltrim($dbparts['path'],'/');

 try {
    $pdo = new PDO("mysql:host=$hostname;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Database Connection:&nbsp;<i color=green class=\"fas fa-check fa-1x\"></i><br><br> ";
    }
catch(PDOException $e)
    {
    echo "Database Connection:&nbsp;<i color=red class=\"fas fa-times fa-1x\"></i><br><br> " . $e->getMessage();
        send_message ($web,$usetwilio,$james,$twinum,'Database issues');
    }   

    $sunrise = get_sunrise();
    $sunset = get_sunset();

    if ($timenow > $sunrise && $timenow < $sunset )
        $daylight = true;
    else
        $daylight = false;

    if (!$daylight){
        ifttt_webhook(true,'porch_lights_on');
        ifttt_webhook(true,'flossy_on');
        
    	$sql = "UPDATE status SET State = :newstate WHERE Name = :name";
				$pdo->prepare($sql)->execute(['newstate' => '1', 'name' => 'motion']);
    sleep(360);
        
    ifttt_webhook(true,'porch_lights_off');
    ifttt_webhook(true,'flossy_off');
	$sql = "UPDATE status SET State = :newstate WHERE Name = :name";
				$pdo->prepare($sql)->execute(['newstate' => '0', 'name' => 'motion']);
    }

    if ($daylight){
        ifttt_webhook(true,'porch_lights_off');
        ifttt_webhook(true,'flossy_off');
    }
    http_response_code(200);

?>

