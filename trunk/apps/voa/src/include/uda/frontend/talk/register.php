<?php
/**
 * voa_uda_frontend_talk_register
 * 注册用户
 *
 * $Author$
 * $Id$
 */

class voa_uda_frontend_talk_register extends voa_uda_frontend_talk_abstract {

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
		// 访客服务类
		$serv_viewer = &service::factory('voa_s_oa_talk_viewer');
		// 取访客信息
		$viewer = array(
			'username' => random(8),
			'ip' => controller_request::get_instance()->get_client_ip()
		);

		// 访客信息入库
		$out = $serv_viewer->insert($viewer);

		return true;
	}

}
