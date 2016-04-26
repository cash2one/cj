<?php
/**
 * voa_c_api_sale_post_trajectory_insert
 * 新增轨迹
 * $Author$ tim_zhang
 * $Id$
 */
class voa_c_api_sale_post_trajectory_insert extends voa_c_api_base {

	
	public function execute() {
		// 需要的参数
		$fields = array(
			// 客户id
			'scid' => array('type' => 'int', 'required' => true),//客户ID
			'stid' => array('type' => 'int', 'required' => true),//客户状态ID
			'content' => array('type' => 'string_trim', 'required' => false),//备注
			'at_ids' => array('at_ids' => 'string_trim', 'required' => false),// 附件ID
			'address' => array('type' => 'string_trim', 'required' => false),//地址
			'location' => array('type' => 'string_trim', 'required' => false),//经纬度
		);
		if (!$this->_check_params($fields)) {
			// 检查参数
			return false;
		}
		
		//获取参数
		$location = array();
		$location = $this->_params['location'];
		$location = explode(',', $location);
		if(is_array($location) &&
				count($location) >= 2) {
			$longitude = $location[0];
			$latitude = $location[1];
		} else {
			$longitude = '';
			$latitude = '';
		}
		$request = array(
						'scid' => $this->_params['scid'],
						'm_uid' => startup_env::get('wbs_uid'),
						'stid' => $this->_params['stid'],
						'content' => $this->_params['content'],
						'at_ids' => $this->_params['at_ids'],
						'present_address' => $this->_params['address'],
						'longitude' => $longitude,
						'latitude' => $latitude
						);

		$reslut = array();
		$uda_trajectory = &uda::factory('voa_uda_frontend_sale_trajectory_insert');
		if ($uda_trajectory->doit($request,$reslut)) {
			$this->_errcode = $uda_trajectory->errcode;
			$this->_errmsg = $uda_trajectory->errmsg;
			// 输出结果
			$this->_result = array(
				'url' => '/frontend/sale/trajectory_list/?pluginid='. startup_env::get('pluginid'),
				'message' => "成功");
			return true;
		} else {
			$this->_errcode = $uda_trajectory->errcode;
			$this->_errmsg = $uda_trajectory->errmsg;
			return false;
		}
		/*
		$reslut = array();
		$uda_trajectory = &uda::factory('voa_uda_frontend_sale_trajectory_insert');
		$uda_trajectory->doit($request, $reslut);
		
		if(empty($reslut)) {
			return $this->_set_errcode(voa_errcode_api_sale::COUSTMER_NULL);
		}
		// 输出结果
		$this->_result = array('url' => '/frontend/sale/trajectory_list/?pluginid='. startup_env::get('pluginid'), 'message' => "成功");
		return true;
		*/
	}
}
