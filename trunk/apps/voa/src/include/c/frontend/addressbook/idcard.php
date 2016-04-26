<?php
/**
 * Created by PhpStorm.
 * User: zhoutao
 * Date: 16/2/17
 * Time: 下午6:59
 */

class voa_c_frontend_addressbook_idcard extends voa_c_frontend_addressbook_base {

	public function execute() {

		$state_secret_key = $this->_setting['authkey'];

		// 加密人员ID
		$m_uid = authcode(startup_env::get('wbs_uid'), $state_secret_key, 'ENCODE');
		$m_uid = rbase64_encode($m_uid);

		$params = array(
			'pluginid' => $this->request->get('pluginid'),
			'm_uid' => $m_uid,
			'visit' => 'preview',
		);

		$url = '/h5/index.html#/app/page/contacts/contacts-businessCard?' . http_build_query($params);
		//$this->redirect($url);
		$this->view->set('redirect_url', $url);
		$this->_mobile_tpl = true;
		$this->_output('mobile/redirect');

		return true;
	}

}
