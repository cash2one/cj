<?php
/**
 * 名片群组操作
 * $Author$
 * $Id$
 */

class voa_c_frontend_namecard_folder extends voa_c_frontend_namecard_base {

	/** 获取指定群组信息 */
	protected function _get_mine($ncf_id) {
		/** 读取群组并判断权限 */
		$serv = &service::factory('voa_s_oa_namecard_folder', array('pluginid' => startup_env::get('pluginid')));
		$folder = $serv->fetch_by_id($ncf_id);
		if (empty($folder) || $folder['m_uid'] != startup_env::get('wbs_uid')) {
			return array();
		}

		return $folder;
	}
}
