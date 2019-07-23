<?php

    ini_set("auto_detect_line_endings", true);
    require __DIR__ . '/../vendor/autoload.php';

	$airbrakeid	    = getenv('AIRBRAKE_ID'); 
	$airbrakekey	= getenv('AIRBRAKE_KEY'); 

    $notifier = new Airbrake\Notifier(array(
        'projectId' => $airbrakeid,
        'projectKey' => $airbrakekey
    ));

    Airbrake\Instance::set($notifier);

    $handler = new Airbrake\ErrorHandler($notifier);
    $handler->register();

    
	date_default_timezone_set('America/New_York');
	$currenttime = date( 'Y-m-d H:i:s', time() );
    $timenow = date('H:i:s',time());

    //------------------------------------------------------
    // Get Environment Variables and include supplemental files
    //------------------------------------------------------ 

	$admin0 	    = getenv('ADMIN_0');   // set this to your admin phone number
    $url            = getenv('JAWSDB_URL');
	$twinum         = getenv('TWILIO_NUMBER');  // Set this to your twilio number

    if (getenv("DISABLE_WEB") !== false)
        $disable_web = getenv("DISABLE_WEB") ;
    else
        $disable_web = true ;

    if (getenv("HOUSENAME") !== false)
        $housename = getenv("HOUSENAME") ;
    else
        $housename = 'Domotron' ;

    if (getenv("MYNAME") !== false)
        $adminname = getenv("MYNAME") ;
    else
        $adminname = 'Piotr Skut' ;

    if (getenv("NONADMIN_NUMBER") !== false)
        $nonadmin = getenv("NONADMIN_NUMBER") ;
    else
        $nonadmin = '+15555555555' ;

    require('pres/functions.php');
    require('pres/webhooks.php');
    require('pres/twilio.php');
    require('pres/get_light.php');

    //------------------------------------------------------
    // Check for Browser and URL Parameters
    //------------------------------------------------------ 

	if (isset($_SERVER['HTTP_USER_AGENT']) && is_browser($_SERVER['HTTP_USER_AGENT'])){  // if browser
    
        if (!$disable_web){  // if browser and web not disabled
            $web         = true ;
    		$body 		 = isset($_GET["Body"]) ? htmlspecialchars($_GET["Body"]) : "Body not set.";
            $sender 	 = isset($_GET["Sender"]) ? htmlspecialchars($_GET["Sender"]) : "+15555555555";
		    require_once('pres/header.php');

            if (isset($_GET["fire"]) && $_GET["fire"] == 'on')  // if browser and fire hook selected
		          $fire = true;
            else  $fire = false;

            if (isset($_GET["usetwilio"]) && $_GET["usetwilio"] == 'on')  // if browser and usetwilio
                  $usetwilio = true;
            else  $usetwilio = false;

            if (isset($_GET["debug"]) && $_GET["debug"] == 'on')  // if browser and debug
                  $debug = true;
            else  $debug = false;


        }
        else {  // if browser detected and web disabled
            $body = '';
            $sender = '';
            $web  = false ;
            $fire = false ;
            $usetwilio = false ;
            $debug = false ;
            echo "Disable web set to ".$disable_web ;
        }
    }

    else {
        $fire       = true ;
        $usetwilio  = true ;
        $web        = false ;
        $debug      = false ;
		$body 		= $_REQUEST['Body'];  // read message into string
		$sender		= $_REQUEST['From'];
    }


    $sunrise = get_sunrise();
    $sunset = get_sunset();

    if ($timenow > $sunrise && $timenow < $sunset )
        $daylight = true;
    else
        $daylight = false;

    $dbparts = parse_url($url);

    $hostname = $dbparts['host'];
    $username = $dbparts['user'];
    $password = $dbparts['pass'];
    $database = ltrim($dbparts['path'],'/');

//    $body   = 'null';
//    $sender = '+15555555555';
	$admin  = false ;					    // user is admin
	$lockit = false ;					// locking door
	$notnew = false ;					// user is not new to the system
    $adminisadminning = false ;

	//------------------------------------------------------
	// Database Connect and Read Table
	//------------------------------------------------------ 

 try {
    $pdo = new PDO("mysql:host=$hostname;dbname=$database", $username, $password);
        // set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    if ($web == 1) echo "Database Connected&nbsp;<i color=green class=\"fas fa-check fa-1x\"></i><br><br> ";
    }
