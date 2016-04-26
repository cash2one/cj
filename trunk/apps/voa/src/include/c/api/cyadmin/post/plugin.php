<?php

/**
 * voa_c_api_cyadmin_post_plugin
 * 获取应用安装记录
 * Created by zhoutao.
 * Created Time: 2015/8/3  14:08
 */
class voa_c_api_cyadmin_post_plugin extends voa_c_api_cyadmin_base {

	protected $_require_login = false;

	public function execute() {

		// 判断密钥是否为空
		$post = $this->request->postx();
		if( empty( $post['key'] ) ) {
			$this->_errcode = '10000';
			$this->_errmsg  = '密钥不得为空';

			return false;
		}

		// 判断密钥是否正确
		$key    = config::get( 'voa.rpc.client.auth_key' );
		$de_key = authcode( authcode( $post['key'], $key, 'DECODE' ), $key, 'DECODE' );
		if( empty ( $de_key ) ) {
			$this->_errcode = '10001';
			$this->_errmsg  = '密钥错误';

			return false;
		}

		// 判断是否过期
		$now_timestamp = startup_env::get('timestamp');
		if ($now_timestamp - $de_key >= 60) {
			$this->_errcode = '10002';
			$this->_errmsg  = '密钥过期';

			return false;
		}

		// 返回应用安装记录
		$serv = &service::factory('voa_s_oa_common_pluginorm');
		$callboack = $serv->list_by_conds(array('cp_available' => 4));

		$this->_result = $callboack;

		return true;
	}


}
