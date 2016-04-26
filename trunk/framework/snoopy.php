<?php
/**
 * Snoopy - the PHP net client
 * Author: Monte Ohrt <monte@ispi.net>
 * Copyright (c): 1999-2008 New Digital Group, all rights reserved
 * Version: 1.2.4
 * The latest version of Snoopy can be obtained from:
 * http://snoopy.sourceforge.net/
 */

class snoopy {
	/**** Public variables ****/
	/** user definable vars */
	// host name we are connecting to */
	var $host = "www.php.net";
	var $host_ip = '';
	// port we are connecting to */
	var $port = 80;
	// proxy host to use */
	var $proxy_host = "";
	/** proxy port to use */
	var $proxy_port = "";
	// proxy user to use */
	var $proxy_user = "";
	// proxy password to use */
	var $proxy_pass = "";
	// agent we masquerade as */
	var $agent = "Snoopy v1.2.4";
	// referer info to pass */
	var	$referer = "";
	/**
	 *  array of cookies to pass
	 * $cookies["username"]="joe";
	 */
	var $cookies = array();
	/**
	 * array of raw headers to send
	 * $rawheaders["Content-type"]="text/html";
	 */
	var	$rawheaders = array();
	// http redirection depth maximum. 0 = disallow
	var $maxredirs = 5;
	// contains address of last redirected address
	var $lastredirectaddr = "";
	// allows redirection off-site
	var	$offsiteok = true;
	// frame content depth maximum. 0 = disallow
	var $maxframes = 0;
	/**
	 *  expand links to fully qualified URLs.
	 * this only applies to fetchlinks()
	 * submitlinks(), and submittext()
	 */
	var $expandlinks = true;
	/**
	 * pass set cookies back through redirects
	 * NOTE: this currently does not respect
	 * dates, domains or paths.
	 */
	var $passcookies = true;
	// user for http authentication
	var	$user = "";
	// password for http authentication
	var	$pass = "";
	// http accept types
	var $accept = "image/gif, image/x-xbitmap, image/jpeg, image/pjpeg, */*";
	// where the content is put
	var $results = "";
	// error messages sent here
	var $error = "";
	// response code returned from server
	var	$response_code = "";
	// headers returned from server sent here
	var	$headers = array();
	// max return data length (body)
	var	$maxlength = 500000;
	/**
	 * timeout on read operations, in seconds
	 * supported only since PHP 4 Beta 4
	 * set to 0 to disallow timeouts
	 */
	var $read_timeout = 0;
	// if a read operation timed out
	var $timed_out = false;
	// http request status
	var	$status = 0;
	/**
	 * temporary directory that the webserver
	 * has permission to write to.
	 * under Windows, this should be C:\temp
	 */
	var $temp_dir = "/tmp";
	var	$curl_path = "/usr/local/bin/curlbak";

	/** send Accept-encoding: gzip? */
	var $use_gzip = true;
	/** 请求的页面是否进行了 gzip 压缩 */
	var $is_gzipped = false;

	/**** Private variables ****/
	// max line length (headers)
	var	$_maxlinelen = 4096;
	// default http request method
	var $_httpmethod = "GET";
	// default http request version
	var $_httpversion = "HTTP/1.0";
	// default submit method
	var $_submit_method = "POST";
	// default submit type
	var $_submit_type = "application/x-www-form-urlencoded";
	// MIME boundary for multipart/form-data submit type
	var $_mime_boundary = "";
	// will be set if page fetched is a redirect
	var $_redirectaddr = false;
	// increments on an http redirect
	var $_redirectdepth = 0;
	// frame src urls
	var $_frameurls = array();
	// increments on frame depth
	var $_framedepth = 0;
	// set if using a proxy server
	var $_isproxy = false;
	// timeout for socket connection
	var $_fp_timeout = 30;

