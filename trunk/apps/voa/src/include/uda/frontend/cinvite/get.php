<?php

/**
 * voa_uda_frontend_cinvite_get
 * 邀请人员/uda/获取数据
 * Created by zhoutao.
 * Created Time: 2015/7/8  17:17
 * modifyed by ppker on 2015/7/15
 */
class voa_uda_frontend_cinvite_get extends voa_uda_frontend_cinvite_base {

	protected $color_array = array(
		0 => 'primary', // 审批中 颜色蓝色
		1 => 'green', // 已审批 颜色绿色
		2 => 'danger', // 未通过 建议红色，目前CSS danger 不识别，可能CSS文件版本低
		3 => 'primary' // 无需核审 颜色随意
	);

	protected $color_gz = array( // 关注状态颜色
		1 => 'primary', // 已关注 颜色蓝色
		2 => 'white', // 已冻结 颜色灰色
		4 => 'green' // 未关注 绿色
	);

	protected $sex_array = array(
		0 => '未知',
		1 => '男',
		2 => '女'
	);

	protected $look_array = array( // 关注状态
		1 => '已关注',
		2 => '已冻结',
		4 => '未关注'
	);

	protected $member = null;

	/*可写可不写*/
	public function __construct() {
		parent::__construct();
		if( $this->member === null ) {
			$this->member = new voa_s_oa_member();
		}

	}


	/**
	 * [one_info 获取单条记录]
	 *
	 * @param  [type] $per_id  [description]
	 * @param  [type] &$result [description]
	 *
	 * @return [type]          [description]
	 */
	public function one_info( $per_id, &$result ) {
		$result = $this->_serv_personnel->get( intval( $per_id ) );

		return true;
	}


	/**
	 * 获取personnel表数据
	 * @return bool
	 */
	public function get_personnel( $conds, $page_option, $orderby, &$result ) {

		$result = $this->_serv_personnel->list_by_conds( $conds, $page_option, $orderby );
		if( ! $result ) {
			$result = array();
		}
		//去掉键值,前端排序就好了。
		$result = array_values( $result );

		$this->format_list( $result );

		return true;
	}

	/**
	 * [format_time description]
	 * @return [type] [description]
	 * ppker
	 */
	public function format_list( &$data ) {
		// 关注状态的获取
		$emails    = $this->filter_null( array_column( $data, 'email' ) );
		$weixin_id = $this->filter_null( array_column( $data, 'weixin_id' ) );
		$phone     = $this->filter_null( array_column( $data, 'phone' ) );
		// 组合条件数组
		$conds_arr = array(
			'm_email'       => array(
				'm_email' => array(
					$emails,
					'IN'
				)
			),
			'm_weixin'      => array(
				'm_weixin' => array(
					$weixin_id,
					'IN'
				)
			),
			'm_mobilephone' => array(
				'm_mobilephone' => array(
					$phone,
					'IN'
				)
			)
		);

		// 查询并合并数据结果集
		$he_array = array();
		$he_order = array( 'm_uid' => 'ASC' );
		foreach( $conds_arr as $key => $val ) {
			// 此处还得排除条件数据为空的情况
			if( $val[ $key ][0] ) {
				$he_array[] = $this->member->fetch_all_by_conditions( $val, $he_order );
			}
		}
		// 过滤掉重复的值
		$end_array = array(); // 最终的关注 数据
		foreach( $he_array as $k1 => $v1 ) {
			foreach( $v1 as $k2 => $v2 ) {
				$end_array[ $k2 ] = $v2;
			}
		}

		// 取出关注字段信息 初始化索引
		foreach( $data as $k => $val ) {
			$data[ $k ]['updated']        = rgmdate( $val['updated'], 'Y-m-d H:i' );
			$data[ $k ]['color']          = $this->color_array[ $val['approval_state'] ]; // 颜色要在前
			$data[ $k ]['approval_state'] = $this->_status[ $val['approval_state'] ];

			if( $val['approval_state'] == 3 ) {
				//关注状态的获取
				foreach( $end_array as $kk => $vv ) {
					if( $val['email'] == $vv['m_email'] ) {
						$data[ $k ]['m_qywxstatus'] = $vv['m_qywxstatus'];
						$data[ $k ]['gz_state']     = $this->look_array[ $vv['m_qywxstatus'] ];
						$data[ $k ]['color_gz']     = $this->color_gz[ $vv['m_qywxstatus'] ]; //关注颜色
					} elseif( $val['weixin_id'] == $vv['m_weixin'] ) {
						$data[ $k ]['m_qywxstatus'] = $vv['m_qywxstatus'];
						$data[ $k ]['gz_state']     = $this->look_array[ $vv['m_qywxstatus'] ];
						$data[ $k ]['color_gz']     = $this->color_gz[ $vv['m_qywxstatus'] ]; //关注颜色
					} elseif( $val['phone'] == $vv['m_mobilephone'] ) {
						$data[ $k ]['m_qywxstatus'] = $vv['m_qywxstatus'];
						$data[ $k ]['gz_state']     = $this->look_array[ $vv['m_qywxstatus'] ];
						$data[ $k ]['color_gz']     = $this->color_gz[ $vv['m_qywxstatus'] ]; //关注颜色
					}
				}
			}


		}

	}

	/**
	 * 过滤掉空数据
	 *
	 * @param  [type] &$data [description]
	 *
	 * @return [type]        [description]
	 */
	public function filter_null( $data ) {
		foreach( $data as $k => $v ) {
			if( ! $v ) {
				unset( $data[ $k ] );
			}
		}

		return $data;
	}


	/**
	 * 获取被邀请人详情
	 *
	 * @param  [type] $conds [description]
	 * @param  [type] &$data [description]
	 *
	 * @return [type]        [description]
	 */
	public function get_view( $conds, &$data ) {
		$data = $this->_serv_personnel->get( $conds );
		if( ! $data ) {
			$data = array();
		}
		$this->view_format( $data );

		// var_dump($data);die;
		return;
	}

	/**
	 * 关注状态
	 */

	public function view_format( &$data ) {

		// 获取关注状态
		$gz_data = array();
		if( $data['approval_state'] == 1 || $data['approval_state'] == 3 ) {
			if( $data['phone'] ) {
				$gz_data = $this->member->fetch_by_mobilephone( $data['phone'] );
			} elseif( $data['email'] ) {
				$gz_data = $this->member->fetch_by_email( $data['email'] );
			} elseif( $data['weixin_id'] ) {
				$gz_data = $this->member->fetch_by_weixin( $data['weixin_id'] );
			}
			//$member->
		}


		$data['gender']         = $this->sex_array[ $data['gender'] ]; // 性别
		$data['updated']        = rgmdate( $data['updated'], 'Y-m-d H:i' ); // 申请时间
		$data['approval_state'] = $this->_status[ $data['approval_state'] ]; // 审批状态
		if( $gz_data ) {
			$data['gz_state'] = $this->look_array[ $gz_data['m_qywxstatus'] ];
		} else {
			$data['gz_state'] = '';
		}
		// 自定义字段
		if( null != $data['custom'] ) {
			$data['custom'] = unserialize( $data['custom'] );
		}
	}

}
