<?php
/**
 * rsyncmem.php
 * 同步用户
 * @uses php tool.php -n rsyncmem -ep_id 10000
 * $Author$
 * $Id$
 */
class voa_backend_tool_rsyncmem extends voa_backend_base {
	/** 参数 */
	private $__opts = array();

	public function __construct($opts) {

		parent::__construct();
		$this->__opts = $opts;
	}

	public function main() {return;

		/** 连接数据库 */
		$cfg = config::get('voa.db.dbadmin');
		$tablepre = $cfg['tablepre'];
		$db = &db::init($cfg);

		startup_env::set('domain', 'cnpenjing');
		voa_h_conf::init_db();
		$db->query('use ep_'.$this->__opts['ep_id']);
		$addr = new voa_wxqy_addressbook();

		/**array ( 'userid' => 'zhangsan', 'name' => 'string',
			* 'department' => 'number', 'position' => 'string', 'mobile' => 'string',
			* 'gender' => 'number', 'tel' => 'string', 'email' => 'string', 'weixinid' =>
			* 'string', 'qq' => 'number', );*/
		$query = $db->query("SELECT * FROM oa_common_department WHERE cd_status<3");
		$departments = array();
		while ($row = $db->fetch_array($query)) {
			$departments[$row['cd_id']] = $row;
		}

		$query = $db->query("SELECT * FROM oa_common_job WHERE cj_status<3");
		$jobs = array();
		while ($row = $db->fetch_array($query)) {
			$jobs[$row['cj_id']] = $row;
		}

		$query = $db->query("SELECT * FROM oa_member WHERE m_status<4");
		while ($row = $db->fetch_array($query)) {
			$data = array(
				'userid' => $row['m_openid'],
				'name' => $row['m_username'],
				'department' => array($departments[$row['cd_id']]['cd_qywxid']),
				'position' => $jobs[$row['cj_id']]['cj_name'],
				'mobile' => $row['m_mobilephone'],
				'gender' => $row['m_gender'],
				'tel' => '',
				'email' => $row['m_email'],
				'weixinid' => $row['m_weixin'],
				'qq' => ''
			);
			if (!$addr->user_create($data, $result)) {
				echo $addr->errcode . ';' . $addr->errmsg . "\n";
			} else echo $row['m_username'] . " ok\n";
		}


	}

}
