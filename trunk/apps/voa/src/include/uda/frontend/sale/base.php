<?php
/**
 * voa_uda_frontend_sale_base
 * 统一数据访问/活动报名/基本控制
 * Create By tim_zhang
 * $Author$
 * $Id$
 */
class voa_uda_frontend_sale_base extends voa_uda_frontend_base {

	public function __construct() {
		parent::__construct();
		$this->_sets = voa_h_cache::get_instance()->get('plugin.sale.setting', 'oa');
	}
	
	/**
	 *根据状态值获取
	 *@param int $mold
	 *@return array data
	 */
	public function get_type($mold) {
		
		$type = &service::factory('voa_s_oa_sale_type');
		
		$data = $type->list_by_conds(array('type' => $mold));
		
		return $data;
	}

}
