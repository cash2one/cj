<?php

/**
 *  voa_uda_frontend_campaign_base
 *  统一数据访问/活动推广/基本控制
 *  Create By XiaodingChen
 * */
class voa_uda_frontend_campaign_base extends voa_uda_frontend_base {

	private $__dbClass;

	public function __construct() {

		parent::__construct();
		
		$this->_sets = voa_h_cache::get_instance()->get('plugin.campaign.setting', 'oa');
		
		$this->__dbClass = array('def' => 'voa_d_oa_campaign_db', 'campaign' => 'voa_d_oa_campaign_campaign', 'total' => 'voa_d_oa_campaign_total', 'share' => 'voa_d_oa_campaign_share', 'custom' => 'voa_d_oa_campaign_custom', 'customer' => 'voa_d_oa_campaign_customer', 'reg' => 'voa_d_oa_campaign_reg', 'right' => 'voa_d_oa_campaign_right', 'type' => 'voa_d_oa_campaign_type', 'setting' => 'voa_d_oa_campaign_setting', 'orders' => 'voa_d_oa_campaign_orders', 'customcols' => 'voa_d_oa_campaign_customcols');
	}

	protected function _campaign($name) {
		
		// 如果类不存在，抛出错误
		if (!isset($this->__dbClass[$name])) {
			
			$class = sprintf('voa_d_oa_campaign_%s', $name);
			
			$alert = sprintf('%s,该类不存在', $class);
			trigger_error($alert, E_USER_ERROR);
			return false;
		}
		
		$class = $this->__dbClass[$name];
		return new $class();
	}

} 