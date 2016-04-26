<?php
/**
 * voa_uda_frontend_talk_addlastview
 * 查看最后聊天记录时间
 *
 * $Author$
 * $Id$
 */

class voa_uda_frontend_talk_addlastview extends voa_uda_frontend_talk_abstract {

	public function __construct() {

		parent::__construct();
	}

	/**
	 * 执行
	 * @param array $in 输入
	 * @param array $out 输出
	 * @return boolean
	 */
	public function execute($in, &$out = null) {

		// 输入参数
		$this->_params = $in;

		// 需要提取的参数列表
		$fields = array(
			array('uid', self::VAR_INT, '', null, false), // 用户uid
			array('tv_uid', self::VAR_INT, '', null, false), // 客户uid
			array('lastts', self::VAR_INT, '', null, false), // 最后发送聊天信息时间
			array('message', null, '', null, false), // 默认消息
		);
		// 提取数据
		$data = array();
		$serv_lastview = &service::factory('voa_s_oa_talk_lastview');
		if (!$this->extract_field($data, $fields)) {
			return false;
		}

		$serv_lastview->insert($data);

		return true;
	}

}
