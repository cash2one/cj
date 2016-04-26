<?php
/**
 * 文件夹列表
 * $Author$
 * $Id$
 */

class voa_c_frontend_datum_folder_list extends voa_c_frontend_datum_folder {

	public function execute() {
		$uid = startup_env::get('wbs_uid');
		$serv_dt = &service::factory('voa_s_oa_datum', array('pluginid' => startup_env::get('pluginid')));
		$serv_f = &service::factory('voa_s_oa_datum_folder', array('pluginid' => startup_env::get('pluginid')));
		$list = $serv_f->fetch_by_uid($uid);

		$this->view->set('list', rhtmlspecialchars($list));
		$this->view->set('ct_folder', $serv_f->count_by_conditions(array('m_uid' => $uid)));
		$this->view->set('ct_datum', $serv_dt->count_by_conditions(array('m_uid' => $uid)));
		$this->view->set('navtitle', '文件夹列表');

		$this->_output('datum/folder/list');
	}
}
