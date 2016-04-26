<?php
/**
 * voa_uda_frontend_talk_say
 * 发送聊天信息
 *
 * $Author$
 * $Id$
 */

class voa_uda_frontend_talk_say extends voa_uda_frontend_talk_abstract {

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

		// 聊天记录服务类
		$serv_viewer = &service::factory('voa_s_oa_talk_wechat');
		$serv_viewer->set_params($in);

		// 入库
		if (!$serv_viewer->add($out)) {
			return false;
		}

		return true;
	}

}
