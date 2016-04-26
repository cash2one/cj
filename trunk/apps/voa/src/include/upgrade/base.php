<?php
/**
 * voa_upgrade_base
 * $Author$
 * $Id$
 */

class voa_upgrade_base {
	// db
	protected $_db = null;
	// 当前版本
	protected $_ver = '';

	public function __construct() {

		// 连接数据库
		$app_name = startup_env::get('app_name');
		$dbs = config::get($app_name.'.db.oa');
		$this->_db = db::init($dbs[0]);
	}

	public function update_version() {

		if (empty($this->_ver)) {
			return true;
		}

		$serv = &service::factory('voa_s_oa_common_setting');
		$serv->update_setting(array('version' => $this->_ver));
		return true;
	}

}
