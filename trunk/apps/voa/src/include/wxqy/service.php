<?php
/**
 * 接收来自微信企业网关的消息
 * $Author$
 * $Id$
 */

class voa_wxqy_service extends voa_wxqy_base {
	/**
	 * 所有可能的消息类型
	 * text: 文本消息
	 * image: 图片消息
	 * voice: 语言消息
	 * video: 视频消息
	 * location: 地理位置消息
	 * link: 链接消息
	 * event: 事件消息
	 */
	protected $_msg_types = array(
		'text', 'image', 'voice', 'video', 'location', 'link', 'event'
	);
	/** 事件消息类型值 */
	const MSG_TYPE_EVENT = 'event';
	/**
	 * 事件消息的事件名称(Event)类型
	 * subscribe: 用户未关注时, 进行关注后的事件推送, EventKey: 事件KEY值, qrscene_ 为前缀, 后面为二维码的参数值
	 * unsubscribe: 取消订阅事件
	 * scan: 用户已关注时的事件推送, EventKey: 事件KEY值，是一个32位无符号整数，即创建二维码时的二维码 scene_id
	 * location: 上报地理位置事件
	 * click: 自定义菜单事件, EventKey: 事件KEY值，与自定义菜单接口中KEY值对应
	 * verify: 身份认证消息
	 */
	protected $_event_types = array(
		'subscribe', 'unsubscribe', 'scan', 'location', 'click', 'view', 'verify'
	);
	/** 完整的消息信息数组 */
	public $msg = array();
	/** 当前消息类型 */
	public $msg_type;
	/** 当前事件 event 值 */
	public $event;
	/** 微信企业公众号 */
	public $to_user_name;
	/** 来源用户id(提交到微信企业平台的id, 即用户的唯一标识) */
	public $from_user_name;

	static function &instance() {
		static $object;
		if(empty($object)) {
			$object	= new self();
		}

		return $object;
	}

	public function __construct() {
		parent::__construct();
	}

	/**
	 * 接收消息
	 * @param bool $force 是否强制重新读取数据
	 */
	public function recv($force = false) {
		/** 如果已经取过信息了, 则 */
		if (!empty($this->msg) && !$force) {
			return $this->msg;
		}

		/** 接收并把 xml 解析成数组 */
		$this->msg = $this->recv_msg();

		/** 如果数组为空, 则 */
		if (!$this->msg) {
			logger::error('qywx msg is empty.');
			return false;
		}

		/** 数组下标转成小写 */
		$msg = array();
		foreach ($this->msg as $key => $val) {
			$key = $this->convert_key($key);
			$msg[$key] = $val;
		}

		$this->msg = $msg;
		/** 如果消息类型不对, 则 */
		if (!in_array(rstrtolower($this->msg['msg_type']), $this->_msg_types)) {
			logger::error("msg_type error:".vxml::array2xml($this->msg));
			return false;
		}

		/** 如果是事件类型, 则判断 event 值是否处在 */
		if (self::MSG_TYPE_EVENT == $this->msg['msg_type']) {
			$this->msg['event'] = rstrtolower($this->msg['event']);
			if (in_array($this->msg['event'], $this->_event_types)) {
				$this->event = $this->msg['event'];
			} else {
				logger::error("event error:".$this->msg['event']);
				return false;
			}
		}

		/** 接收的公众号不是当前公众号, 则 */
		if ($this->msg['to_user_name'] != $this->_open_id) {
			logger::error("to_user_name error:".vxml::array2xml($this->msg));
			return false;
		}

		/** 消息去重 */
		if (!$this->is_unique()) {
			return false;
		}

		/** 记录主要信息 */
		$this->msg_type = rstrtolower($this->msg['msg_type']);
		$this->to_user_name = $this->msg['to_user_name'];
		$this->from_user_name = $this->msg['from_user_name'];
		return $this->msg;
	}

