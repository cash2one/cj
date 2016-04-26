<?php
/**
 * voa_c_api_sale_get_coustmer_list
 * 轨迹列表
 * $Author$ tim_zhang
 * $Id$
 */
class voa_c_api_sale_get_trajectory_list extends voa_c_api_base {

	public function execute() {
		// 需要的参数
		$fields = array(
			// 当前页码
			'page' => array('type' => 'int', 'required' => false),
			// 每页显示数据数
			'limit' => array('type' => 'int', 'required' => false),
			// 客户ID
			'scid' => array('type' => 'int', 'required' => false),
			// 来源ID
			'source' => array('type' => 'int', 'required' => false),
			// 客户状态ID
			'status_id' => array('type' => 'int', 'required' => false),
			// 销售人员ID
			'm_uid' => array('type' => 'int', 'required' => false),
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

		$ulist= &uda::factory('voa_uda_frontend_member_get');
		$uid = startup_env::get('wbs_uid');
	
		//获取所有下级人员id
		$m_uids = array();
		$ulist->sub_muids_by_muid($uid,$m_uids);
		//var_dump($m_uids);
	
		/*
		$m_uids=array(
		481,
		20,
		 21
		);
		*/
		
		// list获取
		$this->uda_trajectory_list = &uda::factory('voa_uda_frontend_sale_trajectory_list');

		$reslut = array();
		$request['scid'] = $this->_params['scid'];
		$request['source'] = $this->_params['source'];
		$request['stid'] = $this->_params['status_id'];
		$request['m_uid'] = $this->_params['m_uid'];
		$request['start'] = $this->_start;
		$request['limit'] = $this->_params['limit'];
		$request['uids'] = $m_uids;
		if(!$this->uda_trajectory_list->doit($request,$reslut)){
			$this->_errcode = $this->uda_trajectory_list->errcode;
            $this->_errmsg =  $this->uda_trajectory_list->errmsg;
            return false;
		};
		$this->_list = $reslut;

		//整理数据
		$data = array();
		$this->uda_trajectory_list->listformat($this->_list,$data); 

		// 输出结果
		$this->_result = array(
			'limit' => $this->_params['limit'],
			'page' => $this->_params['page'],
			'list' => $data
		);
		return true;
	}
}
