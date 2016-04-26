<?php
/**
 * base.php
 * 应用访问统计
 * $Author$
 * $Id$
 */
class voa_c_frontend_stat_base extends voa_c_frontend_base {

	protected function _before_action($action) {

		if (!parent::_before_action($action)) {
			return false;
		}

		return true;
	}

}
