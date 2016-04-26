<?php

/**
 * 邀请列表
 * $Author$ huangzz
 * $Id$
 */
class voa_uda_frontend_cinvite_list extends voa_uda_frontend_cinvite_base {

	/** service 类 */
	private $__service = null;
	/** member */
	private $__member = null;


	public function __construct() {
		parent::__construct();
		if( $this->__service === null ) {
			$this->__service = new voa_s_oa_cinvite_personnel();
		}
		if( $this->__member === null ) {
			$this->__member = new voa_uda_frontend_member_get();
		}
	}

	/**
	 * 根据条件查找关注状态,用于后台查看
	 *
	 * @param array $res 查询结果
	 * @param array $m_mobilephone 手机号码
	 */
	public function member_invite( &$res, $m_mobilephone ) {
		//member_by_account方法是直接运用member分支中的方法
		$member_msg = $this->__member->member_by_account( $m_mobilephone, $member );
		$res        = isset( $member['m_qywxstatus'] ) ? $member['m_qywxstatus'] : 0;
		$res        = $this->_qywxstatus[ $res ];

		return $res;
	}

	/**
	 * 根据条件查找邀请记录,用于后台查看
	 *
	 * @param array $result 查询结果
	 * @param array $conditions 实际查询条件
	 * @param int|array $page_option 分页参数
	 */
	public function list_invite( &$result, $conds, $page_option ) {
		$result['list']  = $this->_list_invite_by_conds( $conds, $page_option );
		$result['total'] = $this->_count_invite_by_conds( $conds );

		return $result;
	}

	/**
	 * 根据条件计算邀请数据数量
	 *
	 * @param array $conds
	 *
	 * @return number
	 */
	public function _count_invite_by_conds( $conds ) {
		$total = $this->__service->count_by_conds( $conds );

		return $total;
	}

	/**
	 * [list_all 获取所有数据]
	 *
	 * @param  [type] $page_option [description]
	 * @param  array $orderby [description]
	 *
	 * @return [type]              [description]
	 */
	public function list_all( $page_option = null, $orderby = array() ) {
		$request = $this->__service->list_all( $page_option, $orderby );

		return $request;
	}


	/**
	 * 按照条件查询查询
	 *
	 * @param array $conds
	 *
	 * @return array
	 */
	public function _list_invite_by_conds( $conds, $pager ) {
		$list = array();
		$list = $this->__service->list_by_conds( $conds, $pager, array( 'updated' => 'DESC' ) );
		$this->__format_list( $list );

		return $list;
	}

	/**
	 * 格式化数据列表
	 *
	 * @param array $list 列表（引用）
	 */
	public function __format_list( &$list ) {
		if( $list ) {
			//获取列表的文字信息
			$per_ids = array_column( $list, 'per_id' );
			foreach( $list as $k => &$v ) {

				$m_uid = $v['invite_uid'];

				$user       = voa_h_user::get( $m_uid );
				$v['m_uid'] = $m_uid;
				if( isset( $user['m_username'] ) ) {
					$v['invite_uid'] = $user['m_username'];
				} else {
					$v['invite_uid'] = '已删除';
				}
				$v['created']           = rgmdate( $v['created'], 'Y-m-d H:i' );
				$v['approval_state_id'] = $v['approval_state'];
				$v['approval_state']    = $this->_status[ $v['approval_state'] ];
				if( $v['approval_state_id'] == 3 ) {
					$v['is_approval_state'] = $this->is_approval[0];
				} elseif( $v['approval_state_id'] == 0 || $v['approval_state_id'] == 1 || $v['approval_state_id'] == 2 ) {
					$v['is_approval_state'] = $this->is_approval[1];
				}
			}
		}
	}

}
