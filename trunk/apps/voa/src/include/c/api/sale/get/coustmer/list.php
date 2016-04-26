<?php
/**
 * voa_c_api_sale_get_coustmer_list
 * 客户列表
 * $Author$ tim_zhang
 * $Id$
 */
class voa_c_api_sale_get_coustmer_list extends voa_c_api_base {

	public function execute() {
		// 需要的参数
		$fields = array(
			// 当前页码
			'page' => array('type' => 'int', 'required' => false),
			// 每页显示数据数
			'limit' => array('type' => 'int', 'required' => false),
			// 客户来源
			'source' => array('type' => 'int', 'required' => false),
			// 销售阶段
			'status' => array('type' => 'int', 'required' => false),
			'cm_uid' => array('type' => 'int', 'required' => false),
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
		$this->uda_coustmer_list = &uda::factory('voa_uda_frontend_sale_coustmer_list');
		$reslut = array();
		$request['source'] = $this->_params['source'];
		$request['cm_uid'] = $this->_params['cm_uid'];
		$request['status'] = $this->_params['status'];
		$request['start'] = $this->_start;
		$request['limit'] = $this->_params['limit'];
		$this->uda_coustmer_list->doit($request,$reslut);
		$this->_list = $reslut;

		//整理数据
		$data = array();
		$this->uda_coustmer_list->listformat($this->_list,$data); 
		// 输出结果
		$this->_result = array(
			'limit' => $this->_params['limit'],
			'page' => $this->_params['page'],
			'list' => $data
		);
		return true;
	}
}
