<?php
/**
 * voa_c_api_travel_post_customerclass
 * 更新分类信息
 * $Author$
 * $Id$
 */

class voa_c_api_travel_post_customerclass extends voa_c_api_travel_customerabstract {

	public function execute() {

		// 如果不是管理员
		if (0 == $this->_is_admin) {
			$this->_set_errcode(voa_errcode_oa_goods::NO_PRIVILEGES);
			return true;
		}

		// 获取表格 classid
		$classid = (int)$this->_params['classid'];

		// 更新数据
		$uda = &uda::factory('voa_uda_frontend_customer_class', $this->_ptname);
		if (0 < $classid) {
			if (!$uda->update($this->_params, $classid)) {
				$this->_errcode = $uda->errno;
				$this->_errmsg = $uda->error;
				return true;
			}
		} else {
			$class = array();
			if (!$uda->add($this->_params, $class)) {
				$this->_errcode = $uda->errno;
				$this->_errmsg = $uda->error;
				return true;
			}

			$this->_result = $class;
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
