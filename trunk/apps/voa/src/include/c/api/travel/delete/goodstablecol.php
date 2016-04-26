<?php
/**
 * voa_c_api_travel_delete_goodstablecol
 * 删除表格列信息
 * $Author$
 * $Id$
 */

class voa_c_api_travel_delete_goodstablecol extends voa_c_api_travel_goodsabstract {

	public function execute() {

		// 如果不是管理员
		if (0 == $this->_is_admin) {
			$this->_set_errcode(voa_errcode_oa_goods::NO_PRIVILEGES);
			return true;
		}

		// 获取属性ID
		$tc_id = (int)$this->_get('tc_id');
		// 如果属性ID为空
		if (empty($tc_id)) {
			$this->_set_errcode(voa_errcode_oa_goods::GOODS_TC_ID_IS_EMPTY);
			return true;
		}

		// 删除表格列信息
		$uda = &uda::factory('voa_uda_frontend_goods_tablecol', $this->_ptname);
		if (!$uda->delete($tc_id)) {
			$this->_errcode = $uda->errno;
			$this->_errmsg = $uda->error;
			return true;
		}

		// 更新缓存
		voa_h_cache::get_instance()->get('plugin.travel.goodstablecol', 'oa', true);

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
