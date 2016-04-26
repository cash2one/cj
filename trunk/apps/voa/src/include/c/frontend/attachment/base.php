<?php
/**
 * 附件基类
 * $Author$
 * $Id$
 */

class voa_c_frontend_attachment_base extends voa_c_frontend_base {

	protected function _before_action($action) {
		if (!parent::_before_action($action)) {
			return false;
		}

		return true;
	}

}
