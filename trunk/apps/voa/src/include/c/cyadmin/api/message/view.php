<?php

/**
 *    voa_c_cyadmin_api_message_view
 * 消息详情
 * $Author$
 * $Id$
 */
class voa_c_cyadmin_api_message_view extends voa_c_cyadmin_api_base {

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


		//查询消息列表

		if( empty( $this->_get ) ) {
			$this->_errcode = '10005';
			$this->_errmsg  = '非法操作';

			return false;
		}
		$uda = &uda::factory( 'voa_uda_cyadmin_enterprise_msglist' );

		$message_log  = &uda::factory( 'voa_uda_cyadmin_enterprise_message_log' );
		$data         = array();
		$info['meid'] = $this->_get['meid'];
		$info['uid']  = $uid;
		$uda->getview( $info, $data );
		// 获取发送消息的时间
		$send_time = $message_log->get_messagelog_time( $this->_get['logid'] );
		if( ! $send_time ) {
			$this->_errcode = '10006';
			$this->_errmsg  = '数据出现错误!';

			return false;
		}

		$data['_created'] = rgmdate( $send_time, 'Y-m-d H:i' );

		// 输出结果
		$this->_result = array(
			'data' => $data
		);

		//输出jsonp类型
		$this->_output( $errcode = 0, $errmsg = '', $result = array(), $type = 'jsonp' );
	}


}