	/**
	 * fetch the contents of a web page
	 * @param string $uri the location of the page to fetch
	 * @return $this->results	the output text from the fetch
	 */
	function fetch($uri) {
		/** preg_match("|^([^:]+)://([^:/]+)(:[\d]+)*(.*)|", $uri, $uri_parts); */
		$uri_parts = parse_url($uri);
		if(!empty($uri_parts["user"])) $this->user = $uri_parts["user"];
		if(!empty($uri_parts["pass"])) $this->pass = $uri_parts["pass"];
		if(empty($uri_parts["query"])) $uri_parts["query"] = '';
		if(empty($uri_parts["path"])) $uri_parts["path"] = '';
		if (!isset($uri_parts["scheme"])) {
			return false;
		}

		$this->host2ip($uri, $uri_parts);

		switch(strtolower($uri_parts["scheme"])) {
			case "http":
				$this->host = $uri_parts["host"];
				if(!empty($uri_parts["port"])) {
					$this->port = $uri_parts["port"];
				}

				$fp = null;
				if($this->_connect($fp)) {
					if($this->_isproxy) {
						// using proxy, send entire URI
						$this->_httprequest($uri, $fp, $uri, $this->_httpmethod);
					} else {
						$path = $uri_parts["path"].($uri_parts["query"] ? "?".$uri_parts["query"] : "");
						// no proxy, send only the path
						$this->_httprequest($path, $fp, $uri, $this->_httpmethod);
					}

					$this->_disconnect($fp);
					if($this->_redirectaddr) {
						/* url was redirected, check if we've hit the max depth */
						if($this->maxredirs > $this->_redirectdepth) {
							// only follow redirect if it's on this site, or offsiteok is true
							if(preg_match("|^http://".preg_quote($this->host)."|i", $this->_redirectaddr) || $this->offsiteok) {
								/* follow the redirect */
								$this->_redirectdepth ++;
								$this->lastredirectaddr = $this->_redirectaddr;
								$this->fetch($this->_redirectaddr);
							}
						}
					}

					if($this->_framedepth < $this->maxframes && count($this->_frameurls) > 0) {
						$frameurls = $this->_frameurls;
						$this->_frameurls = array();
						while(list(, $frameurl) = each($frameurls)) {
							if($this->_framedepth < $this->maxframes) {
								$this->fetch($frameurl);
								$this->_framedepth ++;
							} else break;
						}
					}
				} else {
					return false;
				}
				return true;
				break;
			case "https":
				if (!$this->curl_path) {
					return $this->fetch_by_curl($uri);
				}

				if (function_exists("is_executable")) {
					if (!is_executable($this->curl_path)) {
						return $this->fetch_by_curl($uri, $this->_httpmethod);
					}
				}

				$this->host = $uri_parts["host"];
				if(!empty($uri_parts["port"])) {
					$this->port = $uri_parts["port"];
				}

				if($this->_isproxy) {
					// using proxy, send entire URI
					$this->_httpsrequest($uri, $uri, $this->_httpmethod);
				} else {
					$path = $uri_parts["path"].($uri_parts["query"] ? "?".$uri_parts["query"] : "");
					// no proxy, send only the path
					$this->_httpsrequest($path, $uri, $this->_httpmethod);
				}

				if($this->_redirectaddr) {
					/* url was redirected, check if we've hit the max depth */
					if($this->maxredirs > $this->_redirectdepth) {
						// only follow redirect if it's on this site, or offsiteok is true
						if(preg_match("|^http://".preg_quote($this->host)."|i", $this->_redirectaddr) || $this->offsiteok) {
							/* follow the redirect */
							$this->_redirectdepth ++;
							$this->lastredirectaddr = $this->_redirectaddr;
							$this->fetch($this->_redirectaddr);
						}
					}
				}

				if($this->_framedepth < $this->maxframes && count($this->_frameurls) > 0) {
					$frameurls = $this->_frameurls;
					$this->_frameurls = array();

					while(list(, $frameurl) = each($frameurls)) {
						if($this->_framedepth < $this->maxframes) {
							$this->fetch($frameurl);
							$this->_framedepth ++;
						} else break;
					}
				}
				return true;
				break;
			default:
				// not a valid protocol
				$this->error = 'Invalid protocol "'.$uri_parts["scheme"].'"\n';
				return false;
				break;
		}
		return true;
	}

	/**
	 * submit an http form
	 * @param string $uri the location to post the data
	 * @param array $formvars the formvars to use.
	 * @param array $formfiles an array of files to submit
	 * @return $this->results	the text output from the post
	 */
	function submit($uri, $formvars = "", $formfiles = "") {
		unset($postdata);
		$postdata = $this->_prepare_post_body($formvars, $formfiles);

		$uri_parts = parse_url($uri);
		if(!empty($uri_parts["user"])) $this->user = $uri_parts["user"];
		if(!empty($uri_parts["pass"])) $this->pass = $uri_parts["pass"];
		if(empty($uri_parts["query"])) $uri_parts["query"] = '';
		if(empty($uri_parts["path"])) $uri_parts["path"] = '';

		$this->host2ip($uri, $uri_parts);

		switch(strtolower($uri_parts["scheme"])) {
			case "http":
				$this->host = $uri_parts["host"];
				if(!empty($uri_parts["port"])) $this->port = $uri_parts["port"];
				if($this->_connect($fp)) {
					if($this->_isproxy) {
						// using proxy, send entire URI
						$this->_httprequest($uri, $fp, $uri, $this->_submit_method, $this->_submit_type, $postdata);
					} else {
						$path = $uri_parts["path"].($uri_parts["query"] ? "?".$uri_parts["query"] : "");
						// no proxy, send only the path
						$this->_httprequest($path, $fp, $uri, $this->_submit_method, $this->_submit_type, $postdata);
					}

					$this->_disconnect($fp);

					if($this->_redirectaddr) {
						/* url was redirected, check if we've hit the max depth */
						if($this->maxredirs > $this->_redirectdepth) {
							if(!preg_match("|^".$uri_parts["scheme"]."://|", $this->_redirectaddr)) {
								$this->_redirectaddr = $this->_expandlinks($this->_redirectaddr, $uri_parts["scheme"]."://".$uri_parts["host"]);
							}
							// only follow redirect if it's on this site, or offsiteok is true
							if(preg_match("|^http://".preg_quote($this->host)."|i", $this->_redirectaddr) || $this->offsiteok) {
								/* follow the redirect */
								$this->_redirectdepth ++;
								$this->lastredirectaddr = $this->_redirectaddr;
								if(strpos($this->_redirectaddr, "?") > 0) {
									$this->fetch($this->_redirectaddr); // the redirect has changed the request method from post to get
								} else $this->submit($this->_redirectaddr, $formvars, $formfiles);
							}
						}
					}

					if($this->_framedepth < $this->maxframes && count($this->_frameurls) > 0) {
						$frameurls = $this->_frameurls;
						$this->_frameurls = array();

						while(list(, $frameurl) = each($frameurls)) {
							if($this->_framedepth < $this->maxframes) {
								$this->fetch($frameurl);
								$this->_framedepth ++;
							} else break;
						}
					}
				} else {
					return false;
				}
				return true;
				break;
			case "https":
				if (!$this->curl_path) {
					return $this->fetch_by_curl($uri, $this->_submit_method, $this->_submit_type, $postdata);
				}

				if (function_exists("is_executable")) {
					if (!is_executable($this->curl_path)) {
						return $this->fetch_by_curl($uri, $this->_submit_method, $this->_submit_type, $postdata);
					}
				}
				$this->host = $uri_parts["host"];
				if(!empty($uri_parts["port"])) $this->port = $uri_parts["port"];
				if($this->_isproxy) {
					// using proxy, send entire URI
					$this->_httpsrequest($uri, $uri, $this->_submit_method, $this->_submit_type, $postdata);
				} else {
					$path = $uri_parts["path"].($uri_parts["query"] ? "?".$uri_parts["query"] : "");
					// no proxy, send only the path
					$this->_httpsrequest($path, $uri, $this->_submit_method, $this->_submit_type, $postdata);
				}

				if($this->_redirectaddr) {
					/* url was redirected, check if we've hit the max depth */
					if($this->maxredirs > $this->_redirectdepth) {
						if(!preg_match("|^".$uri_parts["scheme"]."://|", $this->_redirectaddr))
							$this->_redirectaddr = $this->_expandlinks($this->_redirectaddr, $uri_parts["scheme"]."://".$uri_parts["host"]);

						// only follow redirect if it's on this site, or offsiteok is true
						if(preg_match("|^http://".preg_quote($this->host)."|i", $this->_redirectaddr) || $this->offsiteok) {
							/* follow the redirect */
							$this->_redirectdepth ++;
							$this->lastredirectaddr = $this->_redirectaddr;
							if(strpos($this->_redirectaddr, "?") > 0) {
								$this->fetch($this->_redirectaddr); // the redirect has changed the request method from post to get
							} else $this->submit($this->_redirectaddr, $formvars, $formfiles);
						}
					}
				}

				if($this->_framedepth < $this->maxframes && count($this->_frameurls) > 0) {
					$frameurls = $this->_frameurls;
					$this->_frameurls = array();

					while(list(, $frameurl) = each($frameurls)) {
						if($this->_framedepth < $this->maxframes) {
							$this->fetch($frameurl);
							$this->_framedepth ++;
						} else break;
					}
				}
				return true;
				break;

			default:
				// not a valid protocol
				$this->error = 'Invalid protocol "'.$uri_parts["scheme"].'"\n';
				return false;
				break;
		}
		return true;
	}

