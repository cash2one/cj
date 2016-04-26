<?php
/**
 * MemberDepartmentService.class.php
 * $author$ zhubeihai
 */
namespace Common\Service;

use Common\Service\AbstractService;

class MemberDepartmentService extends AbstractService {

	/** 部门缓存 */
	protected $_department = array();
	/** 部门人数 */
	protected $_dep_count = array();

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->_d = D('Common/MemberDepartment');
	}

	/**
	 * 从指定部门中获取用户m_uid数据
	 * @param array $cdids 部门id数组
	 */
	public function list_by_cdid($cdids, $page_option, $order_option = array('m_index' => 'ASC')) {

		return $this->_d->list_by_cdid((array)$cdids, $page_option, $order_option);
	}

	public function count_by_cdid($cdids) {

		return $this->_d->count_by_cdid((array)$cdids);
	}

	/**
	 * 获取关于用户UID的部门
	 * @param $m_uid
	 * @return mixed
	 */
	public function list_by_uid($uid) {

		return $this->_d->list_by_uid($uid);
	}

	/**
	 * 查询 部门的人数
	 * @param array $cd_ids 部门id
	 * @return mixed 部门对应 人数 数组
	 */
	public function count_all_department_member_num($cd_ids) {

		if (is_array($cd_ids)) {
			$cd_ids = implode(',', $cd_ids);
		}

		return $this->_d->count_all_department_member_num($cd_ids);
	}

	/**
	 * 查询 部门的人数 根据uid去重
	 * @param array $cd_ids 部门id
	 * @return mixed 部门对应 人数 数组
	 */
	public function unique_count_all_department_member_num($cd_ids) {

		if (is_array($cd_ids)) {
			$cd_ids = implode(',', $cd_ids);
		}

		$ct = $this->_d->unique_count_all_department_member_num($cd_ids);

		return (int)$ct['ct'];
	}

	/**
	 * 统计一个部门的所有人数(包括下级部门)
	 * @param $cd_id
	 * @return mixed
	 */
	public function count_dep_member($cd_id) {

		if (empty($this->_dep_count)) {
			// 获取部门缓存
			$cache = &\Common\Common\Cache::instance();
			$this->_department = $cache->get('Common.department');
			// 获取所有部门ID
			$cd_ids = array_unique(array_column($this->_department, 'cd_id'));
			// 统计所有部门人数
			$counts = $this->count_all_department_member_num($cd_ids);
			// 重组部门 人数 数组
			foreach ($counts as $_value) {
				$this->_dep_count[$_value['cd_id']] = $_value['ct'];
			}
		}

		// 判断是否有下级部门
		$low_dep = $this->_list_childrens($cd_id);

		if (empty($low_dep)) {
			if (isset($this->_dep_count[$cd_id])) {
				$count = $this->_dep_count[$cd_id];
			} else {
				$count = 0;
			}
		} else {
			$low_dep[] = $cd_id;
			$count = $this->unique_count_all_department_member_num($low_dep);
		}

		return $count;
	}

	/**
	 * 找出下级部门
	 * @param int $cd_id 部门id
	 * @return array
	 */
	protected function _list_childrens($cd_id) {

		$dp_ids = array();
		foreach ($this->_department as $_dep) {
			if ($_dep['cd_upid'] != $cd_id) {
				continue;
			}

			$dp_ids[] = $_dep['cd_id'];
			$dp_ids = array_merge($dp_ids, $this->_list_childrens($_dep['cd_id']));
		}

		return array_unique($dp_ids);
	}

}
