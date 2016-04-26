<?php
/**
 * voa_c_api_travel_post_customer
 * 更新客户信息
 * $Author$
 * $Id$
 */

class voa_c_api_travel_post_customer extends voa_c_api_travel_customerabstract {

	public function execute() {

		// 管理员不能编辑客户信息
		if (0 ==1 && 1 == $this->_is_admin) {
			$this->_set_errcode(voa_errcode_oa_goods::NO_PRIVILEGES);
			return true;
		}

		// 获取表格 dataid
		$dataid = 0;
		if (isset($this->_params['dataid'])) {
			$dataid = (int)$this->_params['dataid'];
		}

		// 更新数据
		$uda = &uda::factory('voa_uda_frontend_customer_data', $this->_ptname);
		if (0 < $dataid) {
			if (!$uda->update($this->_member, $this->_params, $dataid)) {
				$this->_errcode = $uda->errno;
				$this->_errmsg = $uda->error;
				return true;
			}
		} else {
			$customer = array();
			if (!$uda->add($this->_member, $this->_params, $customer)) {
				$this->_errcode = $uda->errno;
				$this->_errmsg = $uda->error;
				return true;
			}

			// 更新对应的备注
			$this->_remark2customer($customer['dataid']);

			// 更新对应的产品
			$this->_goods2customer($customer['dataid']);

			$this->_result = $customer;
		}

		return true;
	}

	/**
	 * 关联产品和客户
	 * @return boolean
	 */
	protected function _goods2customer($customer_id = 0) {

		// 如果产品或客户id为空
		if (empty($this->_params['goods_id']) || empty($customer_id)) {
			return true;
		}

		// 新增关联信息
		$this->_params['customer_id'] = $customer_id;
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
	 * 关联备注和客户
	 * @param int $customer_id 客户id
	 * @return boolean
	 */
	protected function _remark2customer($customer_id) {

		// 如果备注 id 为空
		if (!isset($this->_params['crk_ids'])) {
			return true;
		}

		// 切分备注 id
		$crk_ids = $this->_params['crk_ids'];
		if (!is_array($crk_ids)) {
			$crk_ids = explode(',', $crk_ids);
		}

		// 关联备注和客户
		$uda_c2r = &uda::factory('voa_uda_frontend_travel_customer2remark', $this->_ptname);
		if (!$uda_c2r->mv_remark2customer($crk_ids, $customer_id)) {
			$this->_errcode = $uda_c2r->errno;
			$this->_errmsg = $uda_c2r->error;
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
		$this->_ptname['classes'] = voa_h_cache::get_instance()->get('plugin.travel.customerclass', 'oa');
		$this->_ptname['columns'] = voa_h_cache::get_instance()->get('plugin.travel.customertablecol', 'oa');
		$this->_ptname['options'] = voa_h_cache::get_instance()->get('plugin.travel.customertablecolopt', 'oa');
	}

}