	/**
	 * 使用 HTTP DELETE 协议提交数据
	 * @param string $uri
	 * @param array $formvars
	 * @author Deepseath
	 * @return Ambigous <$this->results, boolean>
	 */
	function submit_by_delete($uri, $formvars) {
		$this->_submit_method = 'DELETE';
		return $this->submit($uri, $formvars);
	}

	/**
	 * fetch the links from a web page
	 * @param string $uri where you are fetching from
	 * @return $this->results	an array of the URLs
	 */
	function fetchlinks($uri) {
		if($this->fetch($uri)) {
			if($this->lastredirectaddr) $uri = $this->lastredirectaddr;
			if(is_array($this->results)) {
				for($x = 0; $x < count($this->results); $x ++) {
					$this->results[$x] = $this->_striplinks($this->results[$x]);
				}
			} else $this->results = $this->_striplinks($this->results);

			if($this->expandlinks) $this->results = $this->_expandlinks($this->results, $uri);
			return true;
		} else return false;
	}

	/**
	 * fetch the form elements from a web page
	 * @param string $uri where you are fetching from
	 * @return $this->results	the resulting html form
	 */
	function fetchform($uri) {
		if($this->fetch($uri)) {
			if(is_array($this->results)) {
				for($x = 0; $x < count($this->results); $x ++) {
					$this->results[$x] = $this->_stripform($this->results[$x]);
				}
			} else $this->results = $this->_stripform($this->results);
			return true;
		} else return false;
	}

	/**
	 * fetch the text from a web page, stripping the links
	 * @param string $uri where you are fetching from
	 * @return $this->results	the text from the web page
	 */
	function fetchtext($uri) {
		if($this->fetch($uri)) {
			if(is_array($this->results)) {
				for($x = 0; $x < count($this->results); $x ++) {
					$this->results[$x] = $this->_striptext($this->results[$x]);
				}
			} else $this->results = $this->_striptext($this->results);
			return true;
		} else return false;
	}

	/**
	 * grab links from a form submission
	 * @param string $uri where you are submitting from
	 * @param array $formvars
	 * @param array $formfiles
	 * @return $this->results	an array of the links from the post
	 */
	function submitlinks($uri, $formvars = "", $formfiles = "") {
		if($this->submit($uri, $formvars, $formfiles)) {
			if($this->lastredirectaddr) $uri = $this->lastredirectaddr;
			if(is_array($this->results)) {
				for($x = 0; $x < count($this->results); $x ++) {
					$this->results[$x] = $this->_striplinks($this->results[$x]);
					if($this->expandlinks) {
						$this->results[$x] = $this->_expandlinks($this->results[$x], $uri);
					}
				}
			} else {
				$this->results = $this->_striplinks($this->results);
				if($this->expandlinks) {
					$this->results = $this->_expandlinks($this->results, $uri);
				}
			}
			return true;
		} else return false;
	}

	/**
	 * grab text from a form submission
	 * @param string $uri where you are submitting from
	 * @param string $formvars
	 * @param string $formfiles
	 * @return $this->results	the text from the web page
	 */
	function submittext($uri, $formvars = "", $formfiles = "") {
		if($this->submit($uri, $formvars, $formfiles)) {
			if($this->lastredirectaddr) $uri = $this->lastredirectaddr;
			if(is_array($this->results)) {
				for($x = 0; $x < count($this->results); $x ++) {
					$this->results[$x] = $this->_striptext($this->results[$x]);
					if($this->expandlinks) {
						$this->results[$x] = $this->_expandlinks($this->results[$x], $uri);
					}
				}
			} else {
				$this->results = $this->_striptext($this->results);
				if($this->expandlinks) {
					$this->results = $this->_expandlinks($this->results, $uri);
				}
			}
			return true;
		} else return false;
	}

