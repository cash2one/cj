<?php

/**
 *    附件基类
 *    voa_c_cyadmin_attachment_base
 * */
class voa_c_cyadmin_attachment_base extends voa_c_cyadmin_base {
	protected function _before_action( $action ) {
		if( ! parent::_before_action( $action ) ) {
			return false;
		}

		return true;
	}
}