	/** 消息去重 */
	public function is_unique() {

		$serv = &service::factory('voa_s_oa_weixin_msg', array('pluginid' => 0));
		if (isset($this->msg['msg_id'])) {
			$result = $serv->fetch_by_msgid($this->msg['msg_id']);
			if (empty($result)) {
				$this->insert_wxqy_msg();
				return true;
			}

			return false;
		}

		return true;
		$result = $serv->fetch_by_openid_createtime($this->msg['from_user_name'], $this->msg['create_time']);
		if (empty($result)) {
			$this->insert_wxqy_msg();
			return true;
		}

		return false;
	}

	/** 微信消息入库 */
	public function insert_wxqy_msg() {
		if (empty($this->msg) || empty($this->_xml_from_wx)) {
			return false;
		}

		$serv = &service::factory('voa_s_oa_weixin_msg', array('pluginid' => 0));
		$serv->insert(array(
			'wm_msgid' => $this->msg['msg_id'],
			'wm_fromusername' => $this->msg['from_user_name'],
			'wm_createtime' => $this->msg['create_time'],
			'wm_msg' => $this->_xml_from_wx
		));
	}

	/**
	 * 被动响应文本信息
	 * @param string $content 文本内容
	 */
	public function response_text($content, $encode = false) {
		/** 判断是否有来源用户 */
		if (!isset($this->from_user_name)) {
			return false;
		}

		$txt = new voa_wxqy_text($this);
		/** 响应消息必须的信息 */
		$data = array(
			'to_user_name' => $this->from_user_name,
			'from_user_name' => $this->to_user_name,
			'create_time' => startup_env::get('timestamp'),
			'content' => $content
		);
		if (!$txt->response($data, $encode)) {
			return false;
		}

		return true;
	}

	/**
	 * 更新 appmsg 状态
	 * @param array $data appmsg消息
	 *  + sid 消息唯一标识
	 *  + status 消息的状态(utf-8), 长度 64 位
	 * @param array $to_users
	 */
	public function update_appmsg($data, $agentid, $to_users) {
		throw new Exception('appmsg is not exist.');
		return $this->_post_to('voa_wxqy_appmsg', $data, $agentid, $to_users, 'update');
	}

	/**
	 * 主动发送 appmsg 消息
	 * @param array $data appmsg 消息
	 *  + sid 消息唯一标识
	 *  + image 图片资源id
	 *  + title 标题
	 *  + description 描述
	 *  + url 点击后跳转的url
	 *  + status 消息的状态(utf-8), 长度 64 位
	 * @param array $to_users 接收用户
	 */
	public function post_appmsg($data, $agentid, $to_users) {
		throw new Exception('appmsg is not exist.');
		return $this->_post_to('voa_wxqy_appmsg', $data, $agentid, $to_users);
	}

	/**
	 * 主动发送文本信息
	 * @param string $content 文本信息
	 * @param array $to_users 接收用户
	 */
	public function post_text($content, $agentid, $to_users, $to_partys = array()) {
		return $this->_post_to('voa_wxqy_text', $content, $agentid, $to_users, $to_partys);
	}

	/**
	 * 主动发送图文混排信息
	 * @param array $data 图文混排信息
	 *  + title 标题
	 *  + description 描述
	 *  + url 点击后跳转的url
	 *  + picurl 图文消息url, 支持 jpg, png 格式, 大小:640 * 320, 80 * 80
	 * @param array $to_users 接收用户
	 */
	public function post_news($data, $agentid, $to_users, $to_partys = array()) {
		return $this->_post_to('voa_wxqy_news', $data, $agentid, $to_users, $to_partys);
	}

	/**
	 * 主动发送图片消息
	 * @param array $data 图片信息
	 * @param int $agentid 应用id
	 * @param array $to_users 用户id
	 * @param array $topartys 部门id
	 * @return boolean
	 */
	public function post_image($msg, $agentid, $to_users, $topartys = array()) {

		$data = array(
			'image' => $msg,
			'msgtype' => 'image'
		);
		return $this->_post_to('voa_wxqy_media', $data, $agentid, $to_users, $to_partys);
	}

