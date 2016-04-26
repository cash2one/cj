<?php
/**
 * voa_c_api_travel_get_goodstablecolopt
 * 获取表格列选项信息
 * $Author$
 * $Id$
 */

class voa_c_api_travel_get_goodstablecolopt extends voa_c_api_travel_goodsabstract {

	protected function _before_action($action) {

		// 检查权限
		$this->_require_login = false;

		return parent::_before_action($action);
	}

	public function execute() {

		// 读取数据
		$list = array();
		$uda = &uda::factory('voa_uda_frontend_goods_tablecolopt', $this->_ptname);
		if (!$uda->list_all($this->_params, $list)) {
			$this->_errcode = $uda->errno;
			$this->_errmsg = $uda->error;
			return true;
		}

		// 遍历属性, 看单选/复选中是否有附件
		$columns = voa_h_cache::get_instance()->get('plugin.travel.goodstablecol', 'oa');
		foreach ($list as &$_v) {
			if (empty($columns[$_v['tc_id']])) {
				continue;
			}

			$col = $columns[$_v['tc_id']];
			if (in_array($col['ct_type'], array('radio', 'checkbox'))
					&& voa_d_oa_goods_tablecol::FTYPE_RDCHK_ATTACH == $col['ftype']) {
				$_v['attachurl'] = voa_h_attach::attachment_url($_v['attachid']);
			}
		}

		$this->_result = empty($list) ? array() : array_values($list);

		return true;
	}

	/**
	 * 设置插件/表格名称
	 * @return boolean
	 */
	protected function _init_ptname() {

		parent::_init_ptname();
		$this->_ptname['tablecolopts'] = voa_h_cache::get_instance()->get('plugin.travel.goodstablecolopt', 'oa');
	}

}
