<?php
/**
 * CrontabModel.class.php
 * $author$
 */

namespace Common\Model;

class CrontabModel extends AbstractModel {

	// 任务
	const TYPE_SIGN = 'sign';
	const TYPE_MEETING = 'meeting';

	// 构造方法
	public function __construct() {

		parent::__construct();
		// 字段前缀
		$this->prefield = 'c_';
	}

	/**
	 * 检查任务类型
	 * @param string $type 任务类型
	 */
	public function check_type($type) {

		// 所有可用任务类型
		static $types = array(
			self::TYPE_MEETING, self::TYPE_SIGN
		);

		$type = (string)$type;
		return !(empty($type) || !in_array($type, $types));
	}

	/**
	 * 根据域名和签到类型删除计划任务
	 * @param string $domain 域名
	 * @param string $type 任务类型
	 * @return boolean
	 */
	public function del_by_domain_type($domain, $type) {

		return $this->_m->execsql("DELETE FROM __TABLE__ WHERE `c_domain`=? AND `c_type`=? AND `c_status`<?", array(
			$domain, $type, $this->get_st_delete()
		));
	}

	/**
	 * 根据域名和签到类型删除计划任务
	 * @param string $taskid 任务id
	 * @param string $domain 域名
	 * @param string $type 任务类型
	 * @return boolean
	 */
	public function del_by_taskid_domain_type($taskid, $domain, $type) {

		return $this->_m->execsql("DELETE FROM __TABLE__ WHERE `c_taskid`=? AND `c_domain`=? AND `c_type`=? AND `c_status`<?", array(
			$taskid, $domain, $type, $this->get_st_delete()
		));
	}

	/**
	 * 根据taskid/域名/任务类型读取计划任务信息
	 * @param string $taskid 计划任务id
	 * @param string $domain 域名
	 * @param string $type 任务类型
	 */
	public function get_by_taskid_domain_type($taskid, $domain, $type) {

		return $this->_m->fetch_row("SELECT * FROM __TABLE__ WHERE `c_taskid`=? AND `c_domain`=? AND `c_type`=? AND `c_status`<?", array(
			$taskid, $domain, $type, $this->get_st_delete()
		));
	}

	/**
	 * 根据条件读取计划任务列表
	 * @param int $runtime 执行时间
	 * @param int|array $page_option 分页参数
	 * @throws service_exception
	 */
	public function list_by_runtime($runtime, $page_option = null) {

		// 分页参数
		$limit = '';
		if (!$this->_limit($limit, $page_option)) {
			return false;
		}

		// 读取记录
		return $this->_m->fetch_array("SELECT * FROM __TABLE__ WHERE `c_runtime`<? AND `c_status`<?{$limit}", array(
			$runtime, $this->get_st_delete()
		));
	}

	/**
	 * 根据计划任务id更新任务信息
	 * @param int $id 任务ID
	 * @param int $looptime 间隔时间
	 */
	public function update_runtime_by_id($id, $looptime) {

		return $this->_m->execsql("UPDATE __TABLE__ SET `c_runtime`=`c_runtime`+?, `c_runs`=`c_runs`+?, `c_status`=?, `c_updated`=? WHERE `c_id`=?", array(
			$looptime, 1, $this->get_st_update(), NOW_TIME, $id
		));
	}

}
