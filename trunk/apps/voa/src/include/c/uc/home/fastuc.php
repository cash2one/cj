<?php
/**
 * voa_c_uc_home_fastuc
 * UC用户授权登录
 * $Author$
 * $Id$
 */
class voa_c_uc_home_fastuc extends voa_c_uc_home_base {
	private $cookie;
    private $_cookiename;
    private $_cookieexpired = 3600;
	
	public function execute() {
		
		//返回授权码和过期时间
		$authcode = (string)$this->request->get('auth_code');
		$expiresin = (string)$this->request->get('expires_in');
		$corpid = (string)$this->request->get('corp_id');
		$state = (string)$this->request->get('state');
		
		//验证$state状态
		//if($state != ''){ $this->_error_message('授权操作失败, 请稍后重新尝试');return false;}

		$token_data = array(
			'corpid' => $corpid,//企业号（提供商）的corpid
			'provider_secret' => $secret,//提供商的secret，在提供商管理页面可见
		);
		
		//获取应用提供商凭证
		$urla = 'https://qyapi.weixin.qq.com/cgi-bin/service/get_provider_token';
		voa_h_func::get_json_by_post($data, $urla, $token_data);

		//获取企业号管理员登录信息
		$urlb = 'https://qyapi.weixin.qq.com/cgi-bin/service/get_login_info?provider_access_token='.$data['provider_access_token'];
		$post_data = array(
			'auth_code' => $authcode,//授权企业号管理员登录产生的code
		);
		voa_h_func::get_json_by_post($userdata, $urlb, $post_data);

		$list = $this->_check_user_list($userdata);
		
		return $list;
		
		return true;
		
	}
	
	
	/**
	 * 查询用户是否关联
	 * 有一个或者多个
	 * @param array $data 企业用户信息
	 * @return array
	 */
	protected function _check_user_list($data) {
		
		// 判断该企业号是否关联
		$serv_check = &service::factory('voa_s_uc_login2conn');
		
		$login_data = array(
			'conn_corpid' => $data['user_info']->userid,//企业ID
		);
		
		$list = $serv_check->fetch($login_data);
		
		
		return $list;
	}
	
	/**
	 * 把cookie写入缓存
	 * @param  string $filename 缓存文件名
	 * @param  string $content  文件内容
	 * @return bool
	 */
	public function saveCookie($filename,$content){
		return file_put_contents($filename,$content);
	}
	
	/**
	 * 读取cookie缓存内容
	 * @param  string $filename 缓存文件名
	 * @return string cookie
	 */
	public function getCookie($filename){
		if (file_exists($filename)) {
			$mtime = filemtime($filename);
			if ($mtime < time() - $this->_cookieexpired) return false;
			$data = file_get_contents($filename);
			if ($data) $this->cookie = $data;
		} 
		return $this->cookie;
	}
	
	/*
	 * 删除cookie
	 */
	public function deleteCookie($filename) {
		$this->cookie = '';
		@unlink($filename);
		return true;
	}
	


	

	


}
