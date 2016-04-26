<?php
/**
 * 接收来自微信公众号开放平台网关的消息
 * $Author$
 * $Id$
 */

class voa_weixinopen_service extends voa_weixinopen_abstract {

	// 完整的消息信息数组
	public $msg = array();
	// 当前消息类型
	public $info_type;
	/**
	 * 所有可能的消息类型
	 * component_verify_ticket: ticket 消息
	 * unauthorized: 取消授权消息
	 */
	protected $_info_types = array(
		'component_verify_ticket', 'unauthorized'
	);


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
		// 如果已经取过信息了, 则
		if (!empty($this->msg) && !$force) {
			return $this->msg;
		}

		// 接收并把 xml 解析成数组
		$this->msg = $this->recv_msg();

		// 如果数组为空, 则
		if (!$this->msg) {
			logger::error('qywx msg is empty.');
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
		if (!in_array(rstrtolower($this->msg['info_type']), $this->_info_types)) {
			logger::error("info_type error:".vxml::array2xml($this->msg));
			return false;
		}

		// 记录主要信息
		$this->info_type = rstrtolower($this->msg['info_type']);
		return $this->msg;
	}

	/**
	 * 获取授权链接
	 * @param string $url 目标地址
	 * @param string $scope 授权作用域, snsapi_base: 只能获取 openid; snsapi_userinfo: 可以获取用户详细信息
	 * @param string $state 自定义参数
	 */
	public function oauth_url($url, $appid, $scope = 'snsapi_base', $state = '') {
		return parent::_oauth_url($url, $appid, $scope, $state);
	}

	public function oauth_url_base($url, $appid, $state = '') {
		return parent::_oauth_url($url, $appid, 'snsapi_base', $state);
	}

}
