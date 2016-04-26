<?php
/**
 * memberdump.php
 * 导出指定条件的员工数据
 * @uses php tool.php -n nvotedump -vote 4 -ep_id xx
 * -vote 投票id
 * -ep_id 指定导出的数据库名(企业id)
 * Create By luck
 * $Author$
 * $Id$
 */
class voa_backend_tool_nvotedump extends voa_backend_base {

	/** 参数 */
	private $__opts = array();
	/** 数据库连接 */
	protected $_db;
	/** 所有部门信息 */
	private $__department = array();
	//投票选项信息
	private $__nvote_options = array();

	public function __construct($opts) {

		parent::__construct();
		$this->__opts = $opts;
	}

	public function main() {

		// 连接数据库
		$cfg = config::get('voa.db.dbadmin');
		$tablepre = $cfg['tablepre'];
		$this->_db = &db::init($cfg);

		error_reporting(E_ALL);

		// 投票id
		if (empty($this->__opts['vote'])) {
			echo 'please press must params "-vote"';
			return;
		}

		$vote_id = rintval($this->__opts['vote']);
		if ($vote_id < 1) {
			echo 'please press correct params "-vote"';
			return;
		}

		//数据库id
		if (empty($this->__opts['ep_id'])) {
			echo 'please press must params "-ep_id"';
			return;
		}

		$ep_id = str_replace('ep_', '', $this->__opts['ep_id']);

		try {
			$this->_db->query('USE '.$ep_id);

			// 所有部门信息
			$department = array();
			$q = $this->_db->query("SELECT * FROM `oa_common_department` WHERE cd_status<".voa_d_oa_common_department::STATUS_REMOVE);
			while ($row = $this->_db->fetch_array($q)) {
				$department[$row['cd_id']] = $row;
			}
			$this->__department = $department;

			foreach ($this->__department as $_cd_id => &$_cd) {
				$allname = array();
				$this->__get_department_name($_cd_id, $allname);
				$_cd['allname'] = implode('>', $allname);
			}

			//查询条件
			$where = array();
			$where[] = "`nvote_id`='{$vote_id}'";
			$where[] = "`status`<".voa_d_oa_nvote::STATUS_DELETE;
			$where = $where ? " WHERE ".implode(' AND ', $where) : '';

			$nvote_where[]  = "`id`='{$vote_id}'";
			$nvote_where[] = "`status`<".voa_d_oa_nvote::STATUS_DELETE;
			$nvote_where = $nvote_where ? " WHERE ".implode(' AND ', $nvote_where) : '';
			//查询投票主信息
			$nvote_query = $this->_db->query("SELECT * FROM `oa_nvote` {$nvote_where}");
			$nvote = null;
			if (!$nvote = $this->_db->fetch_array($nvote_query)) {
				echo 'empty nvote data';
				return;
			}
			$nvote = $this->__csv_format($nvote);

			//查询用户投票选项关联
			$m_uids = array();
			$member_options = array();
			$query = $this->_db->query("SELECT m_uid, GROUP_CONCAT(nvote_option_id) option_ids, created FROM `oa_nvote_mem_option` {$where} GROUP BY m_uid order by created");
			while ($row = $this->_db->fetch_array($query)) {
				$m_uids[] = $row['m_uid'];
				$member_options[] = $row;
			}
			if (empty($member_options)) {
				echo 'empty option data ';
				return;
			}

			//查询投票选项
			$query = $this->_db->query("SELECT * FROM oa_nvote_option {$where}");
			while ($row = $this->_db->fetch_array($query)) {
				$this->__nvote_options[$row['id']] = $row['option'];
			}

			//查询用户信息
			$members = array();
			$query = $this->_db->query("SELECT m_uid,m_username FROM oa_member WHERE m_uid IN (" . implode(',', $m_uids) . ")");
			while ($row = $this->_db->fetch_array($query)) {
				$row = $this->__csv_format($row);
				$members[$row['m_uid']] = $row['m_username'];
			}

			$data = array();
			$data[] = '用户名称,所在部门,投票选项,投票时间';
			foreach ($member_options as $mp) {
				$created = rgmdate($mp['created']);
				$username = isset($members[$mp['m_uid']]) ? $members[$mp['m_uid']] : '';
				$data[] = "{$username},{$this->__get_department($mp['m_uid'])},{$this->__get_nvote_option($mp['option_ids'])},{$created}";
			}

			file_put_contents(dirname(__FILE__).DIRECTORY_SEPARATOR. str_replace('/', '_', $nvote['subject']) . '.csv', implode("\r\n", $data));

		} catch (Exception $e) {
			if (stripos($e->getMessage(), 'Unknown database ') === false) {
				echo "\nep_{$i}: ".($e->getMessage())."\n";
			} else {
				echo $e->getMessage();
			}
		}

	}

	/**
	 * 获取投票选项信息
	 * @param $option_ids
	 * @return string
	 */
	private function __get_nvote_option($option_ids) {

		$options = explode(',', $option_ids);
		$option_names = array();
		foreach ($options as $option_id) {
			if (isset($this->__nvote_options[$option_id])) {
				$option_names[] = $this->__nvote_options[$option_id];
			}
		}

		return $this->__csv_format(implode('|', $option_names));
	}

	/**
	 * 获取指定部门的所有级别
	 * @param number $cd_id
	 * @param array $names
	 * @return boolean
	 */
	private function __get_department_name($cd_id, &$names) {
		if (!isset($this->__department[$cd_id])) {
			// 可能是最顶级部门，则按层级排序
			krsort($names);
			return true;
		}

		$names[] = $this->__department[$cd_id]['cd_name'];
		if ($this->__department[$cd_id]['cd_upid'] && isset($this->__department[$this->__department[$cd_id]['cd_upid']]['cd_upid']) && $this->__department[$this->__department[$cd_id]['cd_upid']]['cd_upid']) {
			$this->__get_department_name($this->__department[$cd_id]['cd_upid'], $names);
		} else {
			krsort($names);
			return true;
		}

		return true;
	}


	/**
	 * 获取部门
	 * @param $m_uid
	 * @return string
	 */
	private function __get_department($m_uid) {

		$cd_ids = array();
		$q = $this->_db->query("SELECT * FROM `oa_member_department` WHERE `m_uid`={$m_uid}");
		while ($row = $this->_db->fetch_array($q)) {
			$cd_ids[] = $row['cd_id'];
		}

		if (empty($cd_ids)) {
			return '';
		}

		$dps = array();
		foreach ($cd_ids as $_cd_id) {
			if (isset($this->__department[$_cd_id])) {
				$dps[] = $this->__department[$_cd_id]['allname'];
			}
		}
		return $this->__csv_format(implode(',', $dps));
	}


	/**
	 * 格式化投票信息
	 * @param $string
	 * @return string
	 */
	private function __csv_format($string) {
		if (is_array($string)) {
			foreach ($string as $_k => &$_v) {
				$_v = $this->__csv_format($_v);
			}
			return $string;
		}
		if (strpos($string, ',') === false) {
			return $string;
		}

		return '"' . $string . '"';
	}
}
