<?php
/**
 * 新增名片
 * $Author$
 * $Id$
 */

class voa_c_frontend_namecard_new extends voa_c_frontend_namecard_base {

	public function execute() {
		$fmt = &uda::factory('voa_uda_frontend_namecard_format');
		/** 读取群组 */
		$serv_f = &service::factory('voa_s_oa_namecard_folder', array('pluginid' => startup_env::get('pluginid')));
		$folders = $serv_f->fetch_by_uid(startup_env::get('wbs_uid'));
		if (!$fmt->folder_list($folders)) {
			$this->_error_message($fmt->error);
			return false;
		}

		/** 处理提交 */
		if ($this->_is_post()) {
			$namecard = array();
			$uda_up = &uda::factory('voa_uda_frontend_namecard_insert');
			if (!$uda_up->namecard_new($namecard, $folders)) {
				$this->_error_message($uda_up->error);
				return false;
			}

			$this->_success_message('名片新增成功', '/namecard/view/'.$namecard['nc_id']);
		}

		$this->view->set('form_action', "/namecard/new?handlekey=post");
		$this->view->set('ac', $this->action_name);
		$this->view->set('folders', $folders);
		$this->view->set('namecard', array());

		$this->_output('namecard/post');
	}
}