	/**
	 * 主动发送声音消息
	 * @param array $data 声音信息
	 * @param int $agentid 应用id
	 * @param array $to_users 用户id
	 * @param array $topartys 部门id
	 * @return boolean
	 */
	public function post_voice($msg, $agentid, $to_users, $topartys = array()) {

		$data = array(
			'voice' => $msg,
			'msgtype' => 'voice'
		);
		return $this->_post_to('voa_wxqy_media', $data, $agentid, $to_users, $to_partys);
	}

	/**
	 * 主动发送视频消息
	 * @param array $data 视频信息
	 * @param int $agentid 应用id
	 * @param array $to_users 用户id
	 * @param array $topartys 部门id
	 * @return boolean
	 */
	public function post_video($msg, $agentid, $to_users, $topartys = array()) {

		$data = array(
			'video' => $msg,
			'msgtype' => 'video'
		);
		return $this->_post_to('voa_wxqy_media', $data, $agentid, $to_users, $to_partys);
	}

	/**
	 * 主动发送文件消息
	 * @param array $data 文件信息
	 * @param int $agentid 应用id
	 * @param array $to_users 用户id
	 * @param array $topartys 部门id
	 * @return boolean
	 */
	public function post_file($msg, $agentid, $to_users, $topartys = array()) {

		$data = array(
			'file' => $msg,
			'msgtype' => 'file'
		);
		return $this->_post_to('voa_wxqy_media', $data, $agentid, $to_users, $to_partys);
	}

	/**
	 * 主动发送信息
	 * @param string $class_name 处理类名称
	 * @param string $news 文本信息
	 * @param string $to_users 接收用户
	 * @param string $method 处理方法
	 */
	protected function _post_to($class_name, $data, $agentid, $to_users, $to_partys, $method = 'post') {
		$class = new $class_name($this);
		/** 获取 token */
		if (!$this->get_access_token()) {
			return false;
		}

		if (!$class->$method($data, $agentid, $this->_access_token, $to_users, $to_partys)) {
			return false;
		}

		return true;
	}

	/**
	 * 获取媒体文件
	 * @param string &$data 文件内容信息
	 * <pre>
	 * + file_name 文件名
	 * + content_type 文件类型
	 * + file_data 文件数据流（经base64_encode）
	 * </pre>
	 * @param string $media_id
	 * @param int $errcode (引用结果)错误码
	 * @param string $errmsg (引用结果)错误信息
	 * @return boolean
	 */
	public function get_media(&$data, $media_id, &$errcode = 0, &$errmsg = '') {
		/** 获取token */
		if (!$this->get_access_token()) {
			$this->errcode = 109;
			$this->errmsg = 'Token error';
			return false;
		}

		$media = new voa_wxqy_media($this);
		if (!$media->get($data, $media_id, $this->_access_token)) {
			$this->errcode = $media->errcode;
			$this->errmsg = $media->errmsg;
			return false;
		}

		return true;
	}

	/**
	 * 上传媒体文件
	 * @param array $data (引用结果)上传后返回的结果
	 * - type
	 * - media_id
	 * - created_at
	 * @param string $type 上传的媒体类型，image=图片,video=视频,voice=音频，file=普通文件
	 * @param string $local_file 待上传的文件本地路径
	 * @param number $errcode (引用结果)错误码
	 * @param string $errmsg (引用结果)错误信息
	 * @return boolean
	 */
	public function upload_media(&$data, $type, $local_file, &$errcode = 0, &$errmsg = '') {

		// 获取token
		if (!$this->get_access_token()) {
			return false;
		}

		// 载入媒体管理服务类
		$media = new voa_wxqy_media($this);

		// 上传结果
		$res = false;
		switch ($type) {
			case voa_wxqy_media::TYPE_IMAGE:
				$res = $media->upload_image($data, $this->_access_token, $local_file);
				break;
			case voa_wxqy_media::TYPE_FILE:
				$res = $media->upload_file($data, $this->_access_token, $local_file);
				break;
			case voa_wxqy_media::TYPE_VIDEO:
				$res = $media->upload_video($data, $this->_access_token, $local_file);
				break;
			case voa_wxqy_media::TYPE_VOICE:
				$res = $media->upload_voice($data, $this->_access_token, $local_file);
				break;
		}

		// 成功
		if ($res) {
			return true;
		}

		// 未成功
		if ($media->errcode) {
			$errcode = $media->errcode;
			$errmsg = $media->errmsg;
		} else {
			$errcode = '-1';
			$errmsg = '未知的媒体类型';
		}

		return false;
	}

