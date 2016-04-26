<?php
/**
 * base.php
 *
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_adminer_base extends voa_c_admincp_base {

	protected function _before_action($action) {

		return parent::_before_action($action);
	}

	protected function _after_action($action) {
		parent::_after_action($action);
		return true;
	}

	/**
	 * 输出页面消息
	 * @param number $errcode
	 * @param string $errmsg
	 * @param array $result
	 * @param boolean $output 是否直接输出json，true=是（默认），false=返回结果数据
	 */
	protected function _output($errcode = 0, $errmsg = '', $result = array(), $output = true) {
		$data = array(
			'errcode' => (int)$errcode,
			'errmsg' => (string)$errmsg,
			'result' => $result
		);
		if ($output) {
			echo rjson_encode($data);
			exit;
		} else {
			return $data;
		}
	}

	/**
	 * 呼叫oa uc api接口
	 * @param string $api_name api接口的名
	 * @param array $params
	 * @param boolean $output 是否输出结果json，true=是（默认），false=直接返回结果数组
	 * @return array
	 */
	protected function _call_oauc_api($api_name, $params = array(), $output = true) {

		// oa uc根目录
		$uc_url = config::get('voa.uc_url');

		// 接口名称与url映射
		$uc_api_url_list = array(
 			'smscode' => 'uc/api/post/mobileverify/',
			'smscodeverify' => 'uc/api/post/smscodeverify/'
		);

		if (!isset($uc_api_url_list[$api_name])) {
			$this->_output(1001, '未定义的API');
			return false;
		}

		// api接口url
		$api_url = $uc_url.$uc_api_url_list[$api_name];

		// api 要求的请求方式
		$http_method = 'GET';
		if (preg_match('/\/api\/(get|post|put|delete)\//', $api_url, $match)) {
			$http_method = rstrtoupper($match[1]);
		}

		// 额外的http header请求信息
		$http_headers = array();

		// 请求api的响应结果
		$result = array();
		// 请求api的snoopy调试报告
		$reporting = array();
		if (!voa_h_func::get_json_by_post_and_header($result, $api_url, $params, $http_headers, $http_method, $reporting)) {
			$this->_output(1002, '请求API接口发生错误');
			return false;
		}

		return $this->_output($result['errcode'], $result['errmsg'], $result['result'], $output);
	}

}
