<?php
/**
 * 查看名片信息
 * $Author$
 * $Id$
 */

class voa_c_frontend_namecard_view extends voa_c_frontend_namecard_base {

	public function execute() {
		$uda = uda::factory('voa_uda_frontend_namecard_format');
		/** 获取名片信息 */
		$nc_id = intval($this->request->get('nc_id'));
		$serv_n = &service::factory('voa_s_oa_namecard', array('pluginid' => startup_env::get('pluginid')));
		$namecard = $serv_n->fetch_by_id($nc_id);
		/**时间**/
		$namecard['nc_created'] = rgmdate($namecard['nc_created'], 'Y-m-d H:i:s');

		/** 判断权限 */
		if (empty($nc_id) || startup_env::get('wbs_uid') != $namecard['m_uid']) {
			$this->_error_message('该名片不存在或已被删除');
		}

		/** 过滤 */
		if (!$uda->namecard($namecard)) {
			$this->_error_message($uda->error);
			return false;
		}

		/** 读取附件(名片图片) */
		$attach = array();
		if (0 < $namecard['at_id']) {
			$serv_at = &service::factory('voa_s_oa_common_attachment');
			$attach = $serv_at->fetch_by_id($namecard['at_id']);
		}

		/** 读取群组 */
		//$serv_f = &service::factory('voa_s_oa_namecard_folder', array('pluginid' => startup_env::get('pluginid')));
		//$folders = $serv_f->fetch_by_uid(startup_env::get('wbs_uid'));

		/** 读取群组/公司/职位 */
		$serv_c = &service::factory('voa_s_oa_namecard_company', array('pluginid' => startup_env::get('pluginid')));
		$company = $serv_c->fetch_by_id($namecard['ncc_id']);
		$company && $uda->company($company);

		$serv_j = &service::factory('voa_s_oa_namecard_job', array('pluginid' => startup_env::get('pluginid')));
		$job = $serv_j->fetch_by_id($namecard['ncj_id']);
		$job && $uda->job($job);

		$this->view->set('namecard', $namecard);
		$this->view->set('attach', $attach);
		//$this->view->set('folders', rhtmlspecialchars($folders));
		$this->view->set('company', $company);
		$this->view->set('job', $job);
		$this->view->set('nc_id', $nc_id);

		$this->_output('namecard/view');
	}
}

