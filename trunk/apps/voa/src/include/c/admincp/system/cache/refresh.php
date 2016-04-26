<?php
/**
 * voa_c_admincp_system_cache_refresh
 * 企业后台/系统设置/缓存更新
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_system_cache_refresh extends voa_c_admincp_system_cache_base {

	public function execute() {

		/** 更新缓存操作 */
		$uda_base = &uda::factory('voa_uda_frontend_base');
		$uda_base->update_cache();

		$this->message('success', '缓存更新操作完毕', false, false);

	}

}
