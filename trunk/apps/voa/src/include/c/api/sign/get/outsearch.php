<?php

/**
 * voa_c_api_sign_get_outsearch
 * 外勤查询
 * $Id$
 */
class voa_c_api_sign_get_outsearch extends voa_c_api_base {

	protected $_start = 0;

	public function execute() {
		if (! $this->__execute()) {
			return false;
		}
		;
		
		$serv = &service::factory('voa_s_oa_sign_location');
		$reslut = array();
		$request['start'] = $this->_start;
		$request['limit'] = $this->_params['limit'];
		$request['udate'] = date('Y-m-d', time());
		
		if (! empty($this->_params['udate'])) {
			$request['udate'] = $this->_params['udate'];
		}
		
		$uda = &uda::factory('voa_uda_frontend_sign_out');
		$uda_mem = &uda::factory('voa_uda_frontend_member_get');
		
		$request['m_uid'] = startup_env::get('wbs_uid');
		if (! empty($this->_params['m_uid'])) {
			$request['m_uid'] = $this->_params['m_uid'];
		}
		$perm = array();
		$uda_mem->sub_muids_by_muid($request['m_uid'], $perm);
		$perm[] = $request['m_uid'];
		$request['uids'] = $perm;
		
		$result = array();
		$uda->doit($request, $result);
		if (! empty($result)) {
			$result = $uda->listformat($result);
		} else {
			$result = array();
		}
		$this->_list = $reslut;
		
		// 整理数据
		$data = array();
		
		// 输出结果
		$this->_result = array('limit' => $this->_params['limit'], 'page' => $this->_params['page'], 'list' => $result);
		
		return true;
	}
	/**
	 * 数据验证
	 * @return boolean
	 */
	private function __execute() {
		// 需要的参数
		$fields = array(
			// 当前页码
			'page' => array('type' => 'int', 'required' => false), 
			// 每页显示数据数
			'limit' => array('type' => 'int', 'required' => false), 
			// 上报人员ID
			'm_uid' => array('type' => 'int', 'required' => false), 
			// 日期
			'udate' => array('type' => 'string', 'required' => false));
		if (! $this->_check_params($fields)) {
			// 检查参数
			return false;
		}
		
		if ($this->_params['page'] < 1) {
			// 设定当前页码的默认值
			$this->_params['page'] = 1;
		}
		
		if ($this->_params['limit'] < 1) {
			// 设定每页数据条数的默认值
			$this->_params['limit'] = 10;
		}
		// 获取分页参数
		list($this->_start, $this->_params['limit'], $this->_params['page']) = voa_h_func::get_limit($this->_params['page'], $this->_params['limit'], 100);
		
		return true;
	}

}
