<?php
/**
 * Department.class.php
 * 部门操作
 * $Author$
 */

namespace Common\Common;
use Think\Log;
use Common\Common\Cache;

class Department {

	// 部门列表
	protected $_departments = array();
	// 上级部门id => 子部门ID列表
	protected $_p2c = array();
	// uid => cdid
	protected $_uid2c_cdids = array();
	protected $_uid2p_cdids = array();
	// cdid => parent cdid
	protected $_cdid2p_cdids = array();

	// 实例化
	public static function &instance() {

		static $instance;
		if (empty($instance)) {
			$instance = new self();
		}

		return $instance;
	}

	public function __construct() {

		// 获取部门缓存
		$cache = &\Common\Common\Cache::instance();
		$this->_departments = $cache->get('Common.department');
	}

	/**
	 * 根据用户id获取当前部门或所有上级部门
	 * @param int $uid 用户UID
	 * @param bool|false $parent 是否取上级部门
	 * @param bool|false $force 是否强制重新读取
	 * @return array
	 */
	public function list_cdid_by_uid($uid, $parent = false, $force = false) {

		$my_cdids = array();
		// 只取一级部门id，没调用过或者调用过需要强制重新读取
		if (empty($this->_uid2c_cdids[$uid]) || true == $force) {
			$conds = array('m_uid' => $uid);
			$serv_mdp = D('Common/MemberDepartment', 'Service');
			$my_cdids = array_column((array)$serv_mdp->list_by_conds($conds), 'cd_id');

			// 将数据存到静态变量里
			$this->_uid2c_cdids[$uid] = $my_cdids;
		} else { // 只取一级部门id，并且已有数据，不重新读取
			// 从静态变量里读数据
			$my_cdids = $this->_uid2c_cdids[$uid];
		}

		// 如果不查上级部门
		if (!$parent) {
			return $my_cdids;
		}

		$parent_cdids = array();
		// 上级部门为空或者取上级部门id并且强制重新读取
		if (empty($this->_uid2p_cdids[$uid]) || (!empty($this->_uid2p_cdids[$uid]) && true == $force)) {
			// 遍历每个当前部门
			foreach ($this->_uid2c_cdids[$uid] as $_cdid) {
				// 取多个部门的所有上级部门
				$this->list_parent_cdids($_cdid, $parent_cdids);
			}

			// 将查出来的结果放到静态变量里
			$this->_uid2p_cdids[$uid] = $parent_cdids;
		} else { // 从静态变量里读数据
			$parent_cdids = $this->_uid2p_cdids[$uid];
		}

		return array($my_cdids, $parent_cdids);
	}

	/**
	 * 递归查上级部门方法—公共方法
	 * @param int $cdid 部门
	 * @param array &$p_cdids 上级部门ID
	 * @return array
	 */
	public function list_parent_cdids($cdid, &$p_cdids, $lv = 0) {

		static $origin_cdid = 0;
		// 如果是第一次获取, 则记录起始部门id
		if (0 == $lv) {
			$origin_cdid = $cdid;
		}

		// 如果记录存在, 则直接使用
		if (isset($this->_cdid2p_cdids[$cdid])) {
			$p_cdids = array_merge($p_cdids, $this->_cdid2p_cdids[$cdid]);
			return true;
		}

		// 如果当前部门不存在
		if (!isset($this->_departments[$cdid])) {
			$this->_cdid2p_cdids[$cdid] = !is_array($p_cdids) ? array() : $p_cdids;
			return true;
		}

		$upid = (int)$this->_departments[$cdid]['cd_upid'];
		// 不是顶级部门
		if (0 < $upid) {
			// 存储上级部门id
			$p_cdids[$upid] = $upid;
			// 没到顶级部门，继续递归
			$this->list_parent_cdids($upid, $p_cdids, ++ $lv);
		}

		return true;
	}

	/**
	 * 获取指定部门下的所有子部门
	 * @param string|array $cdids 部门ID
	 * @param bool $include_self 是否包含本身
	 */
	public function list_childrens_by_cdid($cdids, $include_self = false) {

		// 如果部门信息为空
		if (empty($cdids)) {
			return array();
		}

		// 非数组则按 ',' 切分
		if (!is_array($cdids)) {
			$cdids = explode(',', $cdids);
		}

		$return = array();
		// 如果返回值包含自己
		if ($include_self) {
			$return = $cdids;
		}

		// 排序
		sort($cdids);
		$cd_key = implode(',', $cdids);
		// 如果之前已经获取过
		if (!empty($this->_p2c[$cd_key])) {
			return array_merge($this->_p2c[$cd_key], $return);
		}

		// 获取子部门信息
		foreach ($cdids as $_cdid) {
			if (!isset($this->_p2c[$_cdid])) {
				$this->_p2c[$_cdid] = $this->_list_childrens($_cdid);
			}

			$return = array_merge($this->_p2c[$_cdid], $return);
		}

		$return = array_unique($return);
		$this->_p2c[$cd_key] = $return;
		return $return;
	}

	/**
	 * 找出下级部门
	 * @param int $cd_id 部门id
	 * @return array
	 */
	protected function _list_childrens($cd_id) {

		$dp_ids = array();
		foreach ($this->_departments as $_dep) {
			if ($_dep['cd_upid'] != $cd_id) {
				continue;
			}

			$dp_ids[$_dep['cd_id']] = $_dep['cd_id'];
			$dp_ids = array_merge($dp_ids, $this->_list_childrens($_dep['cd_id']));
		}

		return $dp_ids;
	}

	/**
	 * 获取最顶级部门id
	 * @param int $cdid 部门id
	 * @return boolean
	 */
	public function get_top_cdid(&$cdid) {

		static $top_cd_id = 0;
		if (0 < $cd_id) {
			$cdid = $top_cd_id;
			return true;
		}

		// 获取顶级部门id
		foreach ($this->_departments as $_dep) {
			if (0 == $_dep['cd_upid']) {
				$top_cd_id = $_dep['cd_id'];
				break;
			}
		}

		$cdid = $top_cd_id;
		return 0 < $cdid ? true : false;
	}

}
