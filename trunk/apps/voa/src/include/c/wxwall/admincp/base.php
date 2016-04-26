<?php
/**
 * base.php
 * 微信墙前端/后台:基本控制器
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_wxwall_admincp_base extends voa_c_wxwall_base {

	/**
	 * 后台菜单
	 * @var unknown
	 */
	protected $_admincp_actions = array(
			'setting' => array('action' => 'form', 'name' => '微信墙设置'),
			'verify' => array('action' => 'list', 'name' => '内容审核')
	);

	protected $_cookiename_ww_id = 'ww_id';
	protected $_cookiename_key = 'wxwall_key';
	protected $_current_wxwall = array();
	protected $_current_ww_id = 0;

	protected function _before_action($action) {

		if (!parent::_before_action($action)) {
			return false;
		}

		if (!in_array($this->_module, array('homepage', 'login', 'logout'))) {
			if (!$this->_is_login()) {
				$this->_message('error', '登录过期，请重新登录', $this->wxwall_admincp_url(''), true);
			}
		}

		/** 初始化当前管理的墙id */
		$this->view->set('ww_id', $this->_current_ww_id);
		$this->view->set('wxwall', $this->_current_wxwall);

		$navLinks = array();
		foreach ($this->_admincp_actions AS $_module => $_act) {
			$navLinks[] = array(
					'module' => $_module,
					'action' => $_act['action'],
					'name' => $_act['name'],
					'url' => $this->wxwall_admincp_url($_module)
			);
		}
		$this->view->set('navLinks', $navLinks);
		$this->view->set('logoutLink', $this->wxwall_admincp_url('logout'));
		$this->view->set('wxwallUrl', voa_h_wxwall::wxwall_url($this->_current_ww_id));
		if (!empty($this->_current_wxwall['ww_subject'])) {
			$this->view->set('navTitle', $this->_current_wxwall['ww_subject']);
		}
		return true;

	}

	protected function _after_action($action) {

		parent::_after_action($action);
		return true;

	}

	/**
	 * 判断POST提交
	 * @return boolean
	 */
	protected function _is_post() {
		if (!$this->request->is_post()) {
			return false;
		}
		if ($this->_generate_form_hash() != $this->request->post('formhash')) {
			return false;
		}
		return true;
	}

	/**
	 * 判断是否登录
	 * @return boolean
	 */
	protected function _is_login() {
		$key = $this->session->getx($this->_cookiename_key);
		$ww_id = $this->session->getx($this->_cookiename_ww_id);
		if (!$key || !$ww_id || !($wxwall = voa_h_wxwall::get_wxwall($ww_id)) || $key != $this->_wxwall_key($wxwall['ww_admin'], $wxwall['ww_passwd'])) {
			$this->session->setx($this->_cookiename_ww_id, 0);
			$this->session->setx($this->_cookiename_key, '');
			return false;
		}
		$this->_current_wxwall = $wxwall;
		$this->_current_ww_id = $ww_id;
		return true;
	}

	/**
	 * 生成验证字符串
	 * @param string $ww_admin
	 * @param string $ww_passwd
	 * @return string
	 */
	protected function _wxwall_key($ww_admin, $ww_passwd) {
		return md5($ww_admin.$ww_passwd);
	}

	/**
	 * 生成数据表中的用户密码,
	 * @param string $passwd 用户提交的密码
	 * @param string $salt 干扰字串
	 */
	protected function _generate_passwd($passwd, $salt, $pwd_is_md5 = false) {
		return voa_h_wxwall::generate_passwd($passwd, $salt, $pwd_is_md5);
	}

	/**
	 * 消息提醒
	 * @param string $type
	 * @param string $message
	 * @param string $url
	 * @param boolean $redirect
	 */
	protected function _message($type, $message, $url = '', $redirect = false) {
		return parent::_message($type, $message, $url, $redirect, 'wxwall/admincp/message');
	}

}
