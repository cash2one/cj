<?php
/**
 * voa_c_wxwall_admincp_setting_form
 * 微信墙前端/管理/微信墙设置
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_wxwall_admincp_setting_form extends voa_c_wxwall_admincp_setting_base {

	public function execute() {

		if ($this->_is_post()) {

			$fields = array('ww_subject', 'ww_isopen', 'ww_postverify', 'ww_maxpost', 'ww_message');
			$param = array();
			foreach ($fields AS $_key) {
				$param[$_key] = $this->request->post($_key);
			}

			$result = voa_h_wxwall::wxwall_field_check($param, $this->_current_wxwall);
			if (!is_array($result)) {
				$this->_message('error', $result);
			}

			$serv_wxwall = &service::factory('voa_s_oa_wxwall', array('pluginid' => startup_env::get('pluginid')));
			$serv_wxwall->update($result, array('ww_id' => $this->_current_ww_id));

			$this->_message('success', '更新微信墙设置操作完毕', $this->wxwall_admincp_url($this->_module, $this->_action), false);

		}

		$title = $this->_admincp_actions[$this->_module]['name'];

		$this->view->set('navTitle', $title.' - '.$this->_current_wxwall['ww_subject']);
		$this->view->set('currentName', $title);
		$this->view->set('currentLink', $this->wxwall_admincp_url($this->_module));
		$this->view->set('formActionUrl', $this->wxwall_admincp_url($this->_module));
		$this->view->set('post_message_code', voa_h_wxwall::wxwall_post_message_code($this->_current_ww_id));
		$this->view->set('wxwall', $this->_current_wxwall);
		$this->view->set('isopen', voa_h_wxwall::$isopen);
		$this->view->set('postverify', voa_h_wxwall::$postverify);

		$this->output('wxwall/admincp/setting/form');

	}

}
