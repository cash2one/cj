<?php

/**
 * base.php
 * 总后台/企业管理/基类
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_cyadmin_enterprise_account_base extends voa_c_cyadmin_enterprise_base {

	protected function _before_action( $action ) {
		if( ! parent::_before_action( $action ) ) {
			return false;
		}

		return true;
	}

	protected function _after_action( $action ) {
		if( ! parent::_after_action( $action ) ) {
			return false;
		}

		return true;
	}

}
