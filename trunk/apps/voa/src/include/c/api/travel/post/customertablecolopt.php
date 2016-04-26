<?php
/**
 * voa_c_api_travel_post_customertablecolopt
 * 更新表格列选项信息
 * $Author$
 * $Id$
 */

class voa_c_api_travel_post_customertablecolopt extends voa_c_api_travel_customerabstract {

	public function execute() {

		// 如果不是管理员
		if (0 == $this->_is_admin) {
			$this->_set_errcode(voa_errcode_oa_goods::NO_PRIVILEGES);
			return true;
		}

		// 获取表格 tco_id
		$tco_id = (int)$this->_params['tco_id'];

		// 更新数据
		$uda = &uda::factory('voa_uda_frontend_customer_tablecolopt', $this->_ptname);
		if (0 < $tco_id) {
			if (!$uda->update($this->_member, $this->_params, $tco_id)) {
				$this->_errcode = $uda->errno;
				$this->_errmsg = $uda->error;
				return true;
			}
		} else {
			$opts = array();
			if (!$uda->add($this->_member, $this->_params, $opts)) {
				$this->_errcode = $uda->errno;
				$this->_errmsg = $uda->error;
				return true;
			}

			$this->_result = $opts;
		}

		// 更新缓存
		voa_h_cache::get_instance()->get('plugin.travel.customertablecolopt', 'oa', true);

		return true;
	}

}
