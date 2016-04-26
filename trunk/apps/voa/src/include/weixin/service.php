<?php
/**
 * 接收来自微信的消息
 * $Author$
 * $Id$
 */

class voa_weixin_service extends voa_weixin_base {
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
	 */
	protected $_event_types = array(
		'subscribe', 'unsubscribe', 'scan', 'location', 'click', 'view'
	);
	// 完整的消息信息数组
	public $msg = array();
	// 当前消息类型
	public $msg_type;
	// 当前事件 event 值
	public $event;
	// 接收方微信号（openId）
	public $to_user_name;
	// 发送方微信号
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

	// 把驼峰转成以下划线分隔, 如:MsgType => msg_type
	public function convert_key($key) {

		$key{0} = rstrtolower($key{0});
		$key = preg_replace("/([A-Z]+)/es", "_\\1", $key);
		return rstrtolower($key);
	}

	/**
	 * 接收消息
	 * @param bool $force 是否强制重新读取数据
	 */
	public function recv($force = false) {

		// 如果已经取过信息了, 则
		if (!empty($this->msg) && !$force) {
			return $this->msg;
		}

		// 接收并把 xml 解析成数组
		$this->msg = $this->recv_msg();

		// 如果数组为空, 则
		if (!$this->msg) {
			logger::error('msg is empty.');
			return false;
		}

		// 数组下标转成小写
		$msg = array();
		foreach ($this->msg as $key => $val) {
			$key = $this->convert_key($key);
			$msg[$key] = $val;
		}

		$this->msg = $msg;
		// 如果消息类型不对, 则
		if (!in_array(rstrtolower($this->msg['msg_type']), $this->_msg_types)) {
			logger::error("msg_type error:".vxml::array2xml($this->msg));
			return false;
		}

		// 如果是事件类型, 则判断 event 值是否处在
		if (self::MSG_TYPE_EVENT == $this->msg['msg_type']) {
			$this->msg['event'] = rstrtolower($this->msg['event']);
			if (in_array($this->msg['event'], $this->_event_types)) {
				$this->event = $this->msg['event'];
			} else {
				logger::error("event error:".$this->msg['event']);
				return false;
			}
		}

		// 接收的公众号不是当前公众号, 则
		if ($this->msg['to_user_name'] != $this->_open_id) {
			logger::error("to_user_name error:".vxml::array2xml($this->msg));
			return false;
		}

		// 消息去重
		if (!$this->is_unique()) {
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

		$serv = &service::factory('voa_s_oa_wxmp_msg', array('pluginid' => 0));
		if (isset($this->msg['msg_id'])) {
			$result = $serv->get($this->msg['msg_id']);
			if (empty($result)) {
				$this->insert_weixin_msg();
				return true;
			}

			return false;
		}

		$result = $serv->get_by_conds(array(
			'fromusername' => $this->msg['from_user_name'],
			'createtime' => $this->msg['create_time'])
		);
		if (empty($result)) {
			$this->insert_weixin_msg();
			return true;
		}

		return false;
	}

	// 微信消息入库
	public function insert_weixin_msg() {

		if (empty($this->msg) || empty($this->_xml_from_wx)) {
			return false;
		}

		$serv = &service::factory('voa_s_oa_wxmp_msg', array('pluginid' => 0));
		$serv->insert(array(
			'msgid' => $this->msg['msg_id'],
			'fromusername' => $this->msg['from_user_name'],
			'createtime' => $this->msg['create_time'],
			'msg' => $this->_xml_from_wx
		));
	}

	/**
	 * 被动响应文本信息
	 * @param string $content 文本内容
	 */
	public function response_text($content) {

		// 判断是否有来源用户
		if (!isset($this->from_user_name)) {
			return false;
		}

		$txt = new voa_weixin_text($this);
		// 响应消息必须的信息
		$data = array(
			'to_user_name' => $this->from_user_name,
			'from_user_name' => $this->to_user_name,
			'create_time' => startup_env::get('timestamp'),
			'content' => $content
		);
		if (!$txt->response($data)) {
			return false;
		}

		return true;
	}

	/**
	 * 主动发送文本信息
	 * @param string $content 文本信息
	 * @param string $to 接收用户
	 */
	public function post_text($content, $to) {

		return $this->post_to('voa_weixin_text', $content, $to);
	}

	/**
	 * 获取二维码 ticket
	 * @param string $url 二维码url地址
	 * @param int $scene_id 场景id, 生成二维码的必要参数
	 */
	public function get_qrcode(&$url, $scene_id) {

		$ticket = new voa_weixin_qrcode($this);
		if (false == $this->get_access_token()) {
			return false;
		}

		if (!$ticket->get_qrcode_url($url, $scene_id, $this->_access_token)) {
			return false;
		}

		return true;
	}

	// 获取网页来源的 openid
	public function get_web_openid(&$openid) {

		if (!$this->get_web_access_token()) {
			return false;
		}

		$openid = $this->web_token->openid;
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

	public function oauth_url_userinfo($url, $state = '') {
		return parent::_oauth_url($url, 'snsapi_userinfo', $state);
	}

	/**
	 * 主动发送信息
	 * @param string $class_name 处理类名称
	 * @param mixed $data 文本信息
	 * @param string $to 接收用户
	 * @param string $method 处理方法
	 */
	public function post_to($class_name, $data, $to, $method = 'post') {

		$class = new $class_name($this);
		// 获取 token
		if (!$this->get_access_token()) {
			return false;
		}

		if (!$class->$method($data, $to_users, $this->_access_token)) {
			return false;
		}

		return true;
	}
}
