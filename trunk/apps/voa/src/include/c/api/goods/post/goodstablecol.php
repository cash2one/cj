<?php
/**
 * voa_c_api_goods_post_goodstablecol
 * 更新表格列属性信息
 * $Author$
 * $Id$
 */

class voa_c_api_goods_post_goodstablecol extends voa_c_api_goods_abstract {

	public function execute() {

		// 获取表格 tc_id
		$tc_id = (int)$this->_params['tc_id'];

		// 更新数据
		$uda = &uda::factory('voa_uda_frontend_goods_tablecol', $this->_ptname);
		if (0 < $tc_id) {
			if (!$uda->update($this->_member, $this->_params, $tc_id)) {
				$this->_errcode = $uda->errno;
				$this->_errmsg = $uda->error;
				return true;
			}
		} else {
			$tablecol = array();
			if (!$uda->add($this->_member, $this->_params, $tablecol)) {
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
	protected function _set_ptname() {

		parent::_set_ptname();
		$this->_ptname['tablecols'] = voa_h_cache::get_instance()->get('plugin.goods.tablecol', 'oa');
	}

}
