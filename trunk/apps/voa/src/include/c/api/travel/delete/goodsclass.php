<?php
/**
 * voa_c_api_travel_delete_goodsclass
 * 删除分类信息
 * $Author$
 * $Id$
 */

class voa_c_api_travel_delete_goodsclass extends voa_c_api_travel_goodsabstract {

	public function execute() {

		// 如果不是管理员
		if (0 == $this->_is_admin) {
			$this->_set_errcode(voa_errcode_oa_goods::NO_PRIVILEGES);
			return true;
		}

		// 获取分类id
		$classid = (int)$this->_get('classid');
		if (empty($classid)) {
			$this->_set_errcode(voa_errcode_oa_travel::CLASSID_IS_EMPTY);
			return true;
		}

		// 删除表格信息
		$uda = &uda::factory('voa_uda_frontend_goods_class', $this->_ptname);
		if (!$uda->delete($classid)) {
			$this->_errcode = $uda->errno;
			$this->_errmsg = $uda->error;
			return true;
		}

		// 更新缓存
		voa_h_cache::get_instance()->get('plugin.travel.goodsclass', 'oa', true);

		return true;
	}

	/**
	 * 设置插件/表格名称
	 * @return boolean
	 */
	protected function _init_ptname() {

		parent::_init_ptname();
		$this->_ptname['classes'] = voa_h_cache::get_instance()->get('plugin.travel.goodsclass', 'oa');
	}

}
