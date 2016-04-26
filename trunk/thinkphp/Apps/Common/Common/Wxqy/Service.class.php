<?php
/**
 * 企业号接口, 非套件
 * Service.php
 * $author$
 */

namespace Common\Common\Wxqy;
use Think\Log;

class Service extends Base {

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
	// 事件消息类型值
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
	// 完整的消息信息数组
	public $msg = array();
	// 当前消息类型
	public $msg_type;
	// 当前事件 event 值
	public $event;
	// 接收方微信企业号的 openId
	public $to_user_name;
	// 发送方微信企业号的 corpid
	public $from_user_name;

	public static function &instance() {

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
	 *
	 * @param bool $force 是否强制重新读取数据
	 */
	public function recv($force = false) {

		// 如果已经取过信息了, 则
		if (! empty($this->msg) && ! $force) {
			return $this->msg;
		}

		// 接收并把 xml 解析成数组
		$this->msg = $this->recv_msg();

		// 如果数组为空, 则
		if (! $this->msg) {
			Log::record('msg is empty.');
			return false;
		}

		// 数组下标转成小写
		$msg = array();
		foreach ($this->msg as $key => $val) {
			$key = convert_camel_underscore($key);
			$msg[$key] = $val;
		}

		$this->msg = $msg;
		// 如果消息类型不对, 则
		if (! in_array(rstrtolower($this->msg['msg_type']), $this->_msg_types)) {
			Log::record("msg_type error:" . vxml::array2xml($this->msg));
			return false;
		}

		// 如果是事件类型, 则判断 event 值是否处在
		if (self::MSG_TYPE_EVENT == $this->msg['msg_type']) {
			$this->msg['event'] = rstrtolower($this->msg['event']);
			if (in_array($this->msg['event'], $this->_event_types)) {
				$this->event = $this->msg['event'];
			} else {
				Log::record("event error:" . $this->msg['event']);
				return false;
			}
		}

		// 接收的微信企业号不是当前企业号, 则
		if ($this->msg['to_user_name'] != $this->_open_id) {
			Log::record("to_user_name error:" . vxml::array2xml($this->msg));
			return false;
		}

		// 消息去重
		if ($this->is_unique()) {
			return false;
		}

		// 记录主要信息
		$this->msg_type = rstrtolower($this->msg['msg_type']);
		$this->to_user_name = $this->msg['to_user_name'];
		$this->from_user_name = $this->msg['from_user_name'];
		return $this->msg;
	}

	// 消息去重
	public function is_unique() {

		$serv_wm = D('Common/WeixinMsg', 'Service');
		// 如果消息中有 msg_id, 则
		if (isset($this->msg['msg_id'])) {
			// 根据 msg_id 读取数据
			if (!$result = $serv_wm->get($this->msg['msg_id'])) {
				$this->insert_wxqy_msg();
				return false;
			}

			return true;
		}

		return false;
		// 根据用户名和创建时间读取记录
		$conds = array(
			'fromusername' => $this->msg['from_user_name'],
			'createtime' => $this->msg['create_time']
		);
		if (!$result = $serv_wm->get_by_conds($conds)) {
			$this->insert_wxqy_msg();
			return false;
		}

		return true;
	}

	// 微信消息入库
	public function insert_wxqy_msg() {

		// 如果消息为空, 或者 XML 为空
		if (empty($this->msg) || empty($this->_xml_from_wx)) {
			return false;
		}

		// 消息信息入库
		$serv_wxmp = D('Common/WeixinMsg', 'Service');
        $serv_wxmp->insert(array(
			'wm_msgid' => $this->msg['msg_id'],
			'wm_fromusername' => $this->msg['from_user_name'],
			'wm_createtime' => $this->msg['create_time'],
			'wm_msg' => $this->_xml_from_wx
		));
		return true;
	}

	/**
	 * 被动响应文本信息
	 * @param string $content 文本内容
	 * @param boolean $encode 是否加密
	 */
	public function response_text($content, $encode = false) {

		// 判断是否有来源用户
		if (!isset($this->from_user_name)) {
			return false;
		}

		$txt = new \Common\Common\Wxqy\Text($this);
		// 响应消息必须的信息
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
	 *  + string sid 消息唯一标识
	 *  + string status 消息的状态(utf-8), 长度 64 位
	 * @param array $to_users
	 */
	public function update_appmsg($data, $agentid, $to_users) {

		E('appmsg is not exist.');
		return $this->_post_to('\Common\Common\Wxqy\Appmsg', $data, $agentid, $to_users, 'update');
	}

	/**
	 * 主动发送 appmsg 消息
	 * @param array $data appmsg 消息
	 *  + string sid 消息唯一标识
	 *  + string image 图片资源id
	 *  + string title 标题
	 *  + string description 描述
	 *  + string url 点击后跳转的url
	 *  + string status 消息的状态(utf-8), 长度 64 位
	 * @param array $to_users 接收用户
	 */
	public function post_appmsg($data, $agentid, $to_users) {

		E('appmsg is not exist.');
		return $this->_post_to('\Common\Common\Wxqy\Appmsg', $data, $agentid, $to_users);
	}

	/**
	 * 主动发送文本信息
	 * @param string $content 文本信息
	 * @param int $agentid 企业号应用ID
	 * @param array $to_users 接收用户
	 * @param array $to_partys 接收的部门
	 */
	public function post_text($content, $agentid, $to_users, $to_partys = array()) {

		return $this->_post_to('\Common\Common\Wxqy\Text', $content, $agentid, $to_users, $to_partys);
	}

	/**
	 * 主动发送图文混排信息
	 * @param array $data 图文混排信息
	 *  + string title 标题
	 *  + string description 描述
	 *  + string url 点击后跳转的url
	 *  + string picurl 图文消息url, 支持 jpg, png 格式, 大小:640 * 320, 80 * 80
	 * @param array $to_users 接收用户
	 */
	public function post_news($data, $agentid, $to_users, $to_partys = array()) {

		return $this->_post_to('\Common\Common\Wxqy\News', $data, $agentid, $to_users, $to_partys);
	}

	/**
	 * 主动发送图片信息
	 * @param array $msg 图片信息
	 *  + string media_id 图片id
	 * @param array $to_users 接收用户
	 */
	public function post_image($msg, $agentid, $to_users, $to_partys = array()) {

		$data = array(
			'image' => $msg,
			'msgtype' => 'image'
		);
		return $this->_post_to('\Common\Common\Wxqy\Media', $data, $agentid, $to_users, $to_partys);
	}

	/**
	 * 主动发送声音信息
	 * @param array $msg 声音信息
	 *  + string media_id 图片id
	 * @param array $to_users 接收用户
	 */
	public function post_voice($msg, $agentid, $to_users, $to_partys = array()) {

		$data = array(
			'voice' => $msg,
			'msgtype' => 'voice'
		);
		return $this->_post_to('\Common\Common\Wxqy\Media', $data, $agentid, $to_users, $to_partys);
	}

	/**
	 * 主动发送视频信息
	 * @param array $msg 视频信息
	 *  + string media_id 图片id
	 * @param array $to_users 接收用户
	 */
	public function post_video($msg, $agentid, $to_users, $to_partys = array()) {

		$data = array(
			'video' => $msg,
			'msgtype' => 'video'
		);
		return $this->_post_to('\Common\Common\Wxqy\Media', $data, $agentid, $to_users, $to_partys);
	}

	/**
	 * 主动发送文件信息
	 * @param array $msg 文件信息
	 *  + string media_id 图片id
	 * @param array $to_users 接收用户
	 */
	public function post_file($msg, $agentid, $to_users, $to_partys = array()) {

		$data = array(
			'file' => $msg,
			'msgtype' => 'file'
		);
		return $this->_post_to('\Common\Common\Wxqy\Media', $data, $agentid, $to_users, $to_partys);
	}

	/**
	 * 主动发送信息
	 * @param string $class_name 处理类名称
	 * @param string $news 文本信息
	 * @param string $to_users 接收用户
	 * @param string $method 处理方法
	 */
	protected function _post_to($class_name, $data, $agentid, $to_users, $to_partys = array(), $method = 'post') {

		$class = new $class_name($this);
		// 获取 token
		if (!$this->get_access_token()) {
			return false;
		}

		// 调用发送的方法
		if (!$class->$method($data, $agentid, $to_users, $to_partys)) {
			return false;
		}

		return true;
	}

	/**
	 * 获取指定应用的菜单
	 * @param array $menus 菜单
	 * @param int $agentid 应用ID
	 * @return boolean
	 */
	public function get_menu(&$menus, $agentid) {

		return $this->__menu($menus, 'get', $agentid);
	}

	/**
	 * 删除应用菜单
	 * @param int $agentid 应用ID
	 * @return boolean
	 */
	public function delete_menu($agentid) {

		$result = array();
		return $this->__menu($result, 'delete', $agentid);
	}

	/**
	 * 创建应用菜单
	 * @param array $menus 菜单信息
	 * @param int $agentid 应用ID
	 * @param number $pluginid 插件ID
	 * @return boolean
	 */
	public function create_menu($menus, $agentid, $pluginid = 0) {

		return $this->__menu($menus, 'create', $agentid, $pluginid);
	}

	/**
	 * 菜单操作
	 * @param mixed $result 需要返回的结果集
	 * @return boolean
	 */
	public function __menu(&$result) {

		// 获取参数数组
		$params = func_get_args();
		// 第一个参数为返回值
		$f_return = array_shift($params);
		// 第二个参数为方法名
		$method = $params[0];
		// 替换为返回值
		$params[0] = &$f_return;
		$pluginid = $params[2];
		$agentid = $params[1];
		switch ($method) {
			case 'get': // 获取菜单
			case 'delete': // 删除菜单
				break;
			case 'create':
				// 如果插件ID为 0
				if (empty($pluginid) || 0 == $pluginid) {
					// 获取插件列表
					$cache = &\Common\Common\Cache::instance();
					$plugins = $cache->get('Common.plugin');
					// 遍历插件
					foreach ($plugins as $_plugin) {
						// 判断是否当前的应用ID
						if ($agentid == $_plugin['cp_agentid']) {
							$pluginid = $_plugin['cp_pluginid'];
						}
					}
				}

				break;
			default: return false; break;
		}

		// 初始化菜单操作类
		$class = new \Common\Common\Wxqy\Menu($this);
		// 判断方法是否存在
		if (!method_exists($class, $method)) {
			E(L('_ERR_CLASS_METHOD_IS_NOT_EXIST', array('class' => get_class($class), 'method' => $method)));
			return false;
		}

		// 调用指定方法
		if (!call_user_func_array(array($class, $method), $params)) {
			return false;
		}

		// 返回结果
		$result = $f_return;

		return true;
	}

	/**
	 * 创建部门
	 * @param array $result 操作结果
	 * @param array $data 部门数据
	 * @return boolean
	 */
	public function create_department(&$result, $data) {

		return $this->__department($result, 'department_create', $data);
	}

	/**
	 * 删除部门
	 * @param array $result 操作结果
	 * @param int $id 部门ID
	 * @return boolean
	 */
	public function delete_department(&$result, $dpid) {

		return $this->__department($result, 'department_delete', $dpid);
	}

	/**
	 * 更新部门信息
	 * @param array $result 操作结果
	 * @param array $data 部门信息
	 * @return boolean
	 */
	public function update_department(&$result, $data) {

		return $this->__department($result, 'department_update', $data);
	}

	/**
	 * 获取部门列表
	 * @param array $result 部门列表
	 * @return boolean
	 */
	public function list_department(&$result) {

		return $this->__department($result, 'list_departments');
	}

	/**
	 * 部门信息操作
	 * @param array $result 操作结果
	 * @return boolean
	 */
	private function __addrbook(&$result) {

		// 获取参数数组
		$params = func_get_args();
		// 第一个参数为返回值
		$f_return = array_shift($params);
		// 方法名
		$method = $params[0];
		$params[0] = &$f_return;
		// 初始化菜单操作类
		$class = new \Common\Common\Wxqy\Addrbook($this);
		// 判断方法是否存在
		if (!method_exists($class, $method)) {
			E(L('_ERR_CLASS_METHOD_IS_NOT_EXIST', array('class' => get_class($class), 'method' => $method)));
			return false;
		}

		// 调用指定方法
		if (!call_user_func_array(array($class, $method), $params)) {
			return false;
		}

		// 返回结果
		$result = $f_return;

		return true;
	}

	/**
	 * 获取指定用户信息
	 * @param array $result 用户信息
	 * @param string $userid UserID
	 */
	public function get_user(&$result, $userid) {

		return $this->__addrbook($result, 'user_get', $userid);
	}

	/**
	 * 创建用户
	 * @param array $result 操作结果
	 * @param array $data 用户信息
	 * @param string $update 如果用户存在, 是否自动更新
	 */
	public function create_user(&$result, $data, $update = true) {

		return $this->__addrbook($result, 'user_create', $data, $update);
	}

	/**
	 * 更新用户信息
	 * @param array $result 操作结果
	 * @param array $data 用户信息
	 * @param string $create 如果用户不存在, 是否自动创建
	 */
	public function update_user(&$result, $data, $create = true) {

		return $this->__addrbook($result, 'user_update', $data, $create);
	}

	/**
	 * 删除指定用户
	 * @param array $result 操作结果
	 * @param string $userid UserID
	 */
	public function delete_user(&$result, $userid) {

		return $this->__addrbook($result, 'user_delete', $userid);
	}

	/**
	 * 根据部门读取用户列表
	 * @param array $result 操作结果
	 * @param number $dp_id
	 */
	public function list_user_by_department(&$result, $dp_id = 1) {

		return $this->__addrbook($result, 'department_simple_list', $dp_id);
	}

	/**
	 * 获取媒体文件
	 * @param string &$data 文件内容信息
	 * <pre>
	 * + file_name 文件名
	 * + content_type 文件类型
	 * + file_data 文件数据流（经base64_encode）
	 * </pre>
	 * @param string $media_id 媒体ID
	 * @return boolean
	 */
	public function get_media(&$data, $media_id) {

		// 获取token
		if (!$this->get_access_token()) {
			return false;
		}

		// 获取媒体文件
		$media = new \Common\Common\Wxqy\Media($this);
		if (!$media->get($data, $media_id)) {
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
	 * @return boolean
	 */
	public function upload_media(&$data, $type, $local_file) {

		// 获取token
		if (!$this->get_access_token()) {
			return false;
		}

		// 载入媒体管理服务类
		$media = new \Common\Common\Wxqy\Media($this);
		// 上传结果
		$res = false;
		switch ($type) {
			case voa_wxqy_media::TYPE_IMAGE:
				$res = $media->upload_image($data, $local_file);
				break;
			case voa_wxqy_media::TYPE_FILE:
				$res = $media->upload_file($data, $local_file);
				break;
			case voa_wxqy_media::TYPE_VIDEO:
				$res = $media->upload_video($data, $local_file);
				break;
			case voa_wxqy_media::TYPE_VOICE:
				$res = $media->upload_voice($data, $local_file);
				break;
			default: break;
		}

		// 上传媒体文件失败
		if (!$res) {
			E('_ERR_UPLOAD_MEDIA_FAILED');
			return false;
		}

		return true;
	}

	/**
	 * 获取网页来源的 openid
	 * @param string $openid
	 */
	public function get_web_openid(&$openid) {

		// 获取 access token
		if (!$this->get_access_token()) {
			return false;
		}

		// 获取 code
		$code = I('get.code');
		if (empty($code)) {
			return false;
		}

		// 获取用户信息
		if (!$this->get_user_info($code)) {
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

		// 企业号的 corp_id, corp_secret
		$corps = array($this->_corp_id, $this->_corp_secret);
		$this->_corp_id = $corpid;
		$this->_corp_secret = $corpsecret;
		// 获取 access token
		if ($this->get_access_token(true)) {
			$success = true;
		} else {
			$success = false;
		}

		list($this->_corp_id, $this->_corp_secret) = $corps;
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
	public function jsapi_signature(&$jscfg, $url = null) {

		// 未指定则 生成当前页面的 url
		if (null === $url || empty($url)) {
			$url = boardurl();
		} else {
			// 过滤#以及以后的部分
			$url = preg_replace('/#.+?$/', '', $url);
		}

		// 16位随机字符串
		$nonce_str = random(16);
		// js api ticket
		if (!$this->get_jsapi_ticket()) {
			return false;
		}

		// 需要进行sha1加密的字段
		$hashs = array(
			'jsapi_ticket' => $this->_jsapi_ticket,
			'noncestr' => $nonce_str,
			'timestamp' => NOW_TIME,
			'url' => $url
		);
		// 生成签名
		$signature = '';
		$str_hash = '';
		$this->generate_sig($signature, $str_hash, $hashs);
		// jsapi 所需的配置参数
		$jscfg = array(
			'timestamp' => NOW_TIME,
			'nonce_str' => $nonce_str,
			'signature' => $signature,
			'rawhash' => $str_hash,
			'corpid' => $this->_corp_id,
			'url' => $url
		);

		return true;
	}

	/**
	 * 格式化签到的返回数据
	 *
	 * @param array $cfg 传入的数据
	 * @param array $return 返回正确格式
	 * @return bool
	 */
	public function jsapi_signature_format(&$return, $cfg) {

		if (null == $cfg) {
			return false;
		}

		$return = array(
			'appid' => $cfg['corpid'],
			'timestamp' => $cfg['timestamp'],
			'noncestr' => $cfg['nonce_str'],
			'signature' => $cfg['signature']
		);

		return true;
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
	public function jsapi_addr_signature(&$jscfg, $url = null) {

		// 未指定则 生成当前页面的 url
		if (null === $url || empty($url)) {
			$url = boardurl();
		} else {
			// 过滤#以及以后的部分
			$url = preg_replace('/#.+?$/', '', $url);
		}

		// 16位随机字符串
		$nonce_str = random(16);
		// 获取token
		if (!$this->get_access_token()) {
			return false;
		}

		// 需要进行 sha1 加密的字段, 生成 js-sdk 签名
		$hashs = array(
			'corpid' => $this->_corp_id,
			'noncestr' => $nonce_str,
			'timestamp' => NOW_TIME,
			'url' => $url,
			'accesstoken' => $this->_access_token
		);
		// 生成签名
		$signature = '';
		$str_hash = '';
		$this->generate_sig($signature, $str_hash, $hashs);
		// jsapi 配置参数
		$jscfg = array(
			'timestamp' => NOW_TIME,
			'nonce_str' => $nonce_str,
			'signature' => $signature,
			'rawhash' => $str_hash,
			'corpid' => $this->_corp_id,
			'token' => $this->_access_token,
			'url' => $url
		);

		return true;
	}

	/**
	 * 生成签名
	 * @param string $sig 签名
	 * @param string $str_hash hash 字串
	 * @param array $hashs 生成签名的源数据
	 * @return boolean
	 */
	public function generate_sig(&$sig, &$str_hash, $hashs) {

		// 需要按照键名顺序
		ksort($hashs, SORT_STRING);
		$arr = array();
		// 遍历数据
		foreach ($hashs as $_k => $_v) {
			$arr[] = "{$_k}={$_v}";
		}

		// 生成js-sdk签名
		$str_hash = implode('&', $arr);
		$sig = sha1($str_hash);
		return true;
	}

    /**
     * 生成微信企业支付的openid
     * @param $openid
     * @param $userid
     */
    public function get_pay_openid(&$openid, $userid){
        if(self::convert_to_openid_for_pay($openid, $userid)){
            return true;
        }
        return false;
    }


    /**
     * 生成jsapi的签名信息(解决微信红包二次分享问题)
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
    public function jsapi_signature_for_share(&$jscfg, $url = null) {

        // 未指定则 生成当前页面的 url
        if (null === $url || empty($url)) {
            $url = boardurl();
        } else {
            // 过滤#以及以后的部分
           // $url = preg_replace('/#.+?$/', '', $url);
            $url = urldecode($url);
        }

        // 16位随机字符串
        $nonce_str = random(16);
        // js api ticket
        if (!$this->get_jsapi_ticket()) {
            return false;
        }

        // 需要进行sha1加密的字段
        $hashs = array(
            'jsapi_ticket' => $this->_jsapi_ticket,
            'noncestr' => $nonce_str,
            'timestamp' => NOW_TIME,
            'url' => $url
        );
        // 生成签名
        $signature = '';
        $str_hash = '';
        $this->generate_sig($signature, $str_hash, $hashs);
        // jsapi 所需的配置参数
        $jscfg = array(
            'timestamp' => NOW_TIME,
            'nonce_str' => $nonce_str,
            'signature' => $signature,
            'rawhash' => $str_hash,
            'corpid' => $this->_corp_id,
            'url' => $url
        );

        return true;
    }

}
