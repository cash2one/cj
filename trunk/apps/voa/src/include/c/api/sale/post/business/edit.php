<?php
/**
 * voa_c_api_sale_post_business_edit
 * 商机新增或者修改
 * $Author$ tim_zhang
 * $Id$
 */
class voa_c_api_sale_post_business_edit extends voa_c_api_base {

	
	public function execute() {
		// 需要的参数
		$fields = array(
			'bid' => array('type' => 'int', 'required' => false),//商机ID
			'scid' => array('type' => 'int', 'required' => true),//客户ID
			'types ' => array('type' => 'int', 'required' => false),//状态
			'title' => array('type' => 'string_trim', 'required' => true),//机会名称
			'amount' => array('type' => 'number', 'required' => true),//预计金额
			'content' => array('type' => 'string_trim', 'required' => true),//备注
		);
		if (!$this->_check_params($fields)) {
			// 检查参数
			return false;
		}
		
		//获取参数
		$request = array(
						'bid' => $this->_params['bid'],
						'scid' => $this->_params['scid'],
						'm_uid' => startup_env::get('wbs_uid'),
						'type' => $this->_params['types'],
						'title' => $this->_params['title'],
						'amount' => $this->_params['amount'],
						'content' => $this->_params['content']
						);

		//用户id
		$request['m_uid'] = startup_env::get('wbs_uid');

		$reslut = array();

		$uda_business = &uda::factory('voa_uda_frontend_sale_business_edit');
		if ($uda_business->doit($request, $reslut)) {
			$this->_errcode = $uda_business->errcode;
			$this->_errmsg = $uda_business->errmsg;
			// 输出结果
			$this->_result = array(
				'url' => '/frontend/sale/business_view?pluginid='. startup_env::get('pluginid').'&bid='.$reslut['bid'],
				'message' => "成功");
			return true;
		} else {
			$this->_errcode = $uda_business->errcode;
			$this->_errmsg = $uda_business->errmsg;
			return false;
		}
	}
}
