<?php
/**
 * get.php
 * testing 举例
 * /api/testing/get/list/?aaa=bbb&ccc=dddd0l&_api_force=0
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_api_testing_get_list extends voa_c_api_testing_base {

	public function execute() {

		// 待返回的数据
		$data = array();

		// 定义有效的请求参数数组
		$param_allow = array('var1', 'var2', 'var3', 'var4');

		// 载入缓存获取是否更新
		if (isset($this->_params['_api_unique']) && $this->_api_common_cache($data, $this->_params['_api_unique'], $param_allow)) {
			// 存在缓存，则直接返回结果
			$this->_result = $data;
			return null;
		}

		// 实际查询操作，获取最新的数据，支持各种数据类型
		$data = array('a','b','c','d');

		// 当前数据的唯一标识符
		$unique = '';

		// 写入缓存
		$this->_api_common_cache($data);

		/** zhuxun begin, 通过 PDO 方式读取数据, 剔除 service 层 */
		$mem = new voa_d_testing_member();
		try {
			$mem->begin();
			$list = $mem->list_all();
			$mem->commit();
		} catch (Exception $e) {
			Logger::error($e);
			$mem->rollback();
		}

		/** zhuxun end. */

		// 输出结果
		$this->_result = $data;
		return;
	}

}
