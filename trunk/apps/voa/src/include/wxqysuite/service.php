<?php
/**
 * 接收来自微信企业套件网关的消息
 * $Author$
 * $Id$
 */

class voa_wxqysuite_service extends voa_wxqysuite_base {
	// 完整的消息信息数组
	public $msg = array();
	// 当前消息类型
	public $info_type;
	/**
	 * 所有可能的消息类型
	 * suite_ticket: ticket 消息
	 * change_auth: 更改授权消息
	 * cancel_auth: 取消授权消息
	 */
	protected $_info_types = array(
		'suite_ticket', 'change_auth', 'cancel_auth'
	);
	// suite_id
	public $suite_id = '';


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

		// 消息入库
		$this->insert_wxqy_msg();

		// 记录主要信息
		$this->info_type = rstrtolower($this->msg['info_type']);
		$this->suite_id = $this->msg['suite_id'];
		return $this->msg;
	}

	// 微信消息入库
	public function insert_wxqy_msg() {
		if (empty($this->msg)) {
			return false;
		}

		$serv = &service::factory('voa_s_uc_weixin_msg');
		$serv->insert(array(
			'wm_msg' => $this->_xml_from_wx
		));
	}

}
