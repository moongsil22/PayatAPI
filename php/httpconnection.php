<?
	
	/*
	 * HTTP Connection 연결 클래스
	 */

	class HttpConnection {
		
		var $method;
		var $url ;
		var $data;
		var $cachedir;
		var $hash;
		var $usecookie;
		var $cookiefilename;

		var $info;
		var $html;
		var $error;
		var $errno;
		var $debug;
		var $cachetime;
		var $cacheFileName;

		static $referrer = "";
		
		
		/* 생성자 , GET/POST , URL , 인자들 */
		function __construct($method , $url , $params = array() , $encode = true) {
			$method = strtolower($method);

			if ($encode)
				$url = $this->url_encode($url);
			
			
			
			if ($method == "post") {
				
				
					
				if (is_array($params)) {
					$data = http_build_query($params);
					if ($encode)
						$data = $this->url_encode($data);
					
				}else $data = $params;

				$this->data = $data;
			}else if ($method == "upload") {
			
				$this->data = $params;
			}
			
			$this->url = $url;
			$this->method = $method;
			$this->cachedir = "cache".DIRECTORY_SEPARATOR;
		
			if (!is_dir($this->cachedir))
				mkdir($this->cachedir);
			
			$this->hash = md5($this->url.$this->data);

			if (HttpConnection::$referrer == "")
				HttpConnection::$referrer = "http://www.google.com/";
			else
				HttpConnection::$referrer = $this->url;
			
			$this->debug = false;
			$this->cachetime = 24 * 60 * 60;
			$this->usecookie = false;
		}
		
		/* 캐쉬 삭제 */
		function clear_cache() {
			$filename = $this->cachedir.$this->hash;
			@unlink($filename);
			@unlink($filename."_header");
		}
		
		/* HTTP 응답코드 얻기 */
		function get_http_code() {
			return $this->info['http_code'];
		}
		
		/* 파일 확장자 얻기 */
		function get_file_ext($filename)
		{
		      $tmp = explode(".", $filename);
		      $ext = trim($tmp[count($tmp)-1]);
		      if (strpos($ext,"?") !== false) {
		      	$ext = substr($ext,0,strpos($ext,"?"));
		      }
		      return strtolower($ext);
		}
		
		/* 파일 종류 얻기 */
		function get_file_type($filename)
		{
		      $tmp = explode(".", $filename);
		      $ext = trim($tmp[count($tmp)-1]);
		
		      $type_image = "jpg|jpeg|gif|png|ai|eps|psd";
		      $type_compress = "zip|alz|gz|tar|z|rar|ace|bz|bz2";
		      $type_text = "txt|text|rtf|2b|asp|php";
		      $type_html = "htm|html";
		      $type_hwp = "hwp|h30";
		      $type_exe = "exe";
		      $type_font = "ttf";
		      $type_movie = "avi|mpg|mpeg|mqv|asf|wmv|mov|swf|fla|ico";
		      $type_sound = "wav|mp3|mid|wma";
		      
		
		      if(preg_match("/($type_image)/i",$ext))
		              $file_type = 'image';
		      else if(preg_match("/($type_compress)/i",$ext))
		              $file_type = 'compress';
		      else if(preg_match("/($type_text)/i",$ext))
		              $file_type = 'text';
		      else if(preg_match("/($type_html)/i",$ext))
		              $file_type = 'html';
		      else if(preg_match("/($type_hwp)/i",$ext))
		              $file_type = 'hwp';
		      else if(preg_match("/($type_exe)/i",$ext))
		              $file_type = 'exe';
		      else if(preg_match("/($type_font)/i",$ext))
		              $file_type = 'font';
		      else if(preg_match("/($type_movie)/i",$ext))
		              $file_type = 'movie';
		      else if(preg_match("/($type_sound)/i",$ext))
		              $file_type = 'sound';
		      else
		              $file_type = 'unknown';
		
		      return $file_type;
		}
		
		
		/* REQUEST 실행하기 , force가 false면 자동으로 캐쉬 콘트롤 */
		function request($force=false,$headers=array()) {
			
			
			$filename = $this->cachedir.$this->hash;
			$this->cacheFileName = $filename;
			
			$use_cache = false;
			if (file_exists($filename) && !$force) {
				$mtime = filemtime($filename);
				$age = time() - $mtime;

				if ($age < $this->cachetime) {
					$use_cache = true;
					//24 hours before
				}
			}

			if ($use_cache) {
				
				
				$this->html = @file_get_contents($filename);
				$this->error = "";
				$this->errno = 0;
				$this->info = unserialize(@file_get_contents($filename."_header")); 

				if ($this->info['http_code'] != 200 && $this->info['http_code'] != 404) {
					$use_cache = false; //retrieve from online;
				}else {
					if ($this->info['url'] != $this->url) $this->url = $this->info['url'];
				}
			}
			
				
			if (!$use_cache) {

				


				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $this->url);
				curl_setopt($ch, CURLOPT_TIMEOUT, 10);
				curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10 );
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION  ,1); 
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
				curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; .NET CLR 1.1.4322; .NET CLR 2.0.50727)');
				curl_setopt($ch , CURLOPT_REFERER, HttpConnection::$referrer);
				curl_setopt($ch,  CURLOPT_SSL_VERIFYPEER, false);
				
				if (is_array($headers) && count($headers) > 0 ) {
					 curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); 
				}
				
				if ($this->usecookie) {
					curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookiefilename);
					curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookiefilename);
					curl_setopt($ch, CURLOPT_COOKIE, true);
    			}


				if ($this->method == "post" || $this->method == "upload") {
					
					curl_setopt($ch, CURLOPT_POST,true);
					curl_setopt($ch, CURLOPT_POSTFIELDS, $this->data);
					
					
				}

				$this->html = curl_exec($ch);
				$this->info = curl_getinfo($ch);
				$this->error = curl_error($ch);
				$this->errno = curl_errno($ch);
				
				
				if ($this->errno == 0 && $this->error == "" ) {
					
					if (!$force) {
						$this->cacheFileName = $filename;
						file_put_contents($filename,$this->html);
						$fp = fopen($filename."_header", 'w');
						fwrite($fp, serialize($this->info));
						fclose($fp); 
						@chmod($filename,0777);
						@chmod($filename."_header",0777);
					}
				}

				curl_close($ch);
				if ($this->info['url'] != $this->url) {
					
					$this->url = $this->info['url'];
					
				}

			}

			return $this->html;
		}

		/* url parsing */
		function parse_url(&$url) {
			
			$url = $this->url;
			return parse_url($this->url);
		}

		/* 캐쉬 데이터 저장 */
		function save($folder,$force=false) {
			$content = $this->request($force);
			
			if ($content == "" || $this->errno != 0 || ceil($this->info['http_code']) != 200) {
				
				if ($this->errno != 0) {
					warn("Failed to save ".$this->hash." / CODE : ".$this->info['http_code']." / ERRNO : ".$this->errno." / ERRSTR : ".$this->error."/ URL : ".$this->url);
				}
				
				return "";
			}
			$ext = strtolower($this->get_file_ext($this->url)); //utility.php
			if (strlen($ext) > 3) {
				$ext = "";
			}
			
			if ($ext != "")
				$name = $this->hash.".".$ext;
			else
				$name = $this->hash;


			$filename = $folder."/".$name;
			$cache = $this->cachedir.$this->hash;

			

			if (!file_exists($filename) || (@filesize($filename) != @filesize($cache) && $this->get_file_type($filename) != "image" )) {
				
				file_put_contents($filename,$content);
				@chmod($filename,0777);
			}
			
			return $name;
		}
		
		/* 에러 메시지 얻기 */
		function get_error() {

			return array("errno"=>$this->errno,"error",$this->error);
		}
		
		/* URL path 수정 */
		
		function filter_path($path) {
	        $path = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);
		    $parts = array_filter(explode(DIRECTORY_SEPARATOR, $path), 'strlen');
			$absolutes = array();
	
			foreach ($parts as $part) {
				if ('.' == $part) continue;
				if ('..' == $part) {
					array_pop($absolutes);
				} else {
					$absolutes[] = $part;
				}
	        }
		    return implode(DIRECTORY_SEPARATOR, $absolutes);
	    }

		/* URL ENCODING */
		function url_encode($url)
		{
				// safely cast back already encoded "&" within the query
				$urldata = parse_url($url); //path , host , scheme
				$pathdata = pathinfo($urldata['path']); //dirname , basename , extesion
				$query = $urldata['query'];
				
				parse_str($query,$qs);
				$query = "";
				foreach($qs as $k=> $d) {
					$d = trim($d);
					unset($qs[$k]);
					if (strpos($urldata['host'],"127") !== false)
						$k = str_replace("_",".",$k);

					if ($query != "") $query.="&";
					
					$query .= ($k)."=".(rawurlencode($d));
				}
				
				$rurl = "";
				
				if ($urldata['scheme'] != "") {
					if ($urldata['port'] != "" && $urldata['port'] != "80" )
						$rurl = $urldata['scheme'] ."://".$urldata['host'].":".$urldata['port'].$urldata['path'];
					else
						$rurl = $urldata['scheme'] ."://".$urldata['host'].$urldata['path'];

				}else {
					$rurl = $urldata['path'];
				}
				
				if ($query != "") $rurl .= "?".$query;
				return $rurl;
		}


	}



?>