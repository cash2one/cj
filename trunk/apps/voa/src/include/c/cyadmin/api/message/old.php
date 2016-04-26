<?php

/**
 * @Author: ppker
 * @Date:   2015-08-06 21:59:42
 * @Last Modified by:   ChangYi
 * @Last Modified time: 2015-08-26 13:07:57
 */
class voa_c_cyadmin_api_message_old extends voa_c_cyadmin_api_base {


	public function execute() {

		if( ! empty( $this->_get['page'] ) ) {
			$page = $this->_get['page'];
		} else {
			$page = 1;
		}

		if( ! empty( $this->_get['info_'] ) ) {

			$string = authcode( rbase64_decode( $this->_get['info_'] ), config::get( 'voa.development.cyadmin.urlkey' ), 'DECODE' );
			$info   = explode( '`', $string );
			$uid    = $info['0'];
			$epid   = $info['1'];
			//身份验证
			$serv_admin = &service::factory( 'voa_s_cyadmin_enterprise_profile' );

			if( ! $serv_admin->fetch( $epid ) ) {
				$this->_errcode = '10009';
				$this->_errmsg  = '非法操作';

				return false;
			}

		} else {
			$this->_errcode = '10009';
			$this->_errmsg  = '非法操作';

			return false;
		}

		if( ! empty( $this->_get['title'] ) ) {
			$title = $this->_get['title'];
		} else {
			$title = '';
		}

		$message   = array();
		$uda       = &uda::factory( 'voa_uda_cyadmin_enterprise_msglist' );
		$all_count = '';
		$multi     = '';
		$uda->list_old( $page, $uid, $epid, $message, $all_count, $multi, $title );


		$list = array();
		if( $message ) {
			//$uda->_formdata($message,$list);
			foreach( $message as &$_val ) {
				$_val['created'] = rgmdate( $_val['created'], 'Y-m-d  H:i' );
			}
			$list = $message;
		}

		// 输出结果
		$this->_result = array(
			'total' => $all_count,
			'list'  => $list,
			'multi' => $multi
		);

		//输出jsonp类型
		$this->_output( $errcode = 0, $errmsg = '', $result = array(), $type = 'jsonp' );
	}


}
