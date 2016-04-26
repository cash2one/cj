<?php
/**
 * 活动/分类列表
 * $Author$
 * $Id$
 */
class voa_c_api_campaign_get_type extends voa_c_api_campaign_base {

	public function execute() {

		$type = voa_d_oa_campaign_type::get_type();

		// 输出结果
		$this->_result = $type;

		return true;
	}

}
