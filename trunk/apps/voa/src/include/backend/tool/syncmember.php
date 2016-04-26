<?php
/**
 * syncmember.php
 * 同步用户(关注状态)信息
 * @uses php tool.php -n syncmember
 * $Author$
 * $Id$
 */
class voa_backend_tool_syncmember extends voa_backend_base {
	/** 参数 */
	private $__opts = array();
	// 数据库连接
	protected $_db;

	public function __construct($opts) {

		parent::__construct();
		$this->__opts = $opts;
	}

	public function main() {

		exit;
		startup_env::set('domain', 'marykay');
		// 读取配置信息
		if (!voa_h_conf::init_db()) {
			exit('config file is missing.');
			return false;
		}

		/**include APP_PATH.'/logs/syncmember.php';

		$userids = array();
		foreach ($userlist as $_u) {
			$userids[] = $_u['userid'];
		}*/

		$serv = &service::factory('voa_s_oa_member');
		$list = $serv->fetch_all_by_conditions(array('m_qywxstatus' => 0));
		$csv = array('UserID,Email,Username');
		foreach ($list as $_u) {
			$csv[] = $_u['m_openid'].','.$_u['m_email'].','.$_u['m_username'];
		}

		logger::error(implode("\n", $csv));
		//$serv->update_by_conditions(array('m_qywxstatus' => 1), array('m_openid' => $userids));
		return true;

		$page = 1;
		$perpage = 100;
		while (true) {
			/**$addr = &voa_wxqy_addressbook::instance();
			$result = array();
			if (!$addr->department_simple_list($result)) {
				exit('read error');
				return false;
			}*/

			// userid
			$userids = array();
			foreach ($result['userlist'] as $u) {
				$userids[$u['userid']] = $u['userid'];
			}

			logger::error(var_export($result['userlist'], true));
			break;
		}

		return true;
	}

}
