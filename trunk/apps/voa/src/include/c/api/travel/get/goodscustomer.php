<?php
/**
 * voa_c_api_travel_get_goodscustomer
 * 获取已关注产品的客户列表
 * $Author$
 * $Id$
 */

class voa_c_api_travel_get_goodscustomer extends voa_c_api_travel_customerabstract {

	public function execute() {

		// 获取产品id
		$goods_id = (int)$this->_get('goods_id');

		// 获取意向产品信息
		$list = array();
		$uda = &uda::factory('voa_uda_frontend_travel_customer2goods', $this->_ptname);
		if (!$uda->list_customer_by_goods_id($goods_id, null, $list)) {
			$this->_errcode = $uda->errno;
			$this->_errmsg = $uda->error;
			return true;
		}

		// 获取对应关系中的 customer_id
		$ids = array();
		if (!empty($list)) {
			foreach ($list as $_v) {
				$ids[] = $_v['customer_id'];
			}
		}

		$this->_result = $ids;

		return true;
	}

	/**
	 * 设置插件/表格名称
	 * @return boolean
	 */
	protected function _init_ptname() {

		parent::_init_ptname();
		$this->_ptname['classes'] = voa_h_cache::get_instance()->get('plugin.travel.customerclass', 'oa');
		$this->_ptname['columns'] = voa_h_cache::get_instance()->get('plugin.travel.customertablecol', 'oa');
		$this->_ptname['options'] = voa_h_cache::get_instance()->get('plugin.travel.customertablecolopt', 'oa');

		// 只取自己的
		$this->_ptname['conds'] = array(
			'uid' => $this->_member['m_uid']
		);
	}

}

