<?php
/**
 * voa_c_api_travel_post_goodstablecol
 * 更新表格列属性信息
 * $Author$
 * $Id$
 */

class voa_c_api_superreport_post_goodstablecol extends voa_c_api_superreport_abstract {

	public function execute() {

		// 如果不是管理员
		if (0 == $this->_is_admin) {
			$this->_set_errcode(voa_errcode_oa_goods::NO_PRIVILEGES);
			return true;
		}

		// 获取表格 tc_id
		$tc_id = (int)$this->_params['tc_id'];

		// 更新数据
		$uda = &uda::factory('voa_uda_frontend_superreport_tablecol', $this->_ptname);
		$uda->member = $this->_member;
		if (0 < $tc_id) {
			if (!$uda->update($this->_params)) {
				$this->_errcode = $uda->errno;
				$this->_errmsg = $uda->error;
				return true;
			}
		} else {
			$tablecol = array();
			if (!$uda->add($this->_params, $tablecol)) {
				$this->_errcode = $uda->errno;
				$this->_errmsg = $uda->error;
				return true;
			}

			$this->_result = $tablecol;
		}

		return true;
	}

	/**
	 * 设置插件/表格名称
	 * @return boolean
	 */
	protected function _init_ptname() {

		parent::_init_ptname();
		$this->_ptname['tablecols'] = voa_h_cache::get_instance()->get('plugin.travel.goodstablecol', 'oa');
	}

}
