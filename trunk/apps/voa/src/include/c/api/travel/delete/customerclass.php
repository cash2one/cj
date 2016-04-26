<?php
/**
 * voa_c_api_travel_delete_customerclass
 * 删除分类信息
 * $Author$
 * $Id$
 */

class voa_c_api_travel_delete_customerclass extends voa_c_api_travel_customerabstract {

	public function execute() {

		// 如果不是管理员
		if (0 == $this->_is_admin) {
			$this->_set_errcode(voa_errcode_oa_goods::NO_PRIVILEGES);
			return true;
		}

		// 获取分类ID
		$classid = (int)$this->_get('classid');
		// 如果分类为空
		if (empty($classid)) {
			$this->_set_errcode(voa_errcode_oa_customer::CLASSID_IS_EMPTY);
			return true;
		}

		// 删除表格信息
		$uda = &uda::factory('voa_uda_frontend_customer_class', $this->_ptname);
		if (!$uda->delete($classid)) {
			$this->_errcode = $uda->errno;
			$this->_errmsg = $uda->error;
			return true;
		}

		// 缓存更新
		voa_h_cache::get_instance()->get('plugin.travel.customerclass', 'oa', true);

		return true;
	}

	/**
	 * 设置插件/表格名称
	 * @return boolean
	 */
	protected function _init_ptname() {

		parent::_init_ptname();
		$this->_ptname['classes'] = voa_h_cache::get_instance()->get('plugin.travel.customerclass', 'oa');
	}

}