	/**
	 * 推送微信企业号信息
	 * @param string $type
	 * @param array|string $data
	 * @param array $to_users
	 * @param array $to_partys
	 * @param string $method
	 * @return boolean
	 */
	public function push_qymsg($type, $data, $agentid, $to_users, $to_partys = array(), $method = 'post') {
		$class_name = 'voa_wxqy_'.$type;
		/** 数据格式转换 */
		$data = is_array($data) ? $data : unserialize($data);
		$json = rjson_encode($data);
		$res = array();
		if (!voa_h_func::get_json_by_post($res, $agentid, sprintf(self::POST_URL, $token), $json)) {
			return false;
		}

		return true;
	}

	/**
	 * 获取网页来源的 openid
	 * @param string $openid
	 */
	public function get_web_openid(&$openid) {
		if (!$this->get_access_token()) {
			return false;
		}

		$cr = controller_request::get_instance();
		$code = (string)$cr->get('code');
		if (empty($code)) {
			return false;
		}

		if (!$this->get_user_info($this->_access_token, $code)) {
			return false;
		}

		$openid = $this->userinfo['user_id'];
		return true;
	}

	/**
	 * 获取授权链接
	 * @param string $url 目标地址
	 * @param string $scope 授权作用域, snsapi_base: 只能获取 openid; snsapi_userinfo: 可以获取用户详细信息
	 * @param string $state 自定义参数
	 */
	public function oauth_url($url, $scope = 'snsapi_base', $state = '') {
		return parent::_oauth_url($url, $scope, $state);
	}

	public function oauth_url_base($url, $state = '') {
		return parent::_oauth_url($url, 'snsapi_base', $state);
	}

	/**
	 * 用于测试cropid和corpsecret是否匹配且合法
	 * @param string $corpid
	 * @param string $corpsecret
	 * @return boolean
	 */
	public function corpid_corpsecret_testing($corpid, $corpsecret) {

		$old_corpid = $this->_corp_id;
		$old_corpsecret = $this->_corp_secret;

		$this->_corp_id = $corpid;
		$this->_corp_secret = $corpsecret;

		if ($this->get_access_token(true)) {
			$success = true;
		} else {
			$success = false;
		}

		$this->_corp_id = $old_corpid;
		$this->_corp_secret = $old_corpsecret;

		return $success;
	}

