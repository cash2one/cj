<?php
/**
 * Created by PhpStorm.
 * User: kk
 * Date: 2015/5/25
 * Time: 9:45
 */
class voa_c_api_news_get_templatelist extends voa_c_api_news_abstract
{

	public function execute()
	{

		// 请求的参数
		$fields = array(
			// 分类ID
			'limit' => array('type' => 'int', 'required' => false),
			'page' => array('type' => 'int', 'required' => false),
		);
		if (!$this->_check_params($fields)) {
			return false;
		}
		if (!$this->_params['page']) {
			$this->_params['page'] = 1;
		}
		if (!$this->_params['limit']) {
			$this->_params['limit'] = 10;
		}

		//获取所有用户信息
		$servm = &service::factory('voa_server_cyadmin_news', array('pluginid' => 0));
		$result = $servm->template_list($this->_params);
		if (!empty($result)) {
			$result = array_values($result);
			foreach ($result as $_key => &$_val) {
				$_val['key'] = ($this->_params['page'] - 1) * 10 + $_key + 1;
			}
		}

		// 输出结果
		$this->_result = array(
			'page' => $this->_params['page'],
			'limit' => $this->_params['limit'],
			'list' => empty($result) ? array() : array_values($result)
		);

		return true;
	}
}
