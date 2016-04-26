<?php
/**
 * voa_uda_frontend_talk_getviewer
 * 获取咨询用户
 *
 * $Author$
 * $Id$
 */

class voa_uda_frontend_talk_getviewer extends voa_uda_frontend_talk_abstract {

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
		$tv_uid = $this->get('tv_uid');
		if (!is_array($tv_uid)) {
			$tv_uid = explode(',', $tv_uid);
		}

		// 访客信息入库
		$out = $serv_viewer->list_by_pks($tv_uid);

		return true;
	}

	/**
	 * 返回新访客数
	 * @return int
	 */
	public function newguest($uid) {

		$lastview = &service::factory('voa_s_oa_talk_lastview');
		$count = $lastview->newguest($uid);

		return $count;
	}
}