	/**
	 * 生成jsapi的签名信息
	 * @param string $url 访问jsapi的页面url，为空则使用当前页面url
	 * @return array
	 * <pre>
	 * + timestamp 生成签名的时间戳
	 * + nonce_str 生成签名的随机字符串
	 * + signature 生成的签名字符串
	 * + rawhash 生成加密字符串的原始字符串，用于调试
	 * + corpid 企业号corpid
	 * + url 调用jsapi的url
	 * </pre>
	 * @link http://qydev.weixin.qq.com/wiki/index.php?title=%E5%BE%AE%E4%BF%A1JS%E6%8E%A5%E5%8F%A3#.E9.99.84.E5.BD.951-JS-SDK.E4.BD.BF.E7.94.A8.E6.9D.83.E9.99.90.E7.AD.BE.E5.90.8D.E7.AE.97.E6.B3.95
	 */
	public function jsapi_signature($url = null) {

		// 未指定则 生成当前页面的 url
		if ($url === null || !$url) {
			$url = config::get('voa.oa_http_scheme').$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		} else {
			// 过滤#以及以后的部分
			$url = preg_replace('/#.+?$/', '', $url);
		}

		// 企业号 corpid
		$corpid = $this->_corp_id;
		// 当前时间戳
		$timestamp = startup_env::get('timestamp');
		// 16位随机字符串
		$nonce_str = '';
		$_chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		for ($i = 0; $i < 16; $i++) {
			$nonce_str .= substr($_chars, mt_rand(0, strlen($_chars) - 1), 1);
		}
		unset($_chars);
		// js api ticket
		$this->get_jsapi_ticket();

		// 需要 进行sha1加密的字段
		$array_hash = array(
			'jsapi_ticket' => $this->_jsapi_ticket,
			'noncestr' => $nonce_str,
			'timestamp' => $timestamp,
			'url' => $url
		);
		// 需要按照键名顺序
		ksort($array_hash, SORT_STRING);
		$str_hash = '';
		$_comma = '';
		foreach ($array_hash as $_k => $_v) {
			$str_hash .= $_comma."{$_k}={$_v}";
			$_comma = '&';
		}
		unset($_k, $_v, $_comma);
		// 生成js-sdk签名
		$signature = sha1($str_hash);

		return array(
			'timestamp' => $timestamp,
			'nonce_str' => $nonce_str,
			'signature' => $signature,
			'rawhash' => $str_hash,
			'corpid' => $corpid,
			'url' => $url
		);
	}

	/**
	 * 生成获取jsapi位置信息签名（兼容微信6.0.2之前的版本）
	 * @param string $url 使用该接口的页面url，如果为null则系统自动判断
	 * @return array
	 * <pre>
	 * + timestamp 生成签名时的时间戳
	 * + nonce_str 生成签名的随即字符串
	 * + signature 签名
	 * + rawhash 生成加密字符串的原始字符串，用于调试
	 * + corpid 企业corpid
	 * + token token
	 * + url 调用jsapi位置接口的页面url
	 * </pre>
	 * @link http://qydev.weixin.qq.com/wiki/index.php?title=%E5%BE%AE%E4%BF%A1JS%E6%8E%A5%E5%8F%A3#.E9.99.84.E5.BD.954-.E4.BD.8D.E7.BD.AE.E7.AD.BE.E5.90.8D.E7.94.9F.E6.88.90.E7.AE.97.E6.B3.95
	 */
	public function jsapi_addr_signature($url = null) {

		// 企业号 corpid
		$corpid = $this->_corp_id;
		// 当前时间戳
		$timestamp = startup_env::get('timestamp');
		// 16位随机字符串
		$nonce_str = '';
		$_chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		for ($i = 0; $i < 16; $i++) {
			$nonce_str .= substr($_chars, mt_rand(0, strlen($_chars) - 1), 1);
		}
		unset($_chars);
		// 获取token
		$this->get_access_token();

		// 需要 进行sha1加密的字段
		$array_hash = array(
			'corpid' => $corpid,
			'noncestr' => $nonce_str,
			'timestamp' => $timestamp,
			'url' => $url,
			'accesstoken' => $this->_access_token
		);
		// 需要按照键名顺序
		ksort($array_hash, SORT_STRING);
		$str_hash = '';
		$_comma = '';
		foreach ($array_hash as $_k => $_v) {
			$str_hash .= $_comma."{$_k}={$_v}";
			$_comma = '&';
		}
		unset($_k, $_v, $_comma);
		// 生成js-sdk签名
		$signature = sha1($str_hash);

		return array(
			'timestamp' => $timestamp,
			'nonce_str' => $nonce_str,
			'signature' => $signature,
			'rawhash' => $str_hash,
			'corpid' => $corpid,
			'token' => $this->_access_token,
			'url' => $url
		);

		return array();
	}

}