catch(PDOException $e)
    {
    if ($web == 1) echo "Database Connection:&nbsp;<i color=red class=\"fas fa-times fa-1x\"></i><br><br> " . $e->getMessage();
        send_message ($web,$usetwilio,$admin0,$twinum,'Database issues');
    Airbrake\Instance::notify($e);
    }   

	$all = array();
	$numbers = array();
	$sql = $pdo->query('SELECT * FROM visitors');
	while ($row = $sql->fetch())
		{		$all[] = $row;
		 		$numbers[] = $row['PhoneNum'];		}
	$n = sizeof($all);
	
	//------------------------------------------------------
	// Look up the sender's code and access end time in the database and check them against the incoming code and the current time
	//------------------------------------------------------ 

	if (in_array($sender,$numbers))
 		$sendernotnew = true ;
 	else
 		$sendernotnew = false ;	

	$key = searchhaystack($all,'PhoneNum',$sender); // $key = 2;

	if ($sendernotnew){
		$thisuserscode = $all[$key]['AccessCode'];
		$thisusersendtime = $all[$key]['EndAccess'];
		$thisusersname = $all[$key]['FirstName'];
		}
	else {
		$thisuserscode = null;
		$thisusersendtime = null;
		$thisusersname = 'friend';
	}

	if (stripos($body,(string)$thisuserscode) !== false) $codechecksout = true ;
	else $codechecksout = false ;

	$temp0 = new DateTime($thisusersendtime);
	$temp1 = new DateTime($currenttime);

	if ( $temp1 < $temp0 ) $timechecksout = true ;
	else $timechecksout = false ;

	//------------------------------------------------------
	// Analyze the Incoming Text Message
	//------------------------------------------------------ 

    if (stripos($body,'unlock') !== false)         // check/clean unlock so don't trigger search for 'lock'
        $body = str_ireplace('unlock','',$body);

    if ((stripos($body,'lock') !== false)) $lockit = true ; 	

    if ($sender == $admin0) $admin = true ;

	if ($admin && stripos($body,'allow') !== false ){
		$adminisadminning = true ;
        $newstring = str_ireplace('allow','',$body);	// remove allow and write to string
        $matchnum = preg_match('/\d{10}/u', $newstring, $matches);	// find first 10 digits
        $newusernum = $matches[0];									// assign to user number
        $newstring = str_replace($newusernum,'',$newstring);		// clean phone number
        $matchname = preg_match('/[a-zA-Z]+/', $newstring, $catches);	// find name
        if(!empty($catches[0]))
            $newusername = $catches[0];
        else
            $newusername = 'friend';
        $newstring = str_replace(' ','',$newstring);            // clean spaces
        $newstring = str_replace($newusername,'',$newstring);	// clean name
        $daysallowed = $newstring;						
        $good = ctype_digit($newstring) && $matchnum ;		    // make sure what is left is a number
        $newusernum = "+1".$newusernum;
        $notnew = in_array($newusernum,$numbers);

        $new_code = random_text( $type = 'distinct', $length = 5 );
        $enddate = date( 'Y-m-d H:i:s', time() + (24*3600*$daysallowed) );			
  	}

	//------------------------------------------------------
	// Display Code
	//------------------------------------------------------ 

	if ($web && $debug){

	echo "<div class=\"container bg-light\" .bg-warning>";
    echo "<br><h3>Debug Mode</h3>";
	echo "<br>Sender: ".$sender."</br>";
	echo "Body: ".$body."</br>";
	echo $admin ? "Admin: Yes" : "Admin: No"; 
    echo "</br></br>";	

		if ($adminisadminning){
			echo "Match Number: ".$matchnum."</br>";
			echo "Match Name: ".$matchname."</br>";
			echo "New User Name: ".$newusername."</br>";	
			echo "New User Number: ".$newusernum."</br>";
			echo "Days Allowed: ".$daysallowed."</br>";
			echo "Checks out: ".$good."</br></br>";	
			echo "Not New User: ".$notnew."</br>";
		}
		else{
			echo "This sender's name is ".$thisusersname."</br>";
			if (!$admin) echo "This sender's end time is ".$thisusersendtime."</br>";
            
			if (!$admin) echo $sendernotnew ? "This sender is not new<br>" : "The sender is new</br>";
			if (!$admin) echo "Their access code is ".$thisuserscode."</br>";
			if (!$admin) echo "Their access expires at ".date("g:i a", strtotime($thisusersendtime))." on ".date("F d, Y", strtotime($thisusersendtime))."</br>";		
			if (!$admin) echo $codechecksout ? "The access code is a match <i color=green class=\"fas fa-check fa-1x\"></i></br>" : "The access code check failed <i color=red class=\"fas fa-times fa-1x\"></i></br>";
			if (!$admin) echo $timechecksout ? "Date checks out <i color=green class=\"fas fa-check fa-1x\"></i></br>" : "Date check failed <i color=red class=\"fas fa-times fa-1x\"></i></br>";
		}

	echo "<br>The server time is ".date("g:i a", strtotime($timenow))."</br>";
	echo "Sunrise today: ".date("g:i a", strtotime($sunrise))."</br>";
	echo "Sunset today: ".date("g:i a", strtotime($sunset))."</br></br>";
    if ($daylight) echo "It's light outside! <i color=orange class=\"fas fa-sun fa-1x\"></i><br><br>";
    else echo "It's dark outside! <i color=black class=\"fas fa-moon fa-1x\"></i><br><br>";
	if ($usetwilio) echo "Using Twilio</br>";
	echo "</div><br>";
    echo "<h3>Webhook & SMS Activity</h3>";
	} 

	//------------------------------------
	// 						Lock Door
	//------------------------------------

	if ( $lockit && ( ($admin && !$adminisadminning) || (!$admin && $codechecksout && $timechecksout) ) ){	

		$mssgtosendr = "I've locked the door ".$thisusersname;
		$mssgtoadmin = $thisusersname." ".$sender." locked the door";

        smartthings_webhook ($fire, 'lock');
        send_message ($web,$usetwilio,$sender,$twinum,$mssgtosendr);
        if (!$admin) send_message ($web,$usetwilio,$admin0,$twinum,$mssgtoadmin);
        
        $sql = "UPDATE visitors SET HasBeen = :hasbeen, LastUse = :currenttime WHERE PhoneNum = :phonenum";
		$pdo->prepare($sql)->execute(['hasbeen' => '1', 'currenttime' => $currenttime, 'phonenum' => $sender]);
    
	}

	//--------------------------------------------------------
	// 						Open Door
	//--------------------------------------------------------

	elseif ( ( ($admin && !$adminisadminning) || (!$admin && $codechecksout && $timechecksout) ) ){
	// OPEN THE DOOR AND SEND CONFIRMATION

		$mssgtosendr = "Code accepted ".$thisusersname.". If the door hasn't unlocked after a few seconds, please try again.";

		$mssgtoadmin = $thisusersname." ".$sender." unlocked the door";	
        if (!$daylight) ifttt_webhook($fire,'porch_lights_on');
        
		smartthings_webhook($fire,'unlock');
        send_message ($web,$usetwilio,$sender,$twinum,$mssgtosendr);
        if (!$admin) send_message ($web,$usetwilio,$admin0,$twinum,$mssgtoadmin);
        
        $sql = "UPDATE visitors SET HasBeen = :hasbeen, LastUse = :currenttime WHERE PhoneNum = :phonenum";
		$pdo->prepare($sql)->execute(['hasbeen' => '1', 'currenttime' => $currenttime, 'phonenum' => $sender]);

    }

	//----------------------------------------------------------------
	// 						Add Update New User - Run Admin Tasks
	//----------------------------------------------------------------

	elseif ($adminisadminning){
		if ($good){

			if (!$notnew){
				if($web) echo "ADDING NEW USER <br>";
				$sql = "INSERT INTO visitors (FirstName, PhoneNum, StartAccess, EndAccess, AccessCode) VALUES (:newusername, :newusernum, :currenttime, :enddate, :newcode )";
				$pdo->prepare($sql)->execute(['newusername' => $newusername, 'newusernum' => $newusernum, 'currenttime' => $currenttime, 'enddate' => $enddate, 'newcode' => $new_code]);
			}
			else{
				if($web) echo "UPDATING EXISTING USER <br>";

				$sql = "UPDATE visitors SET FirstName = :newusername, StartAccess = :currenttime, EndAccess = :enddate, AccessCode = :newcode WHERE PhoneNum = :newusernum";
				$pdo->prepare($sql)->execute(['newusername' => $newusername, 'newusernum' => $newusernum, 'currenttime' => $currenttime, 'enddate' => $enddate, 'newcode' => $new_code]);
			}

			if ($daysallowed == 1)
                $mssgtonuser = "Hi ".$newusername.". I am ".$housename." - ".$adminname."'s smart home. Access granted for 24 hours. Text ".$new_code." back to me to unlock the door. Add the word lock to lock it.";
			else
                $mssgtonuser = "Hi ".$newusername.". I am ".$housename." - ".$adminname."'s smart home. Access granted for ".$daysallowed." days. Text ".$new_code." to me to unlock the door. Add the word lock to lock it.";

			$mssgtosendr = "Gave ".$newusername." ".$newusernum." ".$daysallowed." days";

				if ($daysallowed != 0){
                    send_message ($web,$usetwilio,$newusernum,$twinum,$mssgtonuser);
					send_message ($web,$usetwilio,$sender,$twinum,$mssgtosendr);
			     }	
	   }	
		
		 	// TELL AN ADMIN USER THEY HAVE MADE A FORMATTING ERROR
        else {
            $mssgtosendr = "Format is: allow Name [ph number] [days] admin = ".$admin." Good = ".$good ;
		    send_message ($web,$usetwilio,$sender,$twinum,$mssgtosendr);
			}
	}

	//--------------------------------------------------------------------------------
	// 						HANDLE NON-ADMIN ERROR CASE 1 :- RIGHT TIME, WRONG CODE
	//--------------------------------------------------------------------------------

	elseif ($sendernotnew && $timechecksout && !$codechecksout) {
		$mssgtosendr = "I'm sorry, did you forget your code ".$thisusersname."?";
		$mssgtoadmin = $thisusersname." ".$sender." is having code problems";	
		send_message ($web,$usetwilio,$sender,$twinum,$mssgtosendr);
		send_message ($web,$usetwilio,$admin0,$twinum,$mssgtoadmin);
 	}
	
	//--------------------------------------------
	// 						HANDLE NON-ADMIN ERROR CASE 2 : WRONG TIME
	//--------------------------------------------

	elseif ($sendernotnew && !$timechecksout ) {
		$mssgtosendr = "I'm sorry ".$thisusersname.", it looks like your key has expired. :'(";
		$mssgtoadmin = $thisusersname." ".$sender." just tried to get in with an expired key";	
		send_message ($web,$usetwilio,$sender,$twinum,$mssgtosendr);
		send_message ($web,$usetwilio,$admin0,$twinum,$mssgtoadmin);
	}

	//----------------------------------------------
	// 						NEW USER
	//----------------------------------------------

	else {

		$mssgtosendr = "New phone who dis?";
		$mssgtoadmin = "Knock knock! ".format_phone($sender)." is texting, randomly." ;	
		send_message ($web,$usetwilio,$sender,$twinum,$mssgtosendr);
		send_message ($web,$usetwilio,$admin0,$twinum,$mssgtoadmin);
	}


	if ($web){

        echo "<br><br><h3>User Table</h3>";
        echo "<table class=\"table\"><thead class=\"thead-dark\"><tr><th scope=\"col\">Name</th><th scope=\"col\">Number</th><th scope=\"col\">Code</th><th scope=\"col\">End Access</th><th scope=\"col\">Has Been</th></tr>";
		$sql = $pdo->query('SELECT * FROM visitors');
		while ($row = $sql->fetch())
			{		//$all[] = $row;
                    echo "<tr>";
                    echo "<td>" . $row['FirstName'] . "</td>";
                    echo "<td>" . $row['PhoneNum'] . "</td>";
                    echo "<td>" . $row['AccessCode']. "</td>";
                    echo "<td>" . date("F d, Y", strtotime($row['EndAccess'])) . "</td>";
                    echo "<td>" . $row['HasBeen'] . "</td>";
                    echo "</tr>";
            
        }		
        echo "</table>";
        require_once('pres/footer.php');
    } 

	

?>
