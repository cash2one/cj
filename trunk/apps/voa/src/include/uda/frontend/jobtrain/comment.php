<?php
/**
 * 培训-评论
 * Create By wowxavi
 * $Author$
 * $Id$
 */

class voa_uda_frontend_jobtrain_comment extends voa_uda_frontend_base {
	/** service 类 */
	private $__service = null;
	public function __construct() {
		parent::__construct();
		if ($this->__service == null) {
			$this->__service = new voa_s_oa_jobtrain_comment();
		}
	}
	
}