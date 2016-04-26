<?php
/**
 * voa_c_api_sale_get_business_list
 * 商机列表
 * $Author$ tim_zhang
 * $Id$
 */
class voa_c_api_sale_get_business_list extends voa_c_api_base {

	public function execute() {
		// 需要的参数
		$fields = array(
			// 当前页码
			'page' => array('type' => 'int', 'required' => false),
			// 每页显示数据数
			'limit' => array('type' => 'int', 'required' => false),
			// 时间区间
			'time' => array('type' => 'int', 'required' => false),
			// 商机状态
			'type' => array('types' => 'int', 'required' => false)
		);
		if (!$this->_check_params($fields)) {
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

		// list获取
		$this->uda_business_list = &uda::factory('voa_uda_frontend_sale_business_list');
		$reslut = array();
		$request['time'] = $this->_params['time'];
		$request['type'] = $this->_params['type'];
		$request['start'] = $this->_start;
		$request['limit'] = $this->_params['limit'];
		$this->uda_business_list->doit($request,$reslut);
		$this->_list = $reslut;

		//整理数据
		$data = array();
		$this->uda_business_list->listformat($this->_list,$data); 

		// 输出结果
		$this->_result = array(
			'limit' => $this->_params['limit'],
			'page' => $this->_params['page'],
			'list' => $data
		);
		return true;
	}
}
