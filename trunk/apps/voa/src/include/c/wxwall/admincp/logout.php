<?php
/**
 * voa_c_wxwall_admincp_logout
 * 微信墙前端/管理/退出登录
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_wxwall_admincp_logout extends voa_c_wxwall_admincp_base {

	public function execute() {

		if ($this->request->get('formhash') == $this->_generate_form_hash()) {
			$this->session->setx($this->_cookiename_key, '');
			$this->session->setx($this->_cookiename_ww_id, '');
		}

		$this->_message('success', '您已安全退出微信墙管理登录', $this->wxwall_admincp_url(''));

	}

}
