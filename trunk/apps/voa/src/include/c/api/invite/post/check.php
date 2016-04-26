<?php

/**
 * voa_c_api_invite_post_check
 * 邀请审核处理
 * @Author: ppker
 * @Date:   2015-07-09 17:43:49
 * @Last Modified by:   ChangYi
 * @Last Modified time: 2015-07-21 12:40:49
 */
class voa_c_api_invite_post_check extends voa_c_api_invite_abstract {

	public function execute() {
		// 请求的参数
		$fields = array(
			'per_id'         => array( 'type' => 'int', 'required' => true ),
			'approval_state' => array( 'type' => 'int', 'required' => true )
		);
		if( ! $this->_check_params( $fields ) ) {
			return false;
		}

		try {
			// 获取数据
			$result     = array();
			$result_mem = array();
			$sel_data   = $this->request->postx();
			// 获取被邀请人信息
			$info = &uda::factory( 'voa_uda_frontend_invite_get' );
			// 进行邀请人身份合法检查
			$real_uid = startup_env::get( "wbs_uid" );
			$info->one_info( $this->_params['per_id'], $result );

			if( $real_uid != $result['invite_uid'] ) {
				return $this->_set_errcode( voa_errcode_api_invite::ERR_UID );
			}
			// 自定义字段设置信息
			$member_setting = voa_h_cache::get_instance()->get( 'plugin.member.setting', 'oa' );


			// 手机短信 uda
			// $sms = &uda::factory("voa_uda_uc_smscode_insert");
			$uda = &uda::factory( 'voa_uda_frontend_invite_updata' );
			if( $sel_data['ext_select'] ) {
				$person_info = array();
				if( $result ) {
					$person_info['m_weixin']      = $result['weixin_id'];
					$person_info['m_username']    = $result['name'];
					$person_info['m_email']       = $result['email'];
					$person_info['m_mobilephone'] = $result['phone'];
					// $person_info['cj_id'] = $result['position'];  cj_name
					$person_info['cj_name']  = $result['position'];
					$person_info['m_gender'] = $result['gender'];
					$cd_ids                  = explode( ',', $sel_data['cdid'] );
					if( ! is_array( $cd_ids ) ) {
						$cd_ids = array( $cd_ids );
					}
					$person_info['cd_id'] = $cd_ids;
					// 自定义字段
					if( $result['custom'] ) {
						$custom = unserialize( $result['custom'] );

						//var_dump($custom);die;

						$jc_data = $member_setting['fields'];
						foreach( $custom as $k => $val ) {
							foreach( $jc_data as $k1 => $val1 ) {
								if( $val1['status'] == 2 && $k == $k1 ) {
									$person_info[ 'mf_' . $k ] = $val[0];
								} elseif( $val1['status'] == 1 && $k == $k1 ) {
									$person_info[ 'mf_ext' . $k ] = $val[0];
								}
							}
						}
					}

					//var_dump($person_info);die;
					//开始整理流程;			
					if( ! $uda->update_check( $this->_params, $result, $person_info ) ) {
						$this->_errcode = $uda->errcode;
						$this->_errmsg  = $uda->errmsg;

						return false;
					}
				}

			} else {
				$re = $uda->update_check( $this->_params, $result, array(), $result['phone'] );
				if( ! $re ) {
					$this->_errcode = $uda->errcode;
					$this->_errmsg  = $uda->errmsg;

					return false;
				} else {

				}
			}
		} catch( help_exception $h ) {
			$this->_errcode = $h->getCode();
			$this->_errmsg  = $h->getMessage();
		} catch( Exception $e ) {
			logger::error( $e );
			$this->_errcode = 10101;
			$this->_errmsg  = '未知错误';
		}

		// 输出结果
		$this->_result = array(
			'per_id'  => $this->_params['per_id'],
			'update'  => $result,
			'message' => "操作成功!"
		);

		return true;
	}
}
