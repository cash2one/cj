<?php
/**
 * 编辑名片信息
 * $Author$
 * $Id$
 */

class voa_c_frontend_namecard_edit extends voa_c_frontend_namecard_base {

	public function execute() {
		$uda_fmt = &uda::factory('voa_uda_frontend_namecard_format');
		/** 获取名片信息 */
		$nc_id = intval($this->request->get('nc_id'));
		$serv_n = &service::factory('voa_s_oa_namecard', array('pluginid' => startup_env::get('pluginid')));
		$namecard = $serv_n->fetch_by_id($nc_id);
		if (empty($namecard)) {
			$this->_error_message('当前名片记录不存在'.$nc_id);
			return false;
		}

		/** 判断权限 */
		if (startup_env::get('wbs_uid') != $namecard['m_uid']) {
			$this->_error_message('该名片不存在或已被删除');
			return false;
		}

		/** 名片数据格式化 */
		if (!$uda_fmt->namecard($namecard)) {
			$this->_error_message($uda_fmt->error);
			return false;
		}

		/** 读取群组 */
		$serv_f = &service::factory('voa_s_oa_namecard_folder', array('pluginid' => startup_env::get('pluginid')));
		$folders = $serv_f->fetch_by_uid(startup_env::get('wbs_uid'));
		if (!$uda_fmt->folder_list($folders)) {
			$this->_error_message($uda_fmt->error);
			return false;
		}

		/** 读取公司/职位 */
		$serv_c = &service::factory('voa_s_oa_namecard_company', array('pluginid' => startup_env::get('pluginid')));
		$company = $serv_c->fetch_by_id($namecard['ncc_id']);
		if (!$uda_fmt->company($company)) {
			$this->_error_message($uda_fmt->error);
			return false;
		}

		$serv_j = &service::factory('voa_s_oa_namecard_job', array('pluginid' => startup_env::get('pluginid')));
		$job = $serv_j->fetch_by_id($namecard['ncj_id']);
		if (!$uda_fmt->job($job)) {
			$this->_error_message($uda_fmt->error);
			return false;
		}

		/** 处理编辑 */
		if ($this->_is_post()) {
			$uda_up = &uda::factory('voa_uda_frontend_namecard_update');
			if (!$uda_up->namecard_update($namecard, $folders)) {
				$this->_error_message($uda_up);
				return false;
			}

			$this->_success_message('名片修改成功', '/namecard/view/'.$nc_id);
		}

		$this->view->set('namecard', $namecard);
		$this->view->set('company', $company);
		$this->view->set('job', $job);
		$this->view->set('ac', $this->action_name);
		$this->view->set('folders', $folders);
		$this->view->set('form_action', "/namecard/edit/{$nc_id}?handlekey=post");

		$this->_output('namecard/post');
	}
}
