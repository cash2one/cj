<?php

/**
 * voa_c_admincp_office_thread_base
 * 企业后台/同事社区/基本控制器
 * Create By gaosong
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_thread_base extends voa_c_admincp_office_base {
	protected function _before_action( $action ) {

		if( ! parent::_before_action( $action ) ) {
			return false;
		}

		return true;
	}
}
