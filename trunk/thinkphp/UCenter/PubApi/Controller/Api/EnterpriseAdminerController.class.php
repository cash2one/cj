<?php
/**
 * EnterpriseAdminerController.class.php
 * $author$
 */

namespace PubApi\Controller\Api;

class EnterpriseAdminerController extends AbstractController {

	// List_get
	public function List_get() {

		$params = I('get.');
		$serv_ep = D('PubApi/EnterpriseAdminer', 'Service');
		$list = array();
		if (!$serv_ep->list_adminer($list, $params)) {
			E('_ERR_DEFAULT');
			return false;
		}

		$this->_result = array('list' => $list);
		return true;
	}


	// Fetch_get
	public function Fetch_get() {

		$params = I('get.');
		$serv_ep = D('PubApi/EnterpriseAdminer', 'Service');
		$adminer = array();
		if (!$serv_ep->get_adminer($adminer, $params)) {
			E('_ERR_DEFAULT');
			return false;
		}

		$this->_result = array('adminer' => $adminer);
		return true;
	}


	// Update_get
	public function Update_post() {

		$params = I('post.');
		$serv_ep = D('PubApi/EnterpriseAdminer', 'Service');
		if (!$serv_ep->update_adminer($params)) {
			E('_ERR_DEFAULT');
			return false;
		}

		return true;
	}

	// 新增管理员
	public function Add_post() {

		$params = I('post.');
		$serv_ep = D('PubApi/EnterpriseAdminer', 'Service');
		$adminer = array();
		if (!$serv_ep->add_adminer($adminer, $params)) {
			E('_ERR_DEFAULT');
			return false;
		}

		return true;
	}

	// 删除管理员
	public function Del_post() {

		$params = I('post.');
		$serv_ep = D('PubApi/EnterpriseAdminer', 'Service');
		if (!$serv_ep->del_adminer($params)) {
			E('_ERR_DEFAULT');
			return false;
		}

		return true;
	}

	// Login_post
	public function Login_post() {

		$params = I('post.', '', 'trim');
		$serv_log = D('PubApi/EnterpriseAdminer', 'Service');
		if (!$serv_log->login($this->_result, $params)) {
			E('_ERR_LOGIN_ERROR');
			return false;
		}

		return true;
	}

	// Code_get
	public function Code_get() {

		$params = I('get.', '', 'trim');
		$serv_log = D('PubApi/EnterpriseAdminer', 'Service');
		if (!$serv_log->get_code($this->_result, $params)) {
			E('_ERR_GET_CODE_ERROR');
			return false;
		}

		return true;
	}

	// GetUser_get
	public function GetUser_get() {

		$params = I('get.', '', 'trim');
		$serv_mem = D('PubApi/EnterpriseAdminer', 'Service');
		if (!$serv_mem->get_user($this->_result, $params)) {
			E('_ERR_PARAMS_ERROR');
			return false;
		}

		return true;
	}

	// 检查参数
	protected function _check_signature($action = '') {

		if (in_array($action, array('Login'))) {
			return true;
		}

		return parent::_check_signature($action);
	}

}
