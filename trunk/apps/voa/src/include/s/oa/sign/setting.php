<?php
/**
 * $Author$
 * $Id$
 */

class voa_s_oa_sign_setting extends voa_s_abstract {

	/** 分库/分表的信息 */
	private $__shard_key = array();

	public function __construct($shard_key = array()) {

		$this->__shard_key = $shard_key;
		parent::__construct();
	}

}

