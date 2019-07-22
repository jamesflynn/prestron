<?php
    ini_set("auto_detect_line_endings", true);
	date_default_timezone_set('America/New_York');
    $timenow = date('H:i:s',time());
    require('pres/functions.php');
    require('pres/webhook.php');

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
 }
 catch(PDOException $e)
    {
     $e->getMessage();
    } 

	$all = array();
	$sql = $pdo->query('SELECT * FROM status');
	while ($row = $sql->fetch())
		{		$all[] = $row;   }

	$key = searchhaystack($all,'Name','motion'); // $key = 2;

    $motion = $all[$key]['State'];
    
    if (!$motion) fire_webhook('porch_lights_off');
    else { sleep(360);
            fire_webhook('porch_lights_off');
         }
    
?>

