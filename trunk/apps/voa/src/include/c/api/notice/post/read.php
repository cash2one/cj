<?php
/**
 * 读取公告标识
 * $Author$
 * $Id$
 */

class voa_c_api_notice_post_read extends voa_c_api_sign_base {

	public function execute() {

		$nt_id = (int)$this->_get('nt_id', 0);
		if (0 >= $nt_id) {
			return true;
		}

		/** 读取公告信息 */
		$serv_nt = &service::factory('voa_s_oa_notice', array('pluginid' => startup_env::get('pluginid')));
		$notice = $serv_nt->fetch_by_id($nt_id);

		if (empty($notice)) {
			return true;
		}

		/** 公告已读信息入库 */
		$serv_readed = &service::factory('voa_s_oa_notice_read', array('pluginid' => startup_env::get('pluginid')));
		$readed = $serv_readed->fetch_all_by_nt_id_m_uid($nt_id, $this->_member['m_uid']);
		if (!empty($readed)) {
			return true;
		}

		$serv_readed->insert(array(
			'nt_id' => $nt_id,
			'm_uid' => $this->_member['m_uid']
		));

		return true;
	}
}
