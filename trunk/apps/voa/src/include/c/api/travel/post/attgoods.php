<?php
/**
 * voa_c_api_travel_post_attgoods
 * 新增客户关注的产品关联信息
 * $Author$
 * $Id$
 */

class voa_c_api_travel_post_attgoods extends voa_c_api_travel_goodsabstract {

	public function execute() {

		// 如果产品或客户id为空
		if (empty($this->_params['goods_id']) || empty($this->_params['customer_id'])) {
			$this->_set_errcode(voa_errcode_oa_travel::GOODS_OR_CUSTOMER_IS_EMPTY);
			return true;
		}

		// 新增关联信息
		$uda = &uda::factory('voa_uda_frontend_travel_customer2goods', $this->_ptname);
		$goods = array();
		if (!$uda->add($this->_member, $this->_params, $goods)) {
			$this->_errcode = $uda->errno;
			$this->_errmsg = $uda->error;
			return true;
		}

		return true;
	}

	/**
	 * 设置插件/表格名称
	 * @return boolean
	 */
	protected function _init_ptname() {

		parent::_init_ptname();
		$this->_ptname['classes'] = voa_h_cache::get_instance()->get('plugin.travel.goodsclass', 'oa');
		$this->_ptname['columns'] = voa_h_cache::get_instance()->get('plugin.travel.goodstablecol', 'oa');
		$this->_ptname['options'] = voa_h_cache::get_instance()->get('plugin.travel.goodstablecolopt', 'oa');
	}

}

