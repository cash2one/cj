<?php

class voa_uda_frontend_setting_wxcorp extends voa_uda_frontend_base {

	/** 待操作的插件信息 */
	protected $_plugin = array();
	protected $_serv_setting = null;

	public function __construct() {
		parent::__construct();
		$this->_serv_setting = &service::factory('voa_s_oa_common_setting', array('pluginid' => 0));
		$this->_serv_wx_setting = &service::factory('voa_s_oa_weixin_setting', array('pluginid' => 0));

	}

	public function update($param) {
		if (!empty($param['ep_wxcorpid'])) {
			if ($this->_serv_setting->fetch('corp_id')) {
				$this->_serv_setting->update(array('cs_value'=>$param['ep_wxcorpid']), array('cs_key'=>'corp_id'));
			} else {
				$this->_serv_setting->insert(array('cs_value'=>$param['ep_wxcorpid'], 'cs_key'=>'corp_id'));
			}
			if (!empty($param['ep_wxcorpsecret'])) {
				if ($this->_serv_setting->fetch('corp_id')) {
					$this->_serv_setting->update(array('cs_value'=>$param['ep_wxcorpsecret']), array('cs_key'=>'corp_secret'));
				} else {
					$this->_serv_setting->insert(array('cs_value'=>$param['ep_wxcorpsecret'], 'cs_key'=>'corp_secret'));
				}
				if ($this->_serv_setting->fetch('ep_wxqy')) {
					$this->_serv_setting->update(array('cs_value'=>voa_d_oa_common_setting::WXQY_AUTH), array('cs_key'=>'ep_wxqy'));
				} else {
					$this->_serv_setting->insert(array('cs_value'=>voa_d_oa_common_setting::WXQY_CLOSE, 'cs_key'=>'ep_wxqy'));
				}
				$this->_serv_wx_setting->update(array('ws_value'=>''), array('ws_key'=>'access_token'));
				$this->_serv_wx_setting->update(array('ws_value'=>0), array('ws_key'=>'access_expires'));
				voa_h_cache::get_instance()->get('weixin', 'oa', true);
			}


		}
		if (isset($param['ep_locked'])) {
			if ($this->_serv_setting->fetch('locked')) {
				$this->_serv_setting->update(array('cs_value'=>$param['ep_locked']), array('cs_key'=>'locked'));
			} else {
				$this->_serv_setting->insert(array('cs_value'=>$param['ep_locked'], 'cs_key'=>'locked'));
			}
		}
		if (!empty($param['ep_wxtoken'])) {
			if ($this->_serv_setting->fetch('token')) {
				$this->_serv_setting->update(array('cs_value'=>$param['ep_wxtoken']), array('cs_key'=>'token'));
			} else {
				$this->_serv_setting->insert(array('cs_value'=>$param['ep_wxtoken'], 'cs_key'=>'token'));
			}
		}
		if (!empty($param['ep_wxname'])) {
			if ($this->_serv_setting->fetch('sitename')) {
				$this->_serv_setting->update(array('cs_value'=>$param['ep_wxname']), array('cs_key'=>'sitename'));
			} else {
				$this->_serv_setting->insert(array('cs_value'=>$param['ep_wxname'], 'cs_key'=>'sitename'));
			}
		}
		if (!empty($param['ep_xgaccessid'])) {
			if ($this->_serv_setting->fetch('xg_access_id')) {
				$this->_serv_setting->update(array('cs_value'=>$param['ep_xgaccessid']), array('cs_key'=>'xg_access_id'));
			} else {
				$this->_serv_setting->insert(array('cs_value'=>$param['ep_xgaccessid'], 'cs_key'=>'xg_access_id'));
			}
		}
		if (!empty($param['ep_xgaccesskey'])) {
			if ($this->_serv_setting->fetch('xg_access_key')) {
				$this->_serv_setting->update(array('cs_value'=>$param['ep_xgaccesskey']), array('cs_key'=>'xg_access_key'));
			} else {
				$this->_serv_setting->insert(array('cs_value'=>$param['ep_xgaccesskey'], 'cs_key'=>'xg_access_key'));
			}
		}
		if (!empty($param['ep_xgsecretkey'])) {
			if ($this->_serv_setting->fetch('xg_secret_key')) {
				$this->_serv_setting->update(array('cs_value'=>$param['ep_xgsecretkey']), array('cs_key'=>'xg_secret_key'));
			} else {
				$this->_serv_setting->insert(array('cs_value'=>$param['ep_xgsecretkey'], 'cs_key'=>'xg_secret_key'));
			}
		}
		if (!empty($param['ep_qrcode'])) {
			if ($this->_serv_setting->fetch('qrcode')) {
				$this->_serv_setting->update(array('cs_value'=>$param['ep_qrcode']), array('cs_key'=>'qrcode'));
			} else {
				$this->_serv_setting->insert(array('cs_value'=>$param['ep_qrcode'], 'cs_key'=>'qrcode'));
			}
		}
		voa_h_cache::get_instance()->get('setting', 'oa', true);

		return true;
	}
}
