<?php
/**
 * voa_c_uc_home_fastuc
 * UC用户授权登录
 * https://uc.dev.vchangyi.com/uc/api/post/fastuc
 * $Author$
 * $Id$
 */
class voa_c_uc_api_post_fastuc extends voa_c_uc_api_base {
	
	public function execute() {

		/*请求的参数*/
		$fields = array(
			/*授权企业号管理员登录产生的code*/
			'conn_corpid' => array('type' => 'string_trim', 'required' => true),
			/*留言内容*/
			'conn_userid' => array('type' => 'string_trim', 'required' => true),
			/*二级域名*/
			'dp_cname' => array('email' => 'string_trim', 'required' => true),
			/*手机号码*/
			'conn_mobilephone' => array('email' => 'string_trim', 'required' => true),
		);
		/*基本验证检查*/
		if (!$this->_check_params($fields)) {
			return false;
		}
		
		// 载入uda
		$serv_check = &service::factory('voa_uda_uc_login2conn_insert');

		//获取数据
		$login_data = array(
		  'conn_corpid' => $this->_params['conn_corpid'], //企业corpid
		  'conn_userid' => $this->_params['conn_userid'], //企业userid
		  'dp_cname' => $this->_params['enumber'], //主机记录
		  'conn_mobilephone' => $this->_params['conn_mobilephone'], //手机号
		);
		//验证字段//判断是否重//判断是否存在
		$serv_check->insert_add($login_data);
			
		return true;


	}

	

	


}
