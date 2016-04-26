<?php

/**
 * voa_uda_frontend_invite_insert
 * 邀请人员/uda/入库数据
 * Created by ppker
 * Created Time: 2015/7/8  17:23
 */
class voa_uda_frontend_invite_insert extends voa_uda_frontend_invite_base {

	// service 服务类
	private $__service = null;

	public function __construct() {
		parent::__construct();
		if( $this->__service == null ) {
			$this->__service = new voa_s_oa_invite_personnel();
		}
	}

	/**
	 * [do description]
	 *
	 * @param  [type] $in   [description]
	 * @param  [type] &$out [description]
	 *
	 * @return [type]       [description]
	 */
	public function doit( $in, &$out ) {
		$data = array();
		// API处 已经进行合法验证，此处不用再次验证

		if( $in['name'] ) {
			$data['name'] = $in['name'];
		}
		if( $in['gender'] ) {
			$data['gender'] = $in['gender'];
		}
		if( $in['weixin_id'] ) {
			$data['weixin_id'] = $in['weixin_id'];
		}
		if( $in['phone'] ) {
			$data['phone'] = $in['phone'];
		}
		if( $in['email'] ) {
			$data['email'] = $in['email'];
		}
		if( $in['invite_uid'] ) {
			$data['invite_uid'] = $in['invite_uid'];
		}
		if( $in['position'] ) {
			$data['position'] = $in['position'];
		}
		if( $in['approval_state'] ) {
			$data['approval_state'] = $in['approval_state'];
		}
		if( $in['custom'] ) {
			$data['custom'] = serialize( $in['custom'] );
		}

		// 更新4表 格式化数据
		$four_data = array();
		// 自定义字段设置信息
		$member_setting = voa_h_cache::get_instance()->get( 'plugin.member.setting', 'oa' );
		// 后台设置的默认部门（可多）
		$invite_setting = voa_h_cache::get_instance()->get( 'plugin.invite.setting', 'oa' );
		$cd_ids         = explode( ',', $invite_setting['cd_id'] );
		if( ! is_array( $cd_ids ) ) {
			$cd_ids = array( $cd_ids );
		}
		// member模块
		$mem_update = &uda::factory( 'voa_uda_frontend_member_update' );
		if( $in['weixin_id'] ) {
			$four_data['m_weixin'] = $in['weixin_id'];
		}
		if( $in['name'] ) {
			$four_data['m_username'] = $in['name'];
		}
		if( $in['email'] ) {
			$four_data['m_email'] = $in['email'];
		}
		if( $in['phone'] ) {
			$four_data['m_mobilephone'] = $in['phone'];
		}
		if( $in['position'] ) {
			$four_data['cj_name'] = $in['position'];
		}
		if( $in['gender'] ) {
			$four_data['m_gender'] = $in['gender'];
		}
		if( $cd_ids ) {
			$four_data['cd_id'] = $cd_ids;
		} // 注意部门
		if( $in['custom'] ) {
			$ext_custom = $in['custom'];
			$ext_data   = $member_setting['fields']; //后台扩展字段
			foreach( $ext_custom as $k => $val ) {
				foreach( $ext_data as $k1 => $val1 ) {
					if( $val1['status'] == 2 && $k == $k1 ) {
						$four_data[ 'mf_' . $k ] = $val[0];
					} elseif( $val1['status'] == 1 && $k == $k1 ) {
						$four_data[ 'mf_ext' . $k ] = $val[0];
					}
				}
			}
		}
		// 以上结束

		//再次处理呀
		$result_mem = array();
		try {
			$this->_serv_personnel->begin();
			$out = $this->_serv_personnel->insert( $data );


			if( $in['approval_state'] == 3 ) { //无需审核，直接邀请 更新到member 4表
				$re = $mem_update->update( $four_data, $result_mem );
				//$re = $mem_update->update($four_data, $result_mem, array(), false); // 更新操作
				if( ! $re ) {
					return voa_h_func::throw_errmsg( $mem_update->errcode . ":" . $mem_update->errmsg );
				}
			}
			$this->_serv_personnel->commit();
		} catch( Exception $e ) {
			$this->_serv_personnel->rollback();
			logger::error( $e );

			//throw new service_exception($e->getMessage(), $e->getCode());
			return $this->set_errmsg( voa_errcode_api_invite::UNKNOW );
		}

		return true;
	}


}
