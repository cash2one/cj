<?php
/**
 * voa_c_api_sale_post_coustmer_edit
 * 客户新增或者修改
 * $Author$ tim_zhang
 * $Id$
 */
class voa_c_api_sale_post_coustmer_edit extends voa_c_api_base {

	
	public function execute() {
		// 需要的参数
		$fields = array(
			// 客户id
			'scid' => array('type' => 'int', 'required' => false),
			'companyshortname' => array('type' => 'string_trim', 'required' => true),//公司简称
			'company' => array('type' => 'string_trim', 'required' => true),//公司全称
			'address' => array('type' => 'string_trim', 'required' => true),//联系地址
			'source' => array('type' => 'string_trim', 'required' => true),//来源ID
			'name' => array('type' => 'string_trim', 'required' => true),//联系人
			'phone' => array('type' => 'string_trim', 'required' => true),//联系电话
			'm_uid' => array('type' => 'string_trim', 'required' => false),//跟踪客户的销售人员ID
			'fields' => array('type' => 'array', 'required' => false),//后台人员自定义字段
		);
		if (!$this->_check_params($fields)) {
			// 检查参数
			return false;
		}
		//获取参数
		$request = array(
						'scid' => $this->_params['scid'],
						'companyshortname' => $this->_params['companyshortname'],
						'company' => $this->_params['company'],
						'address' => $this->_params['address'],
						'source' => $this->_params['source'],
						'name' => $this->_params['name'],
						'phone' => $this->_params['phone'],
						);
		//用户自定义字段
		if(!empty($this->_params['fields'])) {
			$request['sfields'] = $this->_params['fields'];
		}

		//用户id
		$request['m_uid'] = startup_env::get('wbs_uid');

		$reslut = array();

		$uda_coustmer = &uda::factory('voa_uda_frontend_sale_coustmer_edit');
		if ($uda_coustmer->doit($request,$reslut)) {
			$this->_errcode = $uda_coustmer->errcode;
			$this->_errmsg = $uda_coustmer->errmsg;
			// 输出结果
			$this->_result = array(
				'url' => '/frontend/sale/coustmer_view/?pluginid='. startup_env::get('pluginid').'&scid='.$reslut['scid'],
				'message' => "成功");
			return true;
		} else {
			$this->_errcode = $uda_coustmer->errcode;
			$this->_errmsg = $uda_coustmer->errmsg;
			return false;
		}
	}
}
