<?php
/**
 * redirect.php
 * 重定向脚本
 * 本页面目前（2015-03-20）暂时只针对后台视频显示的URL跳板使用
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_frontend_index_redirect extends voa_c_frontend_base {

	public function _before_action($action) {
		$this->_require_login = false;

		return parent::_before_action($action);
	}

	public function execute() {

		$url = (string)$this->request->get('url');
		$url = rhtmlspecialchars($url);

		//logger::error(print_r($GLOBALS, true));

		// 如果不能判断则可以使用来路“video.html”来区分

		$this->response->set_redirect($url);

		return true;
	}

}
