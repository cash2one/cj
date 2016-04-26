<?php
/**
 * 微信墙回复信息
 * $Author$
 * $Id$
 */

class voa_c_frontend_wxwall_reply extends voa_c_frontend_wxwall_base {

	public function execute() {
		/** 获取微信墙信息 */
		$ww_id = intval($this->request->get('ww_id'));
		$serv_w = &service::factory('voa_s_oa_wxwall', array('pluginid' => startup_env::get('pluginid')));
		$wall = $serv_w->fetch_by_id($ww_id);
		if (empty($wall)) {
			$this->_error_message('当前微信墙信息不存在');
		}

		/** 如果已经结束或关闭 */
		if ($wall['endtime'] > startup_env::get('timestamp') || voa_d_oa_wxwall::IS_CLOSE == $wall['ww_isopen']) {
			$this->_error_message('当前微信墙已结束或已关闭');
		}

		/** 检查最大回复数 */
		$this->_chk_reply_maxpost($wall);

		/** 回复信息 */
		$message = trim($this->request->get('message'));

		/** 入库 */
		$serv_p = &service::factory('voa_s_oa_wxwall_post', array('pluginid' => startup_env::get('pluginid')));
		$serv_p->insert(array(
			'm_uid' => startup_env::get('wbs_uid'),
			'm_username' => startup_env::get('wbs_username'),
			'ww_id' => $ww_id,
			'wwp_message' => $message,
			'wwp_status' => 0 == $wall['ww_postverify'] ? voa_d_oa_wxwall_post::STATUS_APPROVE : voa_d_oa_wxwall_post::STATUS_NORMAL
		));

		$this->_success_message('信息发布成功');
	}

	/** 检查最大回复数 */
	function _chk_reply_maxpost($wall) {
		if (0 == $wall['ww_maxpost']) {
			return true;
		}

		$serv = &service::factory('voa_s_oa_wxwall_post', array('pluginid' => startup_env::get('pluginid')));
		$count = $serv->count_by_ww_id_uid($wall['ww_id'], startup_env::get('wbs_uid'));
		if ($count >= $wall['ww_maxpost']) {
			$this->_error_message('已经达到回复的最大值');
		}

		return true;
	}
}

