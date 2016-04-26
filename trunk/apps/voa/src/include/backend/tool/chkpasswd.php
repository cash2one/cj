<?php
/**
 * chkpasswd.php
 * 比对数据表
 * @uses php tool.php -n chkpasswd
 * $Author$
 * $Id$
 */
class voa_backend_tool_chkpasswd extends voa_backend_base {
	/** 参数 */
	private $__opts = array();

	public function __construct($opts) {

		parent::__construct();
		$this->__opts = $opts;
	}

	public function main() {

		/** 连接数据库 */
		$cfg = config::get('voa.db.dbadmin');
		$tablepre = $cfg['tablepre'];
		$db = &db::init($cfg);

		exit;
		/** 判断数据库是否存在 */
		for ($i = 10067; $i < 10167; ++ $i) {
			$db->query('use ep_'.$i);
			$q = $db->query('select * from oa_common_adminer where m_uid=1');
			$adminer = $db->fetch_array($q);
			$q = $db->query('select * from oa_member where m_uid=1');
			$member = $db->fetch_array($q);

			echo $i."\n";
			if (!empty($adminer['ca_password']) && !empty($adminer['ca_salt'])) {
				    //echo 'update oa_member set m_password="'.$adminer['ca_password'].'", m_salt="'.$adminer['ca_salt'].'" where m_uid=1'."\n";
				$db->query('update oa_member set m_password="'.$adminer['ca_password'].'", m_salt="'.$adminer['ca_salt'].'" where m_uid=1');
			} elseif (!empty($member['m_password']) && !empty($member['m_salt'])) {
				    //echo 'update oa_common_adminer set ca_password="'.$member['m_password'].'", ca_salt="'.$member['m_salt'].'" where m_uid=1'."\n";
				$db->query('update oa_common_adminer set ca_password="'.$member['m_password'].'", ca_salt="'.$member['m_salt'].'" where m_uid=1');
			} else {
				echo "???\n";
			}

			//print_r($adminer);print_r($member);
		}

	}

}
