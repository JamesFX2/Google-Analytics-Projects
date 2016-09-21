<?php 
 

function get_Analytics_ID()
{
	// reads universal Analytics cookie, gets the number. 
	if(!isset($_COOKIE['_ga'])) {
		return "n/a";
	}
	else {	
		$temp = filter_var_array(explode(".",preg_replace("/[^0-9\.]/","",$_COOKIE['_ga'])),FILTER_VALIDATE_INT);
		return $temp[2].".".$temp[3];
	}
}

// Handle the parsing of the _ga cookie or setting it to a unique identifier
function gaParseCookie() {
	//new user or not
  if (isset($_COOKIE['_ga'])) {
    
	$cid = get_Analytics_ID();
  }
  else $cid = gaGenUUID();
  return $cid;
}

function gaGenUUID() {
// creates a cookie indicating a new user
  $cid = rand(1000000000,2147483647).'.'.$time;
  $ga = "GA1.2.".$cid;
  setcookie('_ga', $ga, $time+63115200, '/', "sofaworks.co.uk",false,false);
  return $cid;
}

function gaBuildHit( $method = null, $info = null ) {
  if ( $method && $info) {

  // Standard params
  $v = 1;
  $tid = "UA-45934115-1"; // Analytics ID in here
  $cid = gaParseCookie();
  $current = explode(".",$_SERVER['REQUEST_URI']);
  
    
  // Register a PAGEVIEW
  if ($method === 'pageview') {
	  
	$ul = explode(',',$_SERVER['HTTP_ACCEPT_LANGUAGE']);
	$ul = explode(';',$ul[0]); $ul = $ul[0] === "" ? false : strtolower($ul[0]);
	
	//echo temp[0];die;
    // Send PageView hit
    $data = array(
      'v' => $v,
      'tid' => $tid,
      'cid' => $cid,
	  'ds' => 'web',
      't' => 'pageview',
      'dt' => $info['title'],
	  'dl'=>rawurlencode('http://www.sofaworks.co.uk/'.str_replace('migration','',str_replace('/tracker/','/',$current[0]))),
	  'dh'=>rawurlencode($_SERVER['SERVER_NAME']),
      'dp' => str_replace('migration','',str_replace('/tracker/','/',$current[0])),
	  'uip' => $_SERVER['REMOTE_ADDR'],
	  'ua' => rawurlencode($_SERVER['HTTP_USER_AGENT']),
	  'z'  =>rand(1000000000,2147483647)  
    );
	if($ul)
		$data["ul"] = rawurlencode($ul);
	if($_SERVER['HTTP_REFERER'])
		$data["dr"] = rawurlencode($_SERVER['HTTP_REFERER']);
 
	//print_r($data); die;
	//test - seems fine
    gaFireHit($data);

  } // end pageview method

  // Register an ECOMMERCE TRANSACTION (and an associated ITEM)
  else if ($method === 'ecommerce') {

    // Set up Transaction params
    $ti = uniqid(); // Transaction ID
    $ta = 'SI';
    $tr = $info['price']; // transaction value (native currency)
    $cu = $info['cc']; // currency code

    // Send Transaction hit
    $data = array(
      'v' => $v,
      'tid' => $tid,
      'cid' => $cid,
      't' => 'transaction',
      'ti' => $ti,
      'ta' => $ta,
      'tr' => $tr,
      'cu' => $cu
    );
    gaFireHit($data);

    // Set up Item params
    $in = urlencode($info['info']->product_name); // item name;
    $ip = $tr;
    $iq = 1;
    $ic = urlencode($info['info']->product_id); // item SKU
    $iv = urlencode('SI'); // Product Category - we use 'SI' in all cases, you may not want to

    // Send Item hit
    $data = array(
      'v' => $v,
      'tid' => $tid,
      'cid' => $cid,
      't' => 'item',
      'ti' => $ti,
      'in' => $in,
      'ip' => $ip,
      'iq' => $iq,
      'ic' => $ic,
      'iv' => $iv,
      'cu' => $cu
    );
    gaFireHit($data);

  } // end ecommerce method
 }
}

// See https://developers.google.com/analytics/devguides/collection/protocol/v1/devguide
function gaFireHit( $data = null ) {
  if ( $data ) {
    $getString = 'https://ssl.google-analytics.com/collect';
    $getString .= '?payload_data&';
    $getString .= http_build_query($data);
    $result = file_get_contents( $getString );
	$result = imagecreatefrompng('tracker.png');

    #$sendlog = error_log($getString, 1, "ME@EMAIL.COM"); // comment this in and change your email to get an log sent to your email
    header("Content-type: image/png");
    return imagepng($result);
  }
  return false;
}

$data = array(
  'title' => 'Migration Pageview',
  'slug' => ''
);
gaBuildHit( 'pageview', $data);

?>