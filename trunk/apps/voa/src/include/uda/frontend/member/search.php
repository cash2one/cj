<?php

/**
 * voa_uda_frontend_member_get
 * 统一数据访问/用户表/获取
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_frontend_member_search extends voa_uda_frontend_member_base {
	/** 部门信息列表 */
	protected $_departments = array();
	protected $_jobs = array();
	/** 部门等级关系 */
	protected $_p2c = array();
	/** 用户列表 */
	protected $_list_p2c = array();

	/** 上下级关系 */
	protected $_all_p2c = array();

	public function __construct() {
		/** 读取职位信息 */
		$this->_jobs = voa_h_cache::get_instance()->get('job', 'oa');
		/** 读取部门信息 */
		$this->_departments = voa_h_cache::get_instance()->get('department', 'oa');
		// p2c 关系
		foreach ($this->_departments as $_dp) {
			if (empty($this->_all_p2c[$_dp['cd_upid']])) {
				$this->_all_p2c[$_dp['cd_upid']] = array();
			}
			$this->_all_p2c[$_dp['cd_upid']][$_dp['cd_id']] = $_dp['cd_id'];
		}
		parent::__construct();
	}

	public function get_cd_ids($uid) {

		// 先取用户所在的所有部门
		$serv_dp = &service::factory('voa_s_oa_member_department');
		$cd_ids = array();
		$cd_ids = $serv_dp->fetch_all_by_uid($uid);

		// 取所有cd_id
		/**foreach ($dps as $_dp) {
		 * //$cd_ids[] = $_dp['cd_id'];
		 * $cd_ids[] = $_dp;
		 * }*/
		// 根据部门cd_id读取所有权限
		$serv_cd = &service::factory('voa_s_oa_common_department');
		$cd_infos = $serv_cd->fetch_all_by_key($cd_ids);

		//var_dump($cd_infos);exit;
		// 遍历所有部门， 判断权限
		$all_cd_ids = array();
		foreach ($cd_infos as $_cd) {
			if (voa_d_oa_common_department::PURVIEW_AllCOMPANY == $_cd['cd_purview'] || !$_cd['cd_purview']) {
				return true;
			}
			$all_cd_ids = array_merge($all_cd_ids, $this->get_child_cd_id($_cd['cd_id'], false));
			if (voa_d_oa_common_department::PURVIEW_OLNYOWNSECTION == $_cd['cd_purview']) {
				$all_cd_ids[] = $_cd['cd_id'];
			}
		}
		return $all_cd_ids;
	}

	//递归获取部门id
	public function get_child_cd_id($cd_id, $self = true) {
		$rets = array();
		if ($self) {
			$rets[] = $cd_id;
		}

		if (!empty($this->_all_p2c[$cd_id])) {
			foreach ($this->_all_p2c[$cd_id] as $_id) {
				$rets = array_merge($rets, $this->get_child_cd_id($_id));
			}
		}
		return $rets;
	}

	/**
	 * 根据关键字搜索相关用户
	 * @param unknown $sotext
	 * @param unknown $data
	 * @param unknown $uid
	 * @return boolean
	 */
	public function search($submit, &$data, $uid) {

		$this->_params = $submit;
		/** 读取员工信息 */
		$servm = &service::factory('voa_s_oa_member', array('pluginid' => 0));
		$conditions = array();
		$cd_ids = array();
		if (true === ($cd_ids = $this->get_cd_ids($uid))) {

		} else {
			$conditions['cd_id'] = $cd_ids;
		}
		$serv_addrso = &service::factory('voa_s_oa_member_search', array('pluginid' => 0));
		$kw = '';
		if (!empty($submit['kw'])) {
			/** 根据用户名搜索通讯录信息 */
			//$kw = array('%' . $submit['kw'] . '%', 'like');
			$kw = $submit['kw'];
			/** 搜索条件 */
		}
		/** 从搜索表取通讯录数据 */
		$list_so = $serv_addrso->fetch_by_conditions_search($kw, $conditions);
		/** 取出 nc_id */
		$m_uids = array();
		foreach ($list_so as $v) {
			$m_uids[] = $v['m_uid'];
		}
		$members = $servm->fetch_all_by_ids($m_uids);
		$this->sort($members, $data);
		return true;
	}

	/**
	 * 整理用户数据
	 * @param unknown $members
	 */
	public function sort($members, &$data) {

		foreach ($members as $k => $v) {
			voa_h_user::push($v);
			$cd_id = (int)$v['cd_id'];
			$cj_id = (int)$v['cj_id'];

			if (empty($this->_departments[$cd_id])) {
				continue;
			}

			if (!array_key_exists($v['cd_id'], $this->_list_p2c)) {
				$this->_list_p2c[$v['cd_id']] = array();
			}

			$this->_list_p2c[$v['cd_id']][] = array(
				'isPerson' => true,
				'id' => $v['m_uid'],
				'name' => $v['m_username'],
				'face' => voa_h_user::avatar($v['m_uid'], $v),
				'job' => empty($this->_jobs[$cj_id]) ? '' : $this->_jobs[$cj_id]['cj_name'],
				'profileURL' => '/frontend/addressbook/show/uid/' . $v['m_uid']
			);

			$this->_fill_to_root($v['cd_id']);
		}

		uksort($this->_p2c, array($this, '_cmp'));
		foreach ($this->_p2c as $k => &$_ar) {
			usort($_ar, array($this, 0 == $k ? '_cmp' : '_r_cmp'));
		}
		unset($_ar);

		/** 拼凑返回数据 */
		$nodes = array();
		if (!empty($this->_p2c[0])) {
			foreach ($this->_p2c[0] as $_cd_id) {
				$this->_sort_by_cd_id($_cd_id, $nodes[]);
			}
		}

		$data = array(
			'type' => (int)$this->get('type'),
			'root' => array(
				'name' => $this->setting['sitename'],
				'isRoot' => true,
				'nodes' => $nodes
			)
		);

		return true;
	}

	/**
	 * 排序
	 * @param int $a
	 * @param int $b
	 * @return number
	 */
	protected function _cmp($a, $b) {

		if (0 == $a) {
			return 1;
		}

		if (0 == $b) {
			return -1;
		}

		if ($this->_departments[$a]['cd_id'] == $this->_departments[$b]['cd_id']) {
			return 0;
		}

		return ($this->_departments[$a]['cd_id'] < $this->_departments[$b]['cd_id']) ? -1 : 1;
	}

	protected function _r_cmp($a, $b) {

		$r = $this->_cmp($a, $b);
		return 0 == $r ? 0 : (-1 == $r ? 1 : -1);
	}

	protected function _fill_to_root($cd_id) {

		if (0 == $cd_id || !array_key_exists($cd_id, $this->_departments)) {
			return true;
		}

		$dp = $this->_departments[$cd_id];
		if (!array_key_exists($dp['cd_upid'], $this->_p2c)) {
			$this->_p2c[$dp['cd_upid']] = array();
		}

		$this->_p2c[$dp['cd_upid']][$dp['cd_id']] = $dp['cd_id'];
		return $this->_fill_to_root($dp['cd_upid']);
	}

	/**
	 * 拼凑返回数据
	 * @param int $cd_id 部门id
	 * @param array $data 人员数据
	 * @return boolean
	 */
	protected function _sort_by_cd_id($cd_id, &$data) {

		$dp = $this->_departments[$cd_id];
		$tmps = array();

		if (array_key_exists($cd_id, $this->_p2c)) {
			foreach ($this->_p2c[$cd_id] as $_cd_id) {
				array_unshift($tmps, array());
				$this->_sort_by_cd_id($_cd_id, $tmps[0]);
			}
		}

		if (array_key_exists($cd_id, $this->_list_p2c)) {
			$tmps = array_merge($this->_list_p2c[$cd_id], $tmps);
		}

		$data = array(
			'name' => $dp['cd_name'],
			'nodes' => $tmps
		);
		return true;
	}
}
