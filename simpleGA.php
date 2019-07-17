<?php 

/* By Flacks */


class simpleGA {
	
	
	private $property = null;
	// e.g. UA-1667044-5
	private $cid = null;
	// Client ID for GA
	private $cookieName = "_ga";
	private $title = "Measurement Protocol";
	
	private $v = 1;
	private $ds = "MP";
	
	
	private $domain = null;
	private $host = null;
	
	
				
	public function __construct($property, $dataSource = null, $cookieName = null) { 
	
		$re = '/^UA\-[0-9]+\-[0-9]{1,3}$/i';
		
		$this->host = $_SERVER['HTTP_HOST'];
	
		if(preg_match($re, $property))
		{
			$this->property = $property;
		}
		
		if($cookieName && strlen($cookieName < 15)) {
			$this->cookieName = $cookieName;
		}
		
		if($dataSource && strlen($dataSource < 100)) {
			$this->ds = $dataSource;
		}
		
		
		$this->cid = $this->getCid();
		
		
		$protocol = stripos($_SERVER['SERVER_PROTOCOL'],'https') === 0 ? 'https://' : 'http://';
		
		$this->domain = $protocol . $this->host;
		
		
	}
	
	private function getCid() {
		
		if(!isset($_COOKIE[$this->cookieName])) {
			return $this->setCid();
		}
		else {	
		$temp = filter_var_array(explode(".",preg_replace("/[^0-9\.]/","",$_COOKIE[$this->cookieName])),FILTER_VALIDATE_INT);
		return $temp[2].".".$temp[3];
		}
		
		
		
		
	
	}
	
	private function setCid() {
		$time = time();
		$domain = $this->host;
		// creates a cookie indicating a new user
		$cid = rand(1000000000,2147483647).'.'.$time;
		$ga = "GA1.2.".$cid;
		setcookie($this->cookieName, $ga, $time+63115200, '/', $domain,false,false);
		return $cid;
		
		
	}
	
	public function pageView($url = false, $title = false, $override = null)
	{
	
		

		
		if(!$title)
		{
			$title = $this->title;
			
		}
		
		$title = substr($title,0,255);
		
		
		if(!$url)
		{
			$url = $_SERVER['REQUEST_URI'];
		}

   
		$data = array(
		  't' => 'pageview',
		  'dt' => $title,
		  'dl'=>rawurlencode($this->domain.$url),
		  'dh'=>rawurlencode($this->host),
		  'dp' => $url,
		  'uip' => $_SERVER['REMOTE_ADDR']
		);
	
 

		$this->sendHit($data, $override);
		
		
		
	}
	
	public function event($categoryObj, $url, $title = false, $override = null)
	{
		$defaults = array(	"ec" => "Default",
							"ea" => "Default",
							"el" => "Default",
							"ev" => false,
							"ni" => 1
						 );
		
		
		if(!$title)
		{
			$title = $this->title;
		}
		
		$title = substr($title,0,255);
		
		
		if(!$url)
		{
			$url = $_SERVER['REQUEST_URI'];
		}

		$data = array(
		  't' => 'event',
		  'dt' => $title,
		  'dl'=>rawurlencode($this->domain.$url),
		  'dh'=>rawurlencode($this->host),
		  'dp' => $url,
		  'uip' => $_SERVER['REMOTE_ADDR']
		);
		
		if($categoryObj && is_array($categoryObj))
		{
			foreach($defaults as $key => $value)
			{
				if(array_key_exists($key, $categoryObj))
				{
					$data[$key] = $categoryObj[$key];
				}
				else $data[$key] = $defaults[$key];	
			}
		}
		$this->sendHit($data, $override);
	}
	
	public function transaction($transObj, $itemsObj, $url, $title = false, $override = null)
	{
		
		// this one doesn't work yet and probably never will
		$defaults = array(	"ec" => "Default",
							"ea" => "Default",
							"el" => "Default",
							"ev" => false,
							"ni" => 1
						 );
		
		
		if(!$title)
		{
			$title = $this->title;
		}
		
		$title = substr($title,0,255);
		
		
		if(!$url)
		{
			$url = $_SERVER['REQUEST_URI'];
		}

		$data = array(
		  't' => 'event',
		  'dt' => $title,
		  'dl'=>rawurlencode($this->domain.$url),
		  'dh'=>rawurlencode($this->host),
		  'dp' => $url,
		  'uip' => $_SERVER['REMOTE_ADDR']
		);
		
		if($categoryObj && is_array($categoryObj))
		{
			foreach($defaults as $key => $value)
			{
				if(array_key_exists($key, $categoryObj))
				{
					$data[$key] = $categoryObj[$key];
				}
				else $data[$key] = $defaults[$key];	
			}
		}
		$this->sendHit($data, $override);
		
		
		
		
		
	}
	
	
	private function sendHit($data = null, $override = null) {
		if ($data) 
		{

			$ul = explode(',',$_SERVER['HTTP_ACCEPT_LANGUAGE']);
			$ul = explode(';',$ul[0]); $ul = $ul[0] === "" ? false : strtolower($ul[0]);
			$data["aip"] = 1;
			$data["v"] = $this->v;
			$data["tid"] = $this->property;
			$data["cid"] = $this->cid;
			$data["ds"] = $this->ds;
			$data["ua"] = rawurlencode($_SERVER['HTTP_USER_AGENT']);
			$data["z"] = rand(1000000000,2147483647);
			
			if($ul)
				$data["ul"] = rawurlencode($ul);
			if($_SERVER['HTTP_REFERER'])
				$data["dr"] = rawurlencode($_SERVER['HTTP_REFERER']);

			if($override && is_array($override))
			{
				foreach($override as $key => $value)
				{
					$data[$key] = $value;
				}
			}
			
			$getString = 'https://ssl.google-analytics.com/collect';
			$getString .= '?payload_data&';
			$getString .= http_build_query($data);
			$result = file_get_contents( $getString );
		}
		return false;
	}
	
}
