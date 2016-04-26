<?php
/**
 * voa_upgrade_ver15112718(未上线)
 * $Author$
 * $Id$
 */

class voa_upgrade_ver15112718 extends voa_upgrade_base {

	/** 当前升级的应用信息 */
	private $__plugin = array();

	public function __construct() {

		parent::__construct();
		$this->_ver = '15112718';
	}

	// 升级
	public function upgrade() {

		// 判断应用表是否存在
		$row = $this->_db->query("SHOW TABLES LIKE 'oa_thread'");
		if ($this->_db->fetch_row($row)) {
			// 应用表结构升级
			$this->_db->query("ALTER TABLE `oa_thread` CHANGE `attach_id` `attach_id` varchar(255) NOT NULL DEFAULT ''");
		}

		return true;
	}

}
