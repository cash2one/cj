<?php
/**
 * memberdump.php
 * 导出指定条件的员工数据
 * @uses php tool.php -n memberdump -qywxstatus 4 -ep_id xx
 * -qywxstatus 关注状态：1已关注，2已冻结，4未关注
 * -ep_id 指定导出的数据库名(企业id)
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_backend_tool_memberdump extends voa_backend_base {

	/** 参数 */
	private $__opts = array();
	/** 数据库连接 */
	protected $_db;
	/** 所有部门信息 */
	private $__department = array();

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

		// 导出关注状态
		$this->__opts['qywxstatus'] = !isset($this->__opts['qywxstatus']) ? 4 : $this->__opts['qywxstatus'];
		if (!in_array($this->__opts['qywxstatus'], array(1, 2, 4))) {
			$this->__opts['qywxstatus'] = 4;
		}
		// 指定企业ID（数据库名）
		$ep_id = 23970;
		if (!empty($this->__opts['ep_id'])) {
			$ep_id = str_replace('ep_', '', $this->__opts['ep_id']);
		}

		try {
			$this->_db->query('USE ep_'.$ep_id);
			$output = '';

			$where = array();
			if ($this->__opts['qywxstatus']) {
				$where[] = "`m_qywxstatus`='{$this->__opts['qywxstatus']}'";
			}
			$where[] = "`m_status`<".voa_d_oa_member::STATUS_REMOVE;
			$where = $where ? " WHERE ".implode(' AND ', $where) : '';

			// 所有部门信息
			$department = array();
			$q = $this->_db->query("SELECT * FROM `oa_common_department` WHERE cd_status<".voa_d_oa_common_department::STATUS_REMOVE);
			while ($row = $this->_db->fetch_array($q)) {
				$department[$row['cd_id']] = $row;
			}
			$this->__department = $department;
			unset($department);

			foreach ($this->__department as $_cd_id => &$_cd) {
				$allname = array();
				$this->__get_department_name($_cd_id, $allname);
				$_cd['allname'] = implode('>', $allname);
			}
			unset($this->__department);

			// 取用户列表
			$member_list = array();
			$member_list[] = '姓名,手机号,微信号,所在部门,关注状态';
			$query = $this->_db->query("SELECT * FROM `oa_member` {$where}");
			while ($row = $this->_db->fetch_array($query)) {
				$row2 = $this->__csv_format($row);
				//print_r($row2);exit;
				$member_list[] = "{$row2['m_username']},{$row2['m_mobilephone']},{$row2['m_weixin']},{$this->__get_department($row['m_uid'])},{$row2['m_qywxstatus']}";
			}

			file_put_contents(dirname(__FILE__).DIRECTORY_SEPARATOR.'memberdump.csv', implode("\r\n", $member_list));

		} catch (Exception $e) {
			if (stripos($e->getMessage(), 'Unknown database ') === false) {
				echo "\nep_{$i}: ".($e->getMessage())."\n";
			} else {
				echo $e->getMessage();
			}
		}

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
