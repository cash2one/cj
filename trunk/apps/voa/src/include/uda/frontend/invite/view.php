<?php

/**
 * voa_uda_frontend_news_view
 * 统一数据访问/新闻公告/获取单个新闻公告
 *
 * $Author$
 * $Id$
 */
class voa_uda_frontend_invite_view extends voa_uda_frontend_invite_base {
	/** service 类 */
	private $__service = null;
	/** member */
	private $__member = null;

	public function __construct() {
		parent::__construct();
		if( $this->__service == null ) {
			$this->__service = new voa_s_oa_invite_personnel();
		}
		if( $this->__member === null ) {
			$this->__member = new voa_uda_frontend_member_get();
		}
	}

	/**
	 * 获取单个邀请信息
	 *
	 * @param array $request 请求的参数
	 * @param array $result (引用结果)信息数组
	 *
	 * @return boolean
	 */
	public function get_view( array $request, array &$result ) {
		// 取得参数
		$per_id = $request['per_id'];
		// 获取邀请信息
		$msg                   = $this->__service->get( $per_id );
		$m_uid                 = $msg['invite_uid'];
		$user                  = voa_h_user::get( $m_uid );
		$msg['invite_uid']     = $user['m_username'];
		$msg['created']        = rgmdate( $msg['created'], 'Y-m-d H:i' );
		$msg['approval_state'] = $this->_status[ $msg['approval_state'] ];
		$m_mobilephone         = $msg['phone'];
		$res                   = array();
		$this->__member->member_by_account( $m_mobilephone, $member );
		$res                 = isset( $member['m_qywxstatus'] ) ? $member['m_qywxstatus'] : 0;
		$msg['m_qywxstatus'] = $this->_qywxstatus[ $res ];
		$msg['gender']       = $this->_gender[ $msg['gender'] ];
		$result              = $msg;

		return true;
	}

}
