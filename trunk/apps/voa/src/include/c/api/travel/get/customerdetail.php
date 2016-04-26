<?php
/**
 * voa_c_api_travel_get_customerdetail
 * 获取客户详情信息
 * $Author$
 * $Id$
 */

class voa_c_api_travel_get_customerdetail extends voa_c_api_travel_customerabstract {
	// 读取备注
	protected $_extend_remark = 1;
	// 读取意向产品
	protected $_extend_attgoods = 2;

	public function execute() {

		// 获取分页参数
		$dataid = (int)$this->_get('dataid');

		// 读取数据
		$data = array();
		$uda = &uda::factory('voa_uda_frontend_customer_data', $this->_ptname);
		if (!$uda->get_one($dataid, $data)) {
			$this->_errcode = $uda->errno;
			$this->_errmsg = $uda->error;
			return true;
		}

		// 剔除主键键值
		if (!isset($data['slide']) && !empty($data['slide'])) {
			$data['slide'] = array_values($data['slide']);
		}

		$this->_get_user($data);

		$this->_result = $data;

		// 把客户id加入条件
		$this->_ptname['conds'] = array('customer_id' => $data['dataid']);
		$this->_result['remark'] = array();
		$this->_result['goods'] = array();
		// 读取备注
		$extends = (int)$this->_get('extends');
		if ($this->_extend_remark == ($extends & $this->_extend_remark)) {
			$this->_get_remark();
		}

		// 读取意向产品
		if ($this->_extend_attgoods == ($extends & $this->_extend_attgoods)) {
			$this->_get_attgoods();
		}

		return true;
	}

	/**
	 * 获取用户名称
	 * @param array $data 客户信息
	 * @return boolean
	 */
	protected function _get_user(&$data) {

		// 如果不是后台调用
		if (0 == $this->_is_admin) {
			return true;
		}

		// 如果列表为空
		if (empty($data)) {
			return true;
		}

		var_dump($data);exit;
		// 根据 uids 读取用户信息
		$serv_member = &service::factory('voa_s_oa_member');
		$user = $serv_member->fetch($data['uid']);

		var_dump($user);exit;
		// 在返回数据中加入用户名
		$data['username'] = $user['m_username'];

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
	}

	// 读取备注
	protected function _get_remark() {

		$total = 0;
		$list = array();
		$uda = &uda::factory('voa_uda_frontend_travel_customer2remark', $this->_ptname);
		if (!$uda->list_all($this->_params, null, $list, $total)) {
			return true;
		}

		$this->_result['remark'] = empty($list) ? array() : array_values($list);

		return true;
	}

	// 读取意向产品
	protected function _get_attgoods() {

		$list = array();
		$uda = &uda::factory('voa_uda_frontend_travel_customer2goods', $this->_ptname);
		if (!$uda->list_all($this->_params, null, $list)) {
			return true;
		}

		$this->_result['goods'] = empty($list) ? array() : array_values($list);

		return true;
	}

}
