<?php
/**
 * voa_c_wxwall_frontend_base
 * 微信墙前端/展示:基本控制器
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_wxwall_frontend_base extends voa_c_wxwall_base {
	protected $_current_wxwall = array();
	protected $_current_ww_id = 0;

	protected function _before_action($action) {

		if (!parent::_before_action($action)) {
			return false;
		}

		$ww_id = $this->request->get('ww_id');
		$ww_id = rintval($ww_id, false);
		if (!$ww_id || !($wxwall = voa_h_wxwall::get_wxwall($ww_id)) || !is_array($wxwall) || empty($wxwall['ww_id'])) {
			$this->_message('error', '对不起，您要访问的微信墙不存在，请确认地址正确。');
		}

		/** 检查微信墙状态 */
		if (($status = voa_h_wxwall::check_status($wxwall)) !== true) {
			$this->_message('error', $status);
		}

		$this->_current_ww_id = $ww_id;
		$this->_current_wxwall = $wxwall;

		$this->view->set('navTitle', $this->_current_wxwall['ww_subject']);
		$this->view->set('wxwallUrl', voa_h_wxwall::wxwall_url($ww_id));
		$this->view->set('wxwallPostCode', voa_h_wxwall::wxwall_post_message_code($ww_id));
		$this->view->set('ww_id', $this->_current_ww_id);
		$this->view->set('wxwall', $this->_current_wxwall);
		return true;
	}

	protected function _after_action($action) {

		parent::_after_action($action);
		return true;
	}

	/**
	 * 消息提醒
	 * @param string $type
	 * @param string $message
	 * @param string $url
	 * @param boolean $redirect
	 */
	protected function _message($type, $message, $url = '', $redirect = false) {
		return parent::_message($type, $message, $url, $redirect, 'wxwall/frontend/message');
	}

	/**
	 * 返回 Ajax 调用结果
	 * @param * $data 返回数据
	 * @param int $errno 错误号, 正确时为 0
	 * @param string $errmsg 错误信息
	 */
	protected function _ajax_return($data, $errno = 0, $errmsg = '') {
		$output = array(
			'data' => $data,
			'errno' => $errno
		);
		/** 如果错误信息不为空, 则 */
		if (!empty($errmsg)) {
			$output['errmsg'] = $errmsg;
		}
		@header('Content-type: text/json');
		echo rjson_encode($output);
		exit;
	}




}
