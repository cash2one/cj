<?php

/**
 * voa_uda_frontend_invite_updata
 * 邀请人员/uda/更新数据
 * Created by zhoutao.
 * Created Time: 2015/7/8  17:23
 * @Last Modified by:   ChangYi
 * @Last Modified time: 2015-07-23 20:25:49
 */
class voa_uda_frontend_invite_updata extends voa_uda_frontend_invite_base {
	/**
	 * 方便扩展,保留之
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * [update_check 更新审核状态]
	 *
	 * @param  [type] $conds   [description]
	 * @param  [type] &$result [description]
	 * @param  [type] $person_info [下一步要更新的数据，4个表的数据]
	 *
	 * @return [type]          [description]
	 */
	public function update_check( $conds, &$result, $person_info = array(), $phone = '' ) {
		$result_mem = array();
		// 企业号
		$re    = voa_h_cache::get_instance()->get( 'setting', 'oa' );
		$ep_id = $re['ep_id'] ? $re['ep_id'] : '';

		$setting = voa_h_cache::get_instance()->get('setting', 'oa');
		$mem_update = &uda::factory( 'voa_uda_frontend_member_update' );
		$sms        = &uda::factory( "voa_uda_uc_smscode_insert" ); // 手机短信uda
		try {
			$this->_serv_personnel->begin();

			$per_id                 = intval( $conds['per_id'] );
			$data['approval_state'] = intval( $conds['approval_state'] );
			$result                 = $this->_serv_personnel->update( $per_id, $data );

			if( ! empty( $person_info ) ) {
				$re = $mem_update->update( $person_info, $result_mem );
				// $re = $mem_update->update($person_info, $result_mem, array(), false);
				if( ! $re ) {
					return voa_h_func::throw_errmsg( $mem_update->errcode . ":" . $mem_update->errmsg );
				}

				if( $mem_update->errcode == 0 ) {

					//发送邀请关注邮件
					if (!empty($result_mem['m_email'])) {

						$uda_mailcloud = &uda::factory('voa_uda_uc_mailcloud_insert');
						$subject = $setting['sitename'] . config::get('voa.mailcloud.subject_for_follow');
						$scheme = config::get('voa.oa_http_scheme');

						$vars = array(
							'%sitename%' => array($setting['sitename']),
							'%qrcode_url%' => array('<img src="' . $setting['qrcode'] . '" />'),
							'%pc_url%' => array($scheme . $setting['domain'] . '/pc'),
							'%download_url%' => array('<a href="' . $scheme . $setting['domain'] . '/frontend/index/download">点击下载</a>')
						);

						$uda_mailcloud->send_invite_follow_mail(array($result_mem['m_email']), $subject, $vars);
					} else {
						//通过微信发送邀请关注邮件
						$invite_result = array();
						$qywx_ab = voa_wxqy_addressbook::instance();
						$qywx_ab->user_invite($result_mem['m_openid'], '', $invite_result);
					}

				} else {
					return voa_h_func::throw_errmsg( $mem_update->errcode . ":" . $mem_update->errmsg );
				}
			} else {
				$message = "很抱歉，您申请加入" . $setting['sitename'] . "企业号未通过。";
				$sms->send( $phone, $message );

			}
			$this->_serv_personnel->commit();
		} catch( help_exception $h ) {

			$this->_serv_personnel->rollBack();

			return $this->errmsg( $h->getCode(), $h->getMessage() );

		} catch( Exception $e ) {
			$this->_serv_personnel->rollBack();
			logger::error( $e );

			return $this->set_errmsg( voa_errcode_api_invite::UNKNOW );
		}

		return true;
	}

	/**
	 * 整理更新邀请设置数据
	 *
	 * @param $data
	 * @param $out
	 *
	 * @return bool
	 */
	public function invite_setting_data( $postx, &$out ) {
		$m_uid = implode( ',', $postx['m_uid'] );
		if( ! empty( $postx['cd_id'] ) ) {
			$cd_id = implode( ',', $postx['cd_id'] );
		} else {
			$cd_id = '';
		}

		// 如果页面没有提交'是否需要审批'的值，那就是不需要审批
		if( ! isset( $postx['is_approval'] ) || $postx['is_approval'] == '' ) {
			$postx['is_approval'] = 0;
		};
		$out = array(
			'primary_id'      => (string) $m_uid, // 可邀请的人员的主键
			'short_paragraph' => (string) $postx['short_paragraph'], // 邀请语
			'is_approval'     => (string) $postx['is_approval'], // 是否需要审批
			'cd_id'           => (string) $cd_id, // 默认审批同意后进入的部门
			'custom'          => empty($postx['custom']) ? '' : serialize( $postx['custom'] ), // 填写的自定义字段设置
			'overdue'         => (string) $postx['overdue'] // 过期时间（秒）
		);

		return true;
	}

	/**
	 * 更新邀请设置
	 *
	 * @param $data
	 * @param $out
	 *
	 * @return bool
	 */
	public function update_invite( $data, $out ) {

		foreach( $data as $k => $v ) {
			$this->_serv_setting->update_by_conds( array( 'key' => $k ), array( 'value' => $v ) );
		}

		return true;
	}

}
