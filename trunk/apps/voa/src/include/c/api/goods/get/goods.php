<?php
/**
 * voa_c_api_goods_get_goods
 * 获取商品列表信息
 * $Author$
 * $Id$
 */

class voa_c_api_goods_get_goods extends voa_c_api_goods_abstract {

	public function execute() {

		// 获取分页参数
		$page = (int)$this->_get('page');
		list($start, $perpage, $page) = voa_h_func::get_limit($page, $this->_p_sets['perpage']);

		// 读取数据
		$total = 0;
		$list = array();
		$uda = &uda::factory('voa_uda_frontend_goods_data', $this->_ptname);
		if (!$uda->list_all($this->_params, array($start, $perpage), $list, $total)) {
			$this->_errcode = $uda->errno;
			$this->_errmsg = $uda->error;
			return true;
		}

		$this->_result = array(
			'total' => $total,
			'data' => empty($list) ? array() : array_values($list)
		);

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
