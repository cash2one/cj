<?php
/**
 * 附件读取
 * $Author$
 * $Id$
 */
class voa_c_frontend_attachment_read extends voa_c_frontend_attachment_base {

	/**
	 * _before_action
	 *
	 * @param mixed $action        	
	 * @access protected
	 * @return void
	 */
	protected function _before_action($action) {
		$this->_require_login = false;
		return parent::_before_action($action);
	}

	public function execute() {
		$at_id = (int) $this->request->get('at_id');

		$download = (int) $this->request->get('download');

		if ($at_id < 1) {
			return true;
		}
		
		/**
		 * 验证 sig
		 */
		$ts = (int) $this->request->get('ts');
		$sig = (string) $this->request->get('sig');
		if (!voa_h_attach::attach_sig_check($at_id, $ts, $sig)) {
			// return true;
		}
		
		$serv = &service::factory('voa_s_oa_common_attachment', array(
			'pluginid' => 0 
		));
		$attach = $serv->fetch_by_id($at_id);
		
		/**
		 * 判断附件是否存在
		 */
		if (empty($attach)) {
			$this->_error_message('attachment_is_not_exists');
			return true;
		}
		
		/**
		 * 附件获取
		 */
		$uda_att = uda::factory('voa_uda_frontend_attachment_get');
		$options = array();
		$options['download'] = $download;

		if (!$uda_att->read($attach, $options)) {
			$this->_error_message('attachment_is_not_exists');
			return false;
		}
		return $this->response->stop();
	}
}
