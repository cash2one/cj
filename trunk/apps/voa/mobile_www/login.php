<?php
/**
 * login.php
 * 用户登录入口
 * Create By Deepseath
 * $Author$
 * $Id$
 */
define('ROOT_PATH', dirname(dirname(dirname(dirname(__FILE__)))).DIRECTORY_SEPARATOR);
define('APP_PATH', dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR);
require ROOT_PATH.'framework'.DIRECTORY_SEPARATOR.'function'.DIRECTORY_SEPARATOR.'core.php';
require ROOT_PATH.'framework'.DIRECTORY_SEPARATOR.'http'.DIRECTORY_SEPARATOR.'response.php';

$login = new login();

class login {

	public $snoopy = null;
	public $errcode = 0;
	public $errmsg = '';
	public $result = array();

	/**
	 * 对方请求的回调函数的变量名
	 * @var string
	 */
	public $return_callback_varname = 'login_result';

	/**
	 * 对方请求的回调函数名
	 * @var string
	 */
	public $return_callback_name = '';

	public function __construct() {

		// 如果请求了回调函数 或者 来自本站的请求则进行检查且为POST请求
		$is_submit = isset($_GET[$this->return_callback_varname]) || (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] && isset($_SERVER['HTTP_REFERER']) && stripos($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST']) !== false);
		if ($is_submit) {
			if (isset($_GET[$this->return_callback_varname])) {
				$this->return_callback_name = rhtmlspecialchars($_GET[$this->return_callback_varname]);
			}
			$login_form = array();
			$get = array_merge($_GET, $_POST);
			if (isset($get['account'])) {
				$login_form['account'] = $get['account'];
			}
			if (isset($get['password'])) {
				$login_form['password'] = $get['password'];
			}

			$https = !empty($_SERVER['HTTPS']) && strcasecmp($_SERVER['HTTPS'], 'on') === 0 ||
			!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
			strcasecmp($_SERVER['HTTP_X_FORWARDED_PROTO'], 'https') === 0;
			$http_scheme = $https ? 'https://' : 'http://';
			$this->get_by_api($http_scheme.$_SERVER['HTTP_HOST'].'/api/auth/post/login/', $login_form);
			$this->output();
		} else {
			$this->login_html();
		}
	}

	public function check_login() {

	}

	/**
	 * 自接口获取数据
	 * @param unknown $url
	 * @param string $post
	 * @param unknown $http_header
	 * @param string $http_method
	 * @return boolean
	 */
	public function get_by_api($url, $post = '', $http_header = array()) {

		// 载入 snoopy 类
		if ($this->snoopy === null) {
			require ROOT_PATH.'framework'.DIRECTORY_SEPARATOR.'snoopy.php';
			$this->snoopy = new snoopy();
		}

		$http_method = 'GET';
		if (preg_match('/\/(GET|POST|PUT|DELETE)\/[a-z0-9_]+(\/*[^\/]*)?$/i', $url, $match)) {
			$http_method = strtoupper($match[1]);
		}

		// 使用自定义的头字段，格式为 array(字段名 => 值, ... ...)
		$this->snoopy->rawheaders = $http_header;
		switch ($http_method) {
			case 'POST':
			case 'PUT':
				$result = $this->snoopy->submit($url, $post);
				break;
			case 'DELETE':
				$result = $this->snoopy->submit_by_delete($url, $post);
				break;
			default:
				$url .= stripos($url, '?') === false ? '?' : '&';
				$url .= http_build_query($post);
				$result = $this->snoopy->fetch($url);
		}
		// 如果读取错误
		if ($result === false || 200 != $this->snoopy->status) {
			$this->set_error(100, '网络连接错误');
			return false;
		}

		// 解析 json
		$data = @json_decode($this->snoopy->results, true);
		if ($data === null) {
			$this->set_error(101, '网络连接错误');
			return false;
		}

		if (isset($data['result']['auth']) && is_array($data['result']['auth'])) {
			$domain = preg_replace('/^'.$data['result']['data']['enterprise']['enumber'].'/i', '', $data['result']['data']['enterprise']['domain']);
			foreach ($data['result']['auth'] as $d) {
				if (isset($d['name']) && isset($d['value'])) {
					setcookie($d['name'], $d['value'], 86400*7 + time(), '/', $domain);
				}
			}
			setcookie('pc_app_userdata', rjson_encode($data['result']['data']));
		}

		$this->set_error($data['errcode'], $data['errmsg'], $data['result']);
		return true;
	}

	/**
	 * 设置错误成员值
	 * @param unknown $errcode
	 * @param unknown $errmsg
	 * @param unknown $result
	 */
	public function set_error($errcode, $errmsg, $result = array()) {
		$this->errcode = $errcode;
		$this->errmsg = $errmsg;
		$this->result = $result;
	}

	/**
	 * 输出 json 格式数据
	 * @param mixed $output_data
	 * @param array $result
	 */
	public function output($output_data = null) {
		if ($output_data !== null) {
			if ($this->return_callback_name) {
				echo $this->return_callback_name.'('.$output_data.')';
			} else {
				echo $output_data;
			}
			exit;
		}

		if (!$this->return_callback_name) {
			@header("Content-type: application/json;charset=utf-8");
		} else {
			//@header("Content-type: application/json;charset=utf-8");
		}
		$data = array(
			'errcode' => $this->errcode,
			'errmsg' => $this->errmsg,
			'result' => $this->result
		);
		if ($this->return_callback_name) {
			echo $this->return_callback_name.'('.rjson_encode($data).')';
		} else {
			echo rjson_encode($data);
		}
		exit;
	}

	public function login_html() {
		echo <<<EOF
<!DOCTYPE html>
<html lang="zh-CN">
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<meta name="description" content="bdFramework" />
	<title>登录</title>
	<base href="http://qqqiyehao.vchangyi.com/" />
	<link rel="stylesheet" href="/admincp/static/css/bootstrap.css" />
	<link rel="stylesheet" href="/admincp/static/css/bootstrap-theme.min.css" />
	<link rel="stylesheet" href="/admincp/static/font-awesome/css/font-awesome.min.css" />
	<link rel="stylesheet" href="/admincp/static/css/bootstrap-select.min.css" />
	<link rel="shortcut icon" type="image/x-icon" href="/favicon.ico" />
	<link rel="apple-touch-icon" href="/favicon.ico" />
	<!--[if lt IE 9]>
	<link rel="stylesheet" href="/admincp/static/css/style-ie8.css" />
	<script type="text/javascript" src="/admincp/static/js/html5.js"></script>
	<![endif]-->
	<script type="text/javascript" src="/admincp/static/js/jquery-1.10.2.js"></script>
	<script type="text/javascript" src="/admincp/static/js/bootstrap.js"></script>
	<script type="text/javascript" src="/admincp/static/js/bootstrap-select.min.js"></script>
	<script type="text/javascript" src="/admincp/static/js/common.js"></script>
	<script type="text/javascript" src="/admincp/static/js/md5.js"></script>
	<style type="text/css">
	.container{width:420px;margin-top:60px;font-size:17px;}
	.btn{width:90%;margin:0 auto;display:block}
	.login-form, .login-form div{height:40px;line-height:40px;margin-top:0;margin-bottom:0}
	.form-label{height:40px;line-height:40px;margin-top:0;margin-bottom:0}
	</style>
</head>
<body>
<div class="container">
	<div class="panel panel-default">
		<div class="panel-heading">
			<h3 class="panel-title"><strong>登录</strong></h3>
		</div>
		<div class="panel-body">
			<form id="form-login" action="/login.php" method="post">
			<div class="row login-form">
				<div class="col-md-4 text-right form-label"><label for="account">手机号/邮箱</label></div>
				<div class="col-md-8 text-left"><input type="text" id="account" value="" maxlength="45" class="form-control" placeholder="请输入手机号或邮箱" required="required" /></div>
			</div>
			<hr />
			<div class="row login-form">
				<div class="col-md-4 text-right form-label"><label for="password">密码</label></div>
				<div class="col-md-8 text-left"><input type="password" id="password" value="" maxlength="45" class="form-control" required="required" /></div>
			</div>
			<hr />
			<button type="submit" class="btn btn-primary btn-lg">登录</button>
			</form>
		</div>
	</div>
</div>
<script type="text/javascript">
jQuery(function(){
	jQuery('#form-login').submit(function(){
		var t = this;
		var action = jQuery(t).attr('action');
		var account = jQuery.trim(jQuery('#account').val());
		var password = jQuery('#password').val();
		jQuery.ajax({
			"url":action,
			"type":"POST",
			"data":{"account":account,"password":hex_md5(password)},
			"success":function(data){
				if (typeof(data.errcode) == 'undefined') {
					alert('网络错误，请重试');
					return false;
				}
				if (data.errcode > 0) {
					alert(data.errmsg);
					return false;
				}
				window.location.href = '/pc';
				return false;
			}
		});
		return false;
	});
});
</script>
</body>
</html>
EOF;
	}

}
