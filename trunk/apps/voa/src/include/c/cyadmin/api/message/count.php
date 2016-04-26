<?php

/**
 * voa_c_cyadmin_api_message_count
 * 未读消息数
 * $Author$
 * $Id$
 */
class voa_c_cyadmin_api_message_count extends voa_c_cyadmin_api_base {


	public function execute() {

		if( ! empty( $this->_get['info_'] ) ) {
			$string = authcode( rbase64_decode( $this->_get['info_'] ), config::get( 'voa.development.cyadmin.urlkey' ), 'DECODE' );

			$info = explode( '`', $string );
			$uid  = $info['0'];
			$epid = $info['1'];
			//身份验证
			$serv_admin = &service::factory( 'voa_s_cyadmin_enterprise_profile' );

			if( ! $serv_admin->fetch( $epid ) ) {
				$this->_errcode = '10009';
				$this->_errmsg  = '非法操作';

				return false;
			}
		} else {
			return false;
		}

		$uda       = &uda::factory( 'voa_uda_cyadmin_enterprise_msglist' );
		$msg_count = 0;

		$uda->msg_count( $uid, $epid, $msg_count );
		//查询用户是否已经付费

		$paystatus = '';
		$uda->get_paystatus( $epid, $paystatus );
		// 输出结果
		$this->_result = array(
			'msg_count' => $msg_count,
			'paystatus' => $paystatus
		);


		//输出jsonp类型
		$this->_output( $errcode = 0, $errmsg = '', $result = array(), $type = 'jsonp' );
	}
}
