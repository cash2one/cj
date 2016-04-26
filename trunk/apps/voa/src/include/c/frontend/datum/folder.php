<?php
/**
 * 资料文件夹操作
 * $Author$
 * $Id$
 */

class voa_c_frontend_datum_folder extends voa_c_frontend_datum_base {

	/** 获取指定文件夹信息 */
	protected function _get_mine($dtf_id) {
		/** 读取文件夹并判断权限 */
		$serv = &service::factory('voa_s_oa_datum_folder', array('pluginid' => startup_env::get('pluginid')));
		$folder = $serv->fetch_by_id($dtf_id);
		if (empty($folder) || $folder['m_uid'] != startup_env::get('wbs_uid')) {
			return array();
		}

		return $folder;
	}
}
