<?php

/**
 * voa_c_api_invite_post_insert
 * 提交处理
 * @Author: ppker
 * @Date:   2015-07-09 17:43:49
 * @Last Modified by:   ChangYi
 * @Last Modified time: 2015-08-04 16:51:55
 */
class voa_c_api_common_post_addmember extends voa_c_api_common_abstract {

	protected $_p_sets;
	//不强制登录，允许外部访问
	protected function _before_action( $action ) {
		$this->_require_login = false;
		if( ! parent::_before_action( $action ) ) {
			return false;
		}

		return true;
	}

	public function execute() {

		if( ! $this->__check_params() ) {
			return false;
		}
		$post_data = $this->request->postx();
		//进行用户邀请人员判断
		/*$this->_p_sets = voa_h_cache::get_instance()->get('plugin.invite.setting', 'oa');
		$permit_uid = explode(',', $this->_p_sets['primary_id']);
		if (!in_array($post_data['uid'], $permit_uid)) {
			return $this->_set_errcode( voa_errcode_api_invite::INVITE_USER_NOT_PERMIT );
		}*/
		try {
			// 获取数据
			$out       = array();

			$post_data['approval_state'] = 3;


			$personnel = &uda::factory( 'voa_uda_frontend_cinvite_insert' );
			if(!$personnel->doit( $post_data, $out )) {
				$this->_errcode = $personnel->errcode;
				$this->_errmsg = $personnel->errmsg;
			}

		} catch (Exception $e) {
			logger::error($e);
			return $this->_api_system_message($e);
		}
		// 判断方式

		$this->_result = array(
			'per_id'  => isset( $out['per_id']) ? $out['per_id'] : '',
		);

		// 输出结果
		return true;
	}

	/**
	 * 检查请求参数
	 * @return bool|void
	 */
	private function __check_params() {
		$post_data = $this->request->postx();

		/*名字的检查*/
		if( empty( $this->_params['name'] ) ) {
			return $this->_set_errcode( voa_errcode_api_invite::NAME_NULL );
		}
		if( ! empty( $this->_params['name'] ) && strlen( $this->_params['name'] ) > 18 ) {
			return $this->_set_errcode( voa_errcode_api_invite::NAME_LEN );
		}


		if( ! empty( $post_data['phone'] ) && ! validator::is_mobile( $post_data['phone'] ) ) {
			return $this->_set_errcode( voa_errcode_api_invite::PHONE_ERR );
		}

		if( ! empty( $post_data['email'] ) && ! validator::is_email( $post_data['email'] ) ) {
			return $this->_set_errcode( voa_errcode_api_invite::EMAIL_ERR );
		}

		if( ! empty( $post_data['weixin_id'] ) && ! preg_match( '/^[a-z]{1}[A-Za-z0-9_-]{1,39}$/', $post_data['weixin_id'] ) ) { //匹配微信号
			return $this->_set_errcode( voa_errcode_api_invite::WEI_ERR );
		}

		if(empty($post_data['uid'])){
			return $this->_set_errcode( voa_errcode_api_invite::INVITE_USER_NUKNOW );
		}
		return true;

	}


}
