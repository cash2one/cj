<?php
/**
 * 查看公告
 * $Author$
 * $Id$
 */

class voa_c_frontend_notice_view extends voa_c_frontend_notice_base {

	public function execute() {
		/** 公告ID */
		$nt_id = rintval($this->request->get('nt_id'));

		/** 读取公告信息 */
		$serv = &service::factory('voa_s_oa_notice', array('pluginid' => startup_env::get('pluginid')));
		$notice = $serv->fetch_by_id($nt_id);
		if (empty($nt_id) || empty($notice)) {
			$this->_error_message('notice_is_not_exists');
		}

		/** 整理输出 */
		$fmt = &uda::factory('voa_uda_frontend_notice_format');
		$scheme = config::get('voa.oa_http_scheme');
		$fmt->format($notice, $scheme.$this->_setting['domain'].'/attachment/read/');

		$this->view->set('action', $this->action_name);
		$this->view->set('notice', $notice);

		$this->_output('notice/view');
	}

}
