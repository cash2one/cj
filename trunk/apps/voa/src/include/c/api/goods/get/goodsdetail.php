<?php
/**
 * voa_c_api_goods_get_goodsdetail
 * 获取商品详情信息
 * $Author$
 * $Id$
 */

class voa_c_api_goods_get_goodsdetail extends voa_c_api_goods_abstract {

	public function execute() {

		// 获取分页参数
		$dataid = (int)$this->_get('dataid');

		// 读取数据
		$data = array();
		$uda = &uda::factory('voa_uda_frontend_goods_data', $this->_ptname);
		if (!$uda->get_one($dataid, $data)) {
			$this->_errcode = $uda->errno;
			$this->_errmsg = $uda->error;
			return true;
		}

		// 剔除主键键值
		if (isset($data['slide']) && !empty($data['slide'])) {
			$data['slide'] = array_values($data['slide']);
		}

		$this->_result = $data;

		return true;
	}

	/**
	 * 设置插件/表格名称
	 * @return boolean
	 */
	protected function _set_ptname() {

		parent::_set_ptname();
		$this->_ptname['classes'] = voa_h_cache::get_instance()->get('plugin.goods.class', 'oa');
		$this->_ptname['columns'] = voa_h_cache::get_instance()->get('plugin.goods.tablecol', 'oa');
		$this->_ptname['options'] = voa_h_cache::get_instance()->get('plugin.goods.tablecolopt', 'oa');
	}

}
