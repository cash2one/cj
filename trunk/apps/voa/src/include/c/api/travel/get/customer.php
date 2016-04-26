<?php
/**
 * voa_c_api_travel_get_customer
 * 获取客户列表信息
 * $Author$
 * $Id$
 */

class voa_c_api_travel_get_customer extends voa_c_api_travel_customerabstract {
	// 最大值
	protected $_max_limit = 5000;

	public function execute() {

		// 判断是否要重新返回客户列表
		if (!$this->_chk_update()) {
			$this->_result = array('data' => array());
			return true;
		}

		// 获取分页参数
		$page = (int)$this->_get('page');
		$limit = (int)$this->_get('limit');
		$limit = 0 >= $limit ? $this->_max_limit : $limit;
		list($start, $perpage, $page) = voa_h_func::get_limit($page, $limit);

		// 读取数据
		$total = 0;
		$list = array();
		$uda = &uda::factory('voa_uda_frontend_customer_data', $this->_ptname);
		if (!$uda->list_all($this->_params, array($start, $perpage), $list, $total)) {
			$this->_errcode = $uda->errno;
			$this->_errmsg = $uda->error;
			return true;
		}

		// 获取用户名称
		$this->_get_users($list);

		$this->_result = array(
			'total' => $total,
			'data' => empty($list) ? array() : array_values($list)
		);

		return true;
	}

	/**
	 * 获取用户名称
	 * @param array $list 客户列表
	 * @return boolean
	 */
	protected function _get_users(&$list) {

		// 如果不是后台调用
		if (0 == $this->_is_admin) {
			return true;
		}

		// 如果列表为空
		if (empty($list)) {
			return true;
		}

		// 取出所有用户 uid
		$uids = array();
		foreach ($list as $_v) {
			$uids[$_v['uid']] = $_v['uid'];
		}

		// 根据 uids 读取用户信息
		$serv_member = &service::factory('voa_s_oa_member');
		$users = $serv_member->fetch_all_by_ids($uids);

		// 在返回数据中加入用户名
		foreach ($list as &$_v) {
			if (empty($users[$_v['uid']])) {
				$_v['username'] = '';
				continue;
			}

			$_v['username'] = $users[$_v['uid']]['m_username'];
		}

		return true;
	}

	// 判断是否需要更新客户列表
	protected function _chk_update() {

		// 取时间戳
		$ts = (int)$this->_get('timestamp');
		if (empty($ts)) {
			return true;
		}

		// 根据时间戳读取数据
		$t = new voa_d_oa_customer_data();
		// 统计条件
		$conds = array('updated>?' => $ts, 'uid' => $this->_member['m_uid']);
		if (0 >= $t->count_by_updated_conds($conds, true)) {
			return false;
		}

		return true;
	}

	/**
	 * 设置插件/表格名称
	 * @return boolean
	 */
	protected function _init_ptname() {

		parent::_init_ptname();
		$this->_ptname['classes'] = voa_h_cache::get_instance()->get('plugin.travel.customerclass', 'oa');
		$this->_ptname['columns'] = voa_h_cache::get_instance()->get('plugin.travel.customertablecol', 'oa');
		$this->_ptname['options'] = voa_h_cache::get_instance()->get('plugin.travel.customertablecolopt', 'oa');

		// 只取自己的
		if (0 == $this->_is_admin) {
			$this->_ptname['conds'] = array(
				'uid' => $this->_member['m_uid']
			);
		}
	}

}
