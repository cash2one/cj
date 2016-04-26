<?php
/**
 * voa_uda_uc_webhost_base
 * uc/web主机
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_uc_webhost_base extends voa_uda_uc_base {

	/** webhost 表*/
	public $service = null;

	public function __construct() {
		parent::__construct();
		if ($this->service === null) {
			$this->service = &service::factory('voa_s_uc_webhost');
		}
	}

}
