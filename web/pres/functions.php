<?php

function five_letter_word(){
	$lines = file('sgb-words.txt');
	$index = rand(1,sizeof($lines));
	return trim(strtoupper($lines[$index]));
}

function random_text( $type = 'alnum', $length = 8 )
{
	switch ( $type ) {
		case 'alnum':
			$pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
			break;
		case 'alpha':
			$pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
			break;
		case 'hexdec':
			$pool = '0123456789abcdef';
			break;
		case 'numeric':
			$pool = '0123456789';
			break;
		case 'nozero':
			$pool = '123456789';
			break;
		case 'distinct':
			$pool = '2345679ACDEFHJKLMNPRSTUVWXYZ';
			break;
		default:
			$pool = (string) $type;
			break;
	}


	$crypto_rand_secure = function ( $min, $max ) {
		$range = $max - $min;
		if ( $range < 0 ) return $min; // not so random...
		$log    = log( $range, 2 );
		$bytes  = (int) ( $log / 8 ) + 1; // length in bytes
		$bits   = (int) $log + 1; // length in bits
		$filter = (int) ( 1 << $bits ) - 1; // set all lower bits to 1
		do {
			$rnd = hexdec( bin2hex( openssl_random_pseudo_bytes( $bytes ) ) );
			$rnd = $rnd & $filter; // discard irrelevant bits
		} while ( $rnd >= $range );
		return $min + $rnd;
	};

	$token = "";
	$max   = strlen( $pool );
	for ( $i = 0; $i < $length; $i++ ) {
		$token .= $pool[$crypto_rand_secure( 0, $max )];
	}
	return $token;
}

function send_message ($web = false, $usetwilio = false, $to ='null',$from = 'null' ,$body = 'null'){

    $ApiVersion = "2010-04-01";
    $AccountSid = getenv('TWILIO_ID');
    $AuthToken = getenv('TWILIO_ACCESS_TOKEN');
    $client = new TwilioRestClient($AccountSid, $AuthToken);

	if ($web){
        // format message for web
        echo '<div class="row no-gutters"><div class="col"><pre>'.format_phone($from).' </pre><i class="fas fa-arrow-right fa-1x"></i><pre> '.format_phone($to).'</pre></div><div class="col-8">'.$body.'</div></div>';
    }
    
	if($usetwilio) {
        // just send text 
        $response = $client->request("/$ApiVersion/Accounts/$AccountSid/SMS/Messages", "POST", array( "To"   => $to,  "From" => $from, "Body" => $body ));
	}	

}  // close function

function format_phone($x='null'){

    $a = substr($x,2,-7);
    $b = substr($x,5,-4);
    $c = substr($x,8);
	$y = "(".$a.") ".$b."-".$c;

	return $y;
}

function searchhaystack($haystack, $field, $value)
{
   foreach($haystack as $key => $product)
   {
      if ( $product[$field] === $value )
         return $key;
   }
   return false;
}

function is_browser($user_agent)
{
    if (strpos($user_agent, 'Opera') || strpos($user_agent, 'OPR/')) return true;
    elseif (strpos($user_agent, 'Edge')) return true;
    elseif (strpos($user_agent, 'Chrome')) return true;
    elseif (strpos($user_agent, 'Safari')) return true;
    elseif (strpos($user_agent, 'Firefox')) return true;
    elseif (strpos($user_agent, 'MSIE') || strpos($user_agent, 'Trident/7')) return true;
    
    return false;
}
?>