	/** set_submit_multipart */
	function set_submit_multipart() {
		$this->_submit_type = "multipart/form-data";
	}

	/** set_submit_normal */
	function set_submit_normal() {
		$this->_submit_type = "application/x-www-form-urlencoded";
	}




/*======================================================================*\
	Private functions
\*======================================================================*/


	/**
	 * strip the hyperlinks from an html document
	 * @param string $document document to strip.
	 * @return an array of the links
	 */
	function _striplinks($document) {
		preg_match_all("'<\s*a\s.*?href\s*=\s*			# find <a href=
						([\"\'])?					# find single or double quote
						(?(1) (.*?)\\1 | ([^\s\>]+))		# if quote found, match up to next matching
													# quote, otherwise match up to next space
						'isx", $document, $links);

		// catenate the non-empty matches from the conditional subpattern
		while(list($key, $val) = each($links[2])) {
			if(!empty($val)) $match[] = $val;
		}

		while(list($key, $val) = each($links[3])) {
			if(!empty($val)) $match[] = $val;
		}

		// return the links
		return $match;
	}

	/**
	 * strip the form elements from an html document
	 * @param string $document document to strip.
	 * @return an array of the links
	 */
	function _stripform($document) {
		preg_match_all("'<\/?(FORM|INPUT|SELECT|TEXTAREA|(OPTION))[^<>]*>(?(2)(.*(?=<\/?(option|select)[^<>]*>[\r\n]*)|(?=[\r\n]*))|(?=[\r\n]*))'Usi", $document, $elements);
		// catenate the matches
		$match = implode("\r\n", $elements[0]);
		// return the links
		return $match;
	}

	/**
	 * strip the text from an html document
	 * @param string $document document to strip.
	 * @return the resulting text
	 */
	function _striptext($document) {
		// I didn't use preg eval (//e) since that is only available in PHP 4.0.
		// so, list your entities one by one here. I included some of the
		// more common ones.
		$search = array(
			"'<script[^>]*?>.*?</script>'si",	// strip out javascript
			"'<[\/\!]*?[^<>]*?>'si",			// strip out html tags
			"'([\r\n])[\s]+'",					// strip out white space
			"'&(quot|#34|#034|#x22);'i",		// replace html entities
			"'&(amp|#38|#038|#x26);'i",			// added hexadecimal values
			"'&(lt|#60|#060|#x3c);'i",
			"'&(gt|#62|#062|#x3e);'i",
			"'&(nbsp|#160|#xa0);'i",
			"'&(iexcl|#161);'i",
			"'&(cent|#162);'i",
			"'&(pound|#163);'i",
			"'&(copy|#169);'i",
			"'&(reg|#174);'i",
			"'&(deg|#176);'i",
			"'&(#39|#039|#x27);'",
			"'&(euro|#8364);'i",				// europe
			"'&a(uml|UML);'",					// german
			"'&o(uml|UML);'",
			"'&u(uml|UML);'",
			"'&A(uml|UML);'",
			"'&O(uml|UML);'",
			"'&U(uml|UML);'",
			"'&szlig;'i",
		);
		$replace = array(
			"",
			"",
			"\\1",
			"\"",
			"&",
			"<",
			">",
			" ",
			chr(161),
			chr(162),
			chr(163),
			chr(169),
			chr(174),
			chr(176),
			chr(39),
			chr(128),
			"?",
			"?",
			"?",
			"?",
			"?",
			"?",
			"?",
		);

		$text = preg_replace($search, $replace, $document);
		return $text;
	}

	/**
	 * expand each link into a fully qualified URL
	 * @param string $links the links to qualify
	 * @param string $uri the full URI to get the base from
	 * @return the expanded links
	 */
	function _expandlinks($links, $uri) {
		preg_match("/^[^\?]+/", $uri, $match);

		$match = preg_replace("|/[^\/\.]+\.[^\/\.]+$|","", $match[0]);
		$match = preg_replace("|/$|","", $match);
		$match_part = parse_url($match);
		$match_root = $match_part["scheme"]."://".$match_part["host"];

		$search = array(
			"|^http://".preg_quote($this->host)."|i",
			"|^(\/)|i",
			"|^(?!http://)(?!mailto:)|i",
			"|/\./|",
			"|/[^\/]+/\.\./|"
		);

		$replace = array(
			"",
			$match_root."/",
			$match."/",
			"/",
			"/"
		);

		$expandedLinks = preg_replace($search, $replace, $links);
		return $expandedLinks;
	}

	/**
	 * go get the http data from the server
	 * @param string $url the url to fetch
	 * @param int $fp the current open file pointer
	 * @param string $uri the full URI
	 * @param string $http_method
	 * @param string $content_type
	 * @param string $body body contents to send if any (POST)
	 */
	function _httprequest($url, $fp, $uri, $http_method, $content_type = "", $body = "") {
		$cookie_headers = '';
		if($this->passcookies && $this->_redirectaddr) $this->setcookies();

		$uri_parts = parse_url($uri);
		if(empty($url)) $url = "/";
		$headers = $http_method." ".$url." ".$this->_httpversion."\r\n";
		if(!empty($this->agent)) {
			$headers .= "User-Agent: ".$this->agent."\r\n";
		}
		if(!empty($this->host) && !isset($this->rawheaders['Host'])) {
			$headers .= "Host: ".$this->host;
			if(!empty($this->port) && 80 != $this->port) {
				$headers .= ":".$this->port;
			}
			$headers .= "\r\n";
		}
		if(!empty($this->accept)) {
			$headers .= "Accept: ".$this->accept."\r\n";
		}
		if($this->use_gzip) {
			// make sure PHP was built with --with-zlib
			// and we can handle gzipp'ed data
			if(function_exists('gzinflate')) {
				$headers .= "Accept-encoding: gzip, deflate\r\n";
			} else {
				//commented to supress the warning
				/**trigger_error(
				"use_gzip is on, but PHP was built without zlib support.".
				"  Requesting file(s) without gzip encoding.",
				E_USER_NOTICE);*/
			}
		}
		if(!empty($this->referer)) {
			$headers .= "Referer: ".$this->referer."\r\n";
		}
		if(!empty($this->cookies)) {
			if(!is_array($this->cookies)) {
				$this->cookies = (array)$this->cookies;
			}
			reset($this->cookies);
			if(count($this->cookies) > 0) {
				$cookie_headers .= 'Cookie: ';
				// debug 临时解决google的302跳转问题
				$isGoogle = strpos($uri_parts['host'], 'google.com') !== false;
				foreach($this->cookies as $cookieKey => $cookieVal) {
					if($isGoogle) {
						$cookie_headers .= $cookieKey."=".($cookieVal)."; ";
					} else {
						$cookie_headers .= $cookieKey."=".urlencode($cookieVal)."; ";
					}
				}
				$headers .= substr($cookie_headers,0,-2)."\r\n";
			}
		}
		if(!empty($this->rawheaders)) {
			if(!is_array($this->rawheaders)) {
				$this->rawheaders = (array)$this->rawheaders;
			}
			while(list($headerKey, $headerVal) = each($this->rawheaders)) {
				$headers .= $headerKey.": ".$headerVal."\r\n";
			}
		}
		if(!empty($content_type)) {
			$headers .= "Content-type: $content_type";
			if($content_type == "multipart/form-data") {
				$headers .= "; boundary=".$this->_mime_boundary;
			}
			$headers .= "\r\n";
		}
		if(!empty($body)) {
			$headers .= "Content-length: ".strlen($body)."\r\n";
		}
		if(!empty($this->user) || !empty($this->pass)) {
			$headers .= "Authorization: Basic ".base64_encode($this->user.":".$this->pass)."\r\n";
		}

		//add proxy auth headers
		if(!empty($this->proxy_user)) {
			$headers .= 'Proxy-Authorization: '.'Basic '.base64_encode($this->proxy_user.':'.$this->proxy_pass)."\r\n";
		}

		// Arice 修正无法正常结束造成非常慢的问题
		if($http_method != $this->_submit_method) {
			$headers .= "Connection: Close\r\n";
		}

		$headers .= "\r\n";

		// set the read timeout if needed
		if($this->read_timeout > 0) {
			socket_set_timeout($fp, $this->read_timeout);
		}
		$this->timed_out = false;

		fwrite($fp, $headers.$body, strlen($headers.$body));

		$this->_redirectaddr = false;
		unset($this->headers);

		// content was returned gzip encoded?
		$this->is_gzipped = false;
		while($currentHeader = fgets($fp, $this->_maxlinelen)) {
			if($this->read_timeout > 0 && $this->_check_timeout($fp)) {
				$this->status = -100;
				return false;
			}

			if($currentHeader == "\r\n") break;

			// if a header begins with Location: or URI:, set the redirect
			if(preg_match("/^(Location:|URI:)/i", $currentHeader)) {
				// get URL portion of the redirect
				preg_match("/^(Location:|URI:)[ ]*(.*)/i", chop($currentHeader), $matches);
				// look for :// in the Location header to see if hostname is included
				if(!preg_match("|\:\/\/|", $matches[2])) {
					// no host in the path, so prepend
					$this->_redirectaddr = $uri_parts["scheme"]."://".$this->host.":".$this->port;
					// eliminate double slash
					if(!preg_match("|^/|", $matches[2])) $this->_redirectaddr .= "/".$matches[2];
					else $this->_redirectaddr .= $matches[2];
				} else $this->_redirectaddr = $matches[2];
			}

			if(preg_match("|^HTTP/|", $currentHeader)) {
                if(preg_match("|^HTTP/[^\s]*\s(.*?)\s|", $currentHeader, $status)) {
					$this->status= $status[1];
                }
				$this->response_code = $currentHeader;
			}
            if(preg_match("/Content-Encoding\s*:\s*gzip/i", $currentHeader)) {
            	$this->is_gzipped = true;
            }

			$this->headers[] = $currentHeader;
		}

		$results = '';
		do {
			// 在读取页面内容时，也需要判断是否超时
			if($this->read_timeout > 0 && $this->_check_timeout($fp)) {
				$this->status = -100;
				return false;
			}
    		$_data = fread($fp, $this->maxlength);
    		if(strlen($_data) == 0) {
        		break;
    		}
    		$results .= $_data;
		} while(true);

		// gunzip
		if($this->is_gzipped) {
			$mtime = explode(' ', microtime());
			$startTime = number_format(($mtime[1] + $mtime[0]), 6);
			// per http://www.php.net/manual/en/function.gzencode.php
			$results = gzinflate(substr($this->unchunkHttp11($results), 10));
		}

		// check if there is a a redirect meta tag
		if(preg_match("'<meta[\s]*http-equiv[^>]*?content[\s]*=[\s]*[\"\']?\d+;[\s]*URL[\s]*=[\s]*([^\"\']*?)[\"\']?>'i", $results, $match)) {
			$this->_redirectaddr = $this->_expandlinks($match[1], $uri);
		}

		// have we hit our frame depth and is there frame src to fetch?
		if(($this->_framedepth < $this->maxframes) && preg_match_all("'<frame\s+.*src[\s]*=[\'\"]?([^\'\"\>]+)'i", $results, $match)) {
			$this->results[] = $results;
			for($x = 0; $x < count($match[1]); $x ++) {
				$this->_frameurls[] = $this->_expandlinks($match[1][$x], $uri_parts["scheme"]."://".$this->host);
			}
		}
		// have we already fetched framed content?
		elseif(is_array($this->results)) $this->results[] = $results;
		// no framed content
		else $this->results = $results;

		return true;
	}

	// 反 chunk
	function unchunkHttp11($data) {
		$i = 0;
		$ret = '';
		while(true) {
			$arr = explode("\r\n", $data);
			$tmp = trim($arr[0]);
			if(preg_match("/^[a-zA-Z0-9]+$/is", $tmp) && !empty($tmp)) {
				$len = hexdec($tmp);
				$ret .= substr($data, strlen($arr[0]) + 2, $len);
				$data = substr($data, strlen($arr[0]) + 2 + $len + 2);
			} else {
				$ret .= $arr[0]."\r\n";
				$data = substr($data, strlen($arr[0]) + 2);
			}
			$i ++;
			if(1 > strlen($data) || $i > 99) {
				break;
			}
		}
		return $ret;
	}

/*======================================================================*\
	Function:	_httpsrequest
	Purpose:	go get the https data from the server using curl
	Input:		$url		the url to fetch
				$uri		the full URI
				$body		body contents to send if any (POST)
	Output:
\*======================================================================*/

	function _httpsrequest($url, $uri, $http_method, $content_type="", $body="") {
		if($this->passcookies && $this->_redirectaddr) $this->setcookies();

		$headers = array();

		$uri_parts = parse_url($uri);
		if(empty($url)) $url = "/";
		// GET ... header not needed for curl
		//$headers[] = $http_method." ".$url." ".$this->_httpversion;
		if(!empty($this->agent)) $headers[] = "User-Agent: ".$this->agent;
		if(!empty($this->host)) {
			if(!empty($this->port)) $headers[] = "Host: ".$this->host.":".$this->port;
			else $headers[] = "Host: ".$this->host;
		}
		if(!empty($this->accept)) $headers[] = "Accept: ".$this->accept;
		if(!empty($this->referer)) $headers[] = "Referer: ".$this->referer;
		if(!empty($this->cookies)) {
			if(!is_array($this->cookies)) $this->cookies = (array)$this->cookies;
			reset($this->cookies);
			if(count($this->cookies) > 0) {
				$cookie_str = 'Cookie: ';
				foreach($this->cookies as $cookieKey => $cookieVal) {
					$cookie_str .= $cookieKey."=".urlencode($cookieVal)."; ";
				}
				$headers[] = substr($cookie_str, 0, -2);
			}
		}
		if(!empty($this->rawheaders)) {
			if(!is_array($this->rawheaders)) $this->rawheaders = (array)$this->rawheaders;
			while(list($headerKey, $headerVal) = each($this->rawheaders)) {
				$headers[] = $headerKey.": ".$headerVal;
			}
		}
		if(!empty($content_type)) {
			if($content_type == "multipart/form-data") $headers[] = "Content-type: $content_type; boundary=".$this->_mime_boundary;
			else $headers[] = "Content-type: $content_type";
		}
		if(!empty($body)) $headers[] = "Content-length: ".strlen($body);
		if(!empty($this->user) || !empty($this->pass)) {
			$headers[] = "Authorization: BASIC ".base64_encode($this->user.":".$this->pass);
		}

		for($curr_header = 0; $curr_header < count($headers); $curr_header ++) {
			$safer_header = strtr($headers[$curr_header], "\"", " ");
			$cmdline_params .= " -H \"".$safer_header."\"";
		}

		if(!empty($body)) $cmdline_params .= " -d \"$body\"";

		if($this->read_timeout > 0) $cmdline_params .= " -m ".$this->read_timeout;

		$headerfile = tempnam($temp_dir, "sno");

		exec($this->curl_path." -k -D \"$headerfile\"".$cmdline_params." \"".escapeshellcmd($uri)."\"", $results, $return);

		if($return) {
			$this->error = "Error: cURL could not retrieve the document, error $return.";
			return false;
		}

		$results = implode("\r\n", $results);
		$result_headers = file("$headerfile");

		$this->_redirectaddr = false;
		unset($this->headers);

		for($currentHeader = 0; $currentHeader < count($result_headers); $currentHeader ++) {
			// if a header begins with Location: or URI:, set the redirect
			if(preg_match("/^(Location: |URI: )/i", $result_headers[$currentHeader])) {
				// get URL portion of the redirect
				preg_match("/^(Location: |URI:)\s+(.*)/",chop($result_headers[$currentHeader]), $matches);
				// look for :// in the Location header to see if hostname is included
				if(!preg_match("|\:\/\/|", $matches[2])) {
					// no host in the path, so prepend
					$this->_redirectaddr = $uri_parts["scheme"]."://".$this->host.":".$this->port;
					// eliminate double slash
					if(!preg_match("|^/|", $matches[2])) $this->_redirectaddr .= "/".$matches[2];
					else $this->_redirectaddr .= $matches[2];
				} else $this->_redirectaddr = $matches[2];
			}

			if(preg_match("|^HTTP/|", $result_headers[$currentHeader])) {
				$this->response_code = $result_headers[$currentHeader];
			}

			$this->headers[] = $result_headers[$currentHeader];
		}

		// check if there is a a redirect meta tag
		if(preg_match("'<meta[\s]*http-equiv[^>]*?content[\s]*=[\s]*[\"\']?\d+;[\s]*URL[\s]*=[\s]*([^\"\']*?)[\"\']?>'i", $results, $match)) {
			$this->_redirectaddr = $this->_expandlinks($match[1], $uri);
		}

		// have we hit our frame depth and is there frame src to fetch?
		if(($this->_framedepth < $this->maxframes) && preg_match_all("'<frame\s+.*src[\s]*=[\'\"]?([^\'\"\>]+)'i", $results, $match)) {
			$this->results[] = $results;
			for($x = 0; $x < count($match[1]); $x ++) {
				$this->_frameurls[] = $this->_expandlinks($match[1][$x], $uri_parts["scheme"]."://".$this->host);
			}
		}
		// have we already fetched framed content?
		elseif(is_array($this->results)) $this->results[] = $results;
		// no framed content
		else $this->results = $results;

		unlink("$headerfile");

		return true;
	}

/*======================================================================*\
	Function:	setcookies()
	Purpose:	set cookies for a redirection
\*======================================================================*/

	function setcookies() {
		for($x = 0; $x < count($this->headers); $x ++) {
			if(preg_match('/^set-cookie:[\s]+([^=]+)=([^;]+)/i', $this->headers[$x], $match)) {
				$this->cookies[$match[1]] = urldecode($match[2]);
			}
		}
	}


/*======================================================================*\
	Function:	_check_timeout
	Purpose:	checks whether timeout has occurred
	Input:		$fp	file pointer
\*======================================================================*/

	function _check_timeout($fp) {
		if($this->read_timeout > 0) {
			$fp_status = socket_get_status($fp);
			if($fp_status["timed_out"]) {
				$this->timed_out = true;
				return true;
			}
		}
		return false;
	}

/*======================================================================*\
	Function:	_connect
	Purpose:	make a socket connection
	Input:		$fp	file pointer
\*======================================================================*/

	function _connect(&$fp) {
		if(!empty($this->proxy_host) && !empty($this->proxy_port)) {
			$this->_isproxy = true;
			$host = $this->proxy_host;
			$port = $this->proxy_port;
		} else {
			$host = $this->host;
			$port = $this->port;
		}
		$this->status = 0;
		if($fp = @fsockopen($host, $port, $errno, $errstr, $this->_fp_timeout)) {
			// socket connection succeeded
			return true;
		} else {
			// socket connection failed
			$this->status = $errno;
			$this->error = $errstr;
			switch($errno) {
				case -3:
					$this->error="socket creation failed (-3)";
				case -4:
					$this->error="dns lookup failure (-4)";
				case -5:
					$this->error="connection refused or timed out (-5)";
				default:
					$this->error="connection failed (".$errno.")";
			}
			return false;
		}
	}
/*======================================================================*\
	Function:	_disconnect
	Purpose:	disconnect a socket connection
	Input:		$fp	file pointer
\*======================================================================*/

	function _disconnect($fp) {
		return(fclose($fp));
	}


/*======================================================================*\
	Function:	_prepare_post_body
	Purpose:	Prepare post body according to encoding type
	Input:		$formvars  - form variables
				$formfiles - form upload files
	Output:		post body
\*======================================================================*/

	function _prepare_post_body($formvars, $formfiles) {
		/** 不强制转换类型, zhuxun */
		/** settype($formvars, "array"); */
		settype($formfiles, "array");
		$postdata = '';

		/** 修改判断, zhuxun */
		/** if(count($formvars) == 0 && count($formfiles) == 0) return;*/
		if (empty($formvars) && 0 == count($formfiles)) return $postdata;

		switch ($this->_submit_type) {
			case "application/x-www-form-urlencoded":
				if (is_array($formvars)) {
					reset($formvars);
					$postdata .= http_build_query($formvars);
				} else {
					$postdata .= $formvars;
				}
				break;
			case "multipart/form-data":
				$this->_mime_boundary = "Snoopy".md5(uniqid(microtime()));
				/** 强制转换类型, zhuxun */
				settype($formvars, "array");
				reset($formvars);
				while(list($key, $val) = each($formvars)) {
					if(is_array($val) || is_object($val)) {
						while(list($cur_key, $cur_val) = each($val)) {
							$postdata .= "--".$this->_mime_boundary."\r\n";
							$postdata .= "Content-Disposition: form-data; name=\"$key\[\]\"\r\n\r\n";
							$postdata .= "$cur_val\r\n";
						}
					} else {
						$postdata .= "--".$this->_mime_boundary."\r\n";
						$postdata .= "Content-Disposition: form-data; name=\"$key\"\r\n\r\n";
						$postdata .= "$val\r\n";
					}
				}

				reset($formfiles);
				while(list($field_name, $file_names) = each($formfiles)) {
					settype($file_names, "array");
					while(list(, $file_name) = each($file_names)) {
						if(!is_readable($file_name)) continue;

						$fp = fopen($file_name, "r");
						$file_content = fread($fp, filesize($file_name));
						fclose($fp);
						$base_name = basename($file_name);

						$postdata .= "--".$this->_mime_boundary."\r\n";
						$postdata .= "Content-Disposition: form-data; name=\"$field_name\"; filename=\"$base_name\"\r\n\r\n";
						$postdata .= "$file_content\r\n";
					}
				}
				$postdata .= "--".$this->_mime_boundary."--\r\n";
				break;
		}

		return $postdata;
	}

	/**
	 * 通过 curl 读取
	 * @param string $uri
	 * @param string $http_method 提交方式:get, post, put
	 * @param string $content_type 数据类型
	 * @param mixed $body post数据
	 * @throws http_request_exception
	 */
	public function fetch_by_curl($uri, $http_method, $content_type = "", $body = "") {

		$this->host2ip($uri);

		$uri_parts = parse_url($uri);
		$ch = curl_init();
		switch ($this->_httpversion) {
			case 'HTTP/1.1':
				curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
				break;
			case 'HTTP/1.0':
			default:
				curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
				break;
		}

		$hostname = '';
		if ($this->_isproxy) {
			$url = preg_replace('/(https?:\/\/)([\w\.\-]+)(\/?)/e', '$this->proxy_rewrite("\1", "\2", "\3", $hostname)', $uri);
			$this->host = $hostname;
		} else {
			if (!empty($this->dnscache)) {
				$url = preg_replace('/(https?:\/\/)([\w\.\-]+)(\/?)/e', '$this->host_resolve("\1", "\2", "\3", $hostname)', $uri);
				$this->host = $hostname;
			} else {
				$url = $uri;
			}
		}

		$this->host = $uri_parts['host'];
		if (!empty($this->host_ip)) {
			$url = $uri_parts['scheme'].'://'.$this->host_ip.$uri_parts['path'];
		}

		/** 新增http proxy的支持 */
		if ($this->_isproxy) {
			curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, true);
			curl_setopt($ch, CURLOPT_PROXY, $this->proxy_host);
		}

		//curl_setopt($ch, CURLOPT_PORT, $this->port);
		curl_setopt($ch, CURLOPT_URL, $url);
		/** set headers not having special keys */
		$headers_fmt = array(
			'Host: '.$this->host
		);
		if(!empty($this->rawheaders)) {
			if(!is_array($this->rawheaders)) {
				$this->rawheaders = (array)$this->rawheaders;
			}
			while(list($headerKey, $headerVal) = each($this->rawheaders)) {
				$headers_fmt[] = $headerKey.": ".$headerVal;
			}
		}

		if (!empty($content_type)) {
			$headers_fmt[] = "Content-type: $content_type";
			if ($content_type == "multipart/form-data") {
				$headers_fmt[] = "; boundary=".$this->_mime_boundary;
			}
		}

		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers_fmt);
		/** set cookie */
		if (!empty($this->cookies)) {
			if(!is_array($this->cookies)) {
				$this->cookies = (array)$this->cookies;
			}

			reset($this->cookies);
			if (count($this->cookies) > 0) {
				$cookie_str = 'Cookie: ';
				foreach ($this->cookies as $cookieKey => $cookieVal) {
					$cookie_str .= $cookieKey."=".urlencode($cookieVal)."; ";
				}

				curl_setopt($ch, CURLOPT_COOKIE, substr($cookie_str, 0, -2));
			}
		}

		if (!empty($this->agent)) {
			curl_setopt($ch, CURLOPT_USERAGENT, "User-Agent: ".$this->agent);
		}

		if(!empty($this->referer)) {
			curl_setopt($ch, CURLOPT_REFERER, "Referer: ".$this->referer);
		}

		/**if (array_key_exists('accept-encoding', $this->headers)) {
			curl_setopt($ch, CURLOPT_ENCODING, $this->headers['accept-encoding']);
		}*/

		$http_method = rstrtoupper($http_method);
		switch ($http_method) {
			case 'POST':
			case 'PUT':
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($body) ? http_build_query($body) : $body);
				break;
			case 'DELETE'://构造 DELETE 协议 by Deepseath
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
				curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($body) ? http_build_query($body) : $body);
				break;
		}

		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->read_timeout);
		curl_setopt($ch, CURLOPT_TIMEOUT, $this->_fp_timeout);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, true);
		/** fix: dnscache's problem with https:// */
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

		if (0 < $this->maxredirs) {
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_MAXREDIRS, $this->maxredirs);
		}

		$result = curl_exec($ch);
		if ($result === false) {
			logger::writeln('snoopy/debug.log', 'Error sending request: #'.curl_errno($ch).' '. curl_error($ch).' at '."$url -I 'Host: {$this->host}'");
			$this->status=-100;
			return false;
		}

		$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		/** 如果使用了http代理，则要去掉前两行 */
		if ($this->_isproxy) {
			$result = substr($result, strpos($result, "\r\n\r\n") + 4);
		}

		$info = curl_getinfo($ch);
		if ($info['redirect_count'] > 0) {
			for ($i = 0; $i < $info['redirect_count']; $i++) {
				$idx = strpos($result, "\r\n\r\n");
				$result = substr($result, $idx + 4);
			}
		}

		$this->status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		/** 解析返回值 */
		$resp = new http_response($this->status, $result);
		$this->results = $resp->get_body();
		$this->headers = $resp->get_headers();
		return true;
	}

	public function proxy_rewrite($prefix, $name, $suffix, &$hostname) {
		$hostname = $name;
		$name = $this->proxy_host;
		return $prefix.$name.$suffix;
	}

	public function host_resolve($prefix, $name, $suffix, &$hostname) {
		$hostname = $name;
		if ($this->dnscache) {
			$key = 'host_'.$name;
			$host = apc_fetch($key);
			if ($host) {
				return $prefix.$host.$suffix;
			}
		}

		$host = gethostbyname($name);
		if (preg_match('/^\d+\.\d+\.\d+\.\d+$/', $host)) {
			if ($this->dnscache) {
				apc_store($key, $host, 3600);
			}

			return $prefix.$host.$suffix;
		}

		return $prefix.$name.$suffix;
	}

	/**
	 * 畅移域名均使用IP解析
	 * 临时方案
	 */
	public function host2ip($uri, $uri_parts = false) {

		// 不要代理
		return true;
		// 如果未提供url信息，则解析
		if (!$uri_parts) {
			$uri_parts = parse_url($uri);
		}

		// 初始化
		$this->proxy_host = '';
		$this->proxy_port = '';
		$this->_isproxy = false;

		// 不是畅移的域名
		if (empty($uri_parts['host']) || stripos($uri_parts['host'], '.vchangyi.com') === false) {
			return false;
		}

		// 畅移的域名则指定IP解析
		$this->proxy_host = '127.0.0.1';
		$this->proxy_port = 80;
		$this->_isproxy = true;

		return true;
	}

}
