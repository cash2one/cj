<?php
/**
 * voa_c_wxwall_frontend_homepage
 * 微信墙前端/展示
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_wxwall_frontend_homepage extends voa_c_wxwall_frontend_base {

	public function execute() {

		$this->view->set('qrcodeupdateUrlBase', $this->wxwall_admincp_url('updateqrcode', '', array('ww_id' => $this->_current_ww_id, 'hash' => ''), false));
		$this->view->set('qrcodeUrlBase', $this->wxwall_admincp_url('qrcode', '', array('ww_id' => $this->_current_ww_id, 'v' => ''), false));
		$this->view->set('getNewListUrlBase', $this->wxwall_admincp_url('getnewlist', '', array('ww_id' => $this->_current_ww_id, 'updated' => ''), false));

		$this->output('wxwall/frontend/homepage');

	}

}
