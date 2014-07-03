<?
   require_once("httpconnection.php");
   
   
   /* Exception 클래스 */
   class PayAtAPIException extends Exception
   {

	  protected $result;
	
	  public function __construct($result) {

	    $this->result = $result;
	    $msg = '알수 없는 응답에러입니다';
	    $code = 400;
	    
	    if (is_object($result)) {
	    	$code = isset($result->code) ? $result->code : 400;
		    if (isset($result->message)) {
		      $msg = $result->message;
		    } 
	    }else if (is_string($result)) {
	    	$msg = $result;
	    }
	    
	
	    parent::__construct($msg, $code);
	  }
	
	  public function getResult() {
	    return $this->result;
	  }
		 
	  public function __toString() {
	    $str = "";
	    if ($this->code != 0) {
	      $str .= $this->code . ': ';
	    }
	    return $str . $this->message;
	  }
   }
   
   
   class PayAtAPI {
     	var $protocol = "http";
     	var $host = "dev.kkokjee.com";
     	var $access_token = "";
     	var $client_id = "";
     	var $client_secret = "";
     	
     	function __construct($client_id,$client_secret) {
     	
     		$this->protocol = "https";
     		$this->host = "www.kkokjee.com";
     		$this->client_id = $client_id;
     		$this->client_secret = $client_secret;
     	}
     	
		
		function get_access_token() {
			if ($this->access_token != "") return true;
			$http = new HttpConnection(
			    "POST",
				$this->protocol."://".$this->host."/oauth/v1/authorization.json",
				array(
					"client_id"=>$this->client_id,
					"client_secret"=>$this->client_secret
				)
			);
			try {
				$json = json_decode($http->request(true));
				if ($json->code != 200) {
					throw new PayAtAPIException($json);
				}
				$this->access_token = $json->data->access_token;	
				
			}catch(Exception $e) {
			   throw new PayAtAPIException($e->getMessage());
			}
			return true;
		}
     	function api($endpoint,$params=array()) {
     		if ($this->access_token == "") {
     			$this->get_access_token();
     		}
     		
     		$mode = "post";
     		
     		foreach($params as $key=>$value) {
     			if (strpos($value,"@") !== false) {
     				$mode = "upload";
     				break;
     			}	
     		}
     		
     		$params["access_token"] = $this->access_token;
     		
     		$http = null;
     		
     		if ($mode == "post") {
	     		$http = new HttpConnection(
				    "post",
					$this->protocol."://".$this->host.$endpoint,
					$params
				);
			}else {
				$http = new HttpConnection(
				    "upload",
					$this->protocol."://".$this->host.$endpoint,
					$params,
					false
				);
			}
			
			$text = $http->request(true);
			$result = null;
			$ext = $http->get_file_ext($endpoint);
			if ($ext == "json") {
				$result = json_decode($text);
			}else if ($ext == "xml") {
				$result = simplexml_load_string($text);
			}
			
			return $result;
     	}
   }
   

?>