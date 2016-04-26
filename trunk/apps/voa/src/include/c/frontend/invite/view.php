<?php

/**
 * @Author: ppker
 * @Date:   2015-07-08 16:42:48
 * @Last Modified by:   ChangYi
 * @Last Modified time: 2015-07-09 18:54:30
 */
class voa_c_frontend_invite_view extends voa_c_frontend_invite_base {

	public function execute() {
		
		$per_id = rintval($this->request->get('per_id'));
		try {
			$uda = &uda::factory('voa_uda_frontend_invite_get');
			$view = array();
			$uda->get_view($per_id, $view);
		} catch (help_exception $h) {
			$this->_error_message($h->getMessage());
			return false;
		} catch (Exception $e) {
			logger::error($e);
			$this->_error_message($e->getMessage());
			return false;
		}
		
		$this->view->set('navtitle', '邀请详情');
		$this->view->set('view', $view);
		$this->_output('mobile/invite/view');
	}

}

