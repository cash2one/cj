<?php
/**
 * base.php
 * 云工作后台/系统设置/门店管理/基类
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_system_shop_base extends voa_c_admincp_system_base {

	/** 门店配置信息 */
	protected $_p_set = array();
	/** 门店所在场所类型ID */
	protected $_placetypeid = 1;

	protected function _before_action($action) {

		if (!parent::_before_action($action)) {
			return false;
		}

		// uda 基类
		$uda_place_abstract = new voa_uda_frontend_common_place_abstract();

		// 获取场地配置信息
		$this->_p_set = $uda_place_abstract->p_sets;
		// 将场地配置信息注入模板
		$this->view->set('p_set', $this->_p_set);
		// 门店所在场所类型ID
		$this->_placetypeid = $this->_p_set['placetypeid_shop'];

		return true;
	}

	protected function _after_action($action) {
		return false;
	}

}
