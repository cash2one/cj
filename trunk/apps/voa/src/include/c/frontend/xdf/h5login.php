<?php
/**
 * Class voa_c_frontend_xdf_h5login
 * 接口/新东方/ H5登录接口
 * @create-time: 2015-06-17
 * @author: huanw
 * @email: wanghuan@vchangyi.com
 */

class voa_c_frontend_xdf_h5login extends voa_c_frontend_xdf_base {

	// refer
	protected $_refer = '';

	public function _before_action($action) {

		//获取来源地址
		$url_referer = $this->request->get('refer');
		if (empty($url_referer)) {
			$this->_error_message('非法操作, 请返回');
		}

		$this->_refer = urldecode($url_referer);
		if (!parent::_before_action($action)) {
			return false;
		}

		return true;
	}

	public function execute() {

		//生成code
		$scode = voa_h_login::code_create();

		//生成一条记录
		$ser_qrcode = &service::factory('voa_s_oa_common_signature');
		$m_uid = startup_env::get('wbs_uid');
		$result = $ser_qrcode->insert(array('sig_code' => $scode, 'sig_m_uid' => $m_uid, 'sig_login_status' => 1, 'sig_login_time' => startup_env::get('timestamp')));

		if (!$result) {
			return $this->_error_message("登录失败");
		}

		// 解析 URL
		$parsed_url = parse_url($this->_refer);
		// 解析参数
		parse_str($parsed_url['query'], $queries);
		// 剔除可能存在的 scode 参数
		unset($queries['scode']);
		// 增加 scode
		$queries['scode'] = $scode;
		// 拼凑待跳转的 URL
		$requesturl = $parsed_url['scheme'].'://'.$parsed_url['host'].$parsed_url['path'].'?'.http_build_query($queries);
		// 锚点
		if (!empty($parsed_url['fragment'])) {
			$requesturl .= '#'.$parsed_url['fragment'];
		}

		// 转向
		$this->redirect($requesturl);
	}
}
