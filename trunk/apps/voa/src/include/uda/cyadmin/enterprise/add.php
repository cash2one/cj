<?php

/**
 * voa_uda_frontend_activity_add
 * Created Time: 2015/5/14  0:20
 */
class voa_uda_cyadmin_enterprise_add extends voa_uda_cyadmin_base {

	private $__request = array();

	/**
	 * 入库操作
	 *
	 * @param        $in
	 * @param        $out
	 * @param object $session
	 *
	 * @return bool
	 */
	public function add( $in, &$out, $session ) {

		// 提交的值进行过滤
		$data = array();
		if( ! $this->getact( $in, $data ) ) {
			return array(
				'errcode' => $this->errcode,
				'errmsg'  => $this->errmsg,
			);
		}

		// 入account库
		$serv = &service::factory( 'voa_s_cyadmin_enterprise_account' );

		$data = $serv->insert( $data );

		if( ! $data ) {
			return false;
		}

		return true;
	}


	/**
	 * 处理提交的数据
	 *
	 * @param $in
	 * @param $out
	 *
	 * @return bool
	 */
	public function getact( $in, &$out ) {
		//获取数据
		if( ! empty( $in ) ) {
			$data['ca_id']  = $in['ca_id'];
			$data['id_number']  = $in['id_number'];
			$data['link_name']  = $in['link_name'];
			$data['link_phone'] = $in['link_phone'];
			$data['email']      = $in['email'];
			$data['area']       = $in['area'];
			$data['co_name']    = $in['co_name'];
			$data['co_address'] = $in['co_address'];
			$data['intro']      = $in['intro'];
			$data['post_ip']    = $in['post_ip'];
		} else {
			$this->errmsg( '10007', '内容不能为空' );

			return false;
		}

		$fields = array(
			'ca_id'  => array( 'ca_id', parent::VAR_STR, null, null, false ),
			'id_number'  => array( 'id_number', parent::VAR_STR, null, null, false ),
			'link_name'  => array( 'link_name', parent::VAR_STR, null, null, false ),
			'link_phone' => array( 'link_phone', parent::VAR_STR, null, null, false ),
			'email'      => array( 'email', parent::VAR_STR, null, null, false ),
			'area'       => array( 'area', parent::VAR_STR, null, null, false ),
			'co_name'    => array( 'co_name', parent::VAR_STR, null, null, false ),
			'co_address' => array( 'co_address', parent::VAR_STR, null, null, false ),
			'intro'      => array( 'intro', parent::VAR_STR, null, null, false ),
			'post_ip'    => array( 'post_ip', parent::VAR_STR, null, null, false ),
		);

		// 检查过滤，参数
		if( ! $this->extract_field( $this->__request, $fields, $data ) ) {
			return false;
		}

		$serv  = &service::factory( 'voa_s_cyadmin_enterprise_account' );
		$count = $serv->count_by_conds( array( 'id_number' => $this->__request['id_number'] ) );
		if( $count > 0 ) {
			$this->errmsg( '20009', '代理编号已存在' );

			return false;
		}

		if( empty( $this->__request['ca_id'] ) ) {
			$this->errmsg( '20000', '跟进销售不能为空' );

			return false;
		}
		if( empty( $this->__request['id_number'] ) ) {
			$this->errmsg( '20001', '代理编号不能为空' );

			return false;
		}
		if( empty( $this->__request['link_name'] ) ) {
			$this->errmsg( '20002', '联系人姓名不能为空' );

			return false;
		}
		if( empty( $this->__request['link_phone'] ) ) {
			$this->errmsg( '20003', '联系人手机号不能为空' );

			return false;
		}
		if( empty( $this->__request['email'] ) ) {
			$this->errmsg( '20004', '邮箱不能为空' );

			return false;
		}
		if( empty( $this->__request['area'] ) ) {
			$this->errmsg( '20005', '代理区域不能为空' );

			return false;
		}
		if( empty( $this->__request['co_name'] ) ) {
			$this->errmsg( '20006', '公司名不能为空' );

			return false;
		}
		if( empty( $this->__request['co_address'] ) ) {
			$this->errmsg( '20007', '公司地址不能为空' );

			return false;
		}
		if( empty( $this->__request['intro'] ) ) {
			$this->errmsg( '20008', '公司简介不能为空' );

			return false;
		}

		$out = $this->__request;

		return true;
	}

	/**
	 * 后台活动编辑更新
	 *
	 * @param $in
	 * @param $out
	 * @param object session
	 *
	 * @return bool
	 */
	public function edit( $in, &$out ) {
		$acid = $in['acid'];

		// 处理时间


		$fields = array(
			'province'     => array( 'province', parent::VAR_STR, null, null, false ),
			'city'         => array( 'city', parent::VAR_STR, null, null, false ),
			'county'       => array( 'county', parent::VAR_STR, null, null, false ),
			'co_name'      => array( 'co_name', parent::VAR_STR, null, null, false ),
			'intro'        => array( 'intro', parent::VAR_STR, null, null, false ),
			'link_name'    => array( 'link_name', parent::VAR_STR, null, null, false ),
			'link_phone'   => array( 'link_phone', parent::VAR_STR, null, null, false ),
			'deadline'     => array( 'deadline', parent::VAR_INT, null, null, false ),
			'created_day'  => array( 'created_day', parent::VAR_STR, null, null, false ),
			'created_hour' => array( 'created_hour', parent::VAR_STR, null, null, false ),
		);

		// 检查过滤，参数
		if( ! $this->extract_field( $this->__request, $fields, $in ) ) {
			return false;
		}

		if( ! validator::is_string_count_in_range( $in['co_name'], 1, 15 ) ) {
			$this->errmsg( '10004', '标题字数最高15字，最低1个字' );

			return false;
		}

		$data = array(
			'province'     => $in['province'],
			'city'         => $in['city'],
			'county'       => $in['county'],
			'co_name'      => $in['co_name'],
			'intro'        => $in['intro'],
			'link_name'    => $in['link_name'],
			'link_phone'   => $in['link_phone'],
			'deadline'     => $in['deadline'],
			'created_day'  => $in['created_day'],
			'created_hour' => $in['created_hour'],
		);
		$serv = &service::factory( 'voa_s_cyadmin_enterprise_account' );
		$out  = $serv->update_by_conds( $acid, $data );
		$out  = $data;

		return true;
	}

	public function agant_setting( $in, $out ) {

		// 提交的值进行过滤
		$data = array();
		if( ! $this->filter_agant( $in, $data ) ) {
			return array(
				'errcode' => $this->errcode,
				'errmsg'  => $this->errmsg,
			);
		}

		// 入agant库
		$serv = &service::factory( 'voa_s_cyadmin_enterprise_agant' );

		$data = $serv->insert( $data );

		if( ! $data ) {
			return false;
		}

		// 更新account库
		$serv = &service::factory( 'voa_s_cyadmin_enterprise_account' );

		$update_data = array(
			'pay_status' => $data['pay_status'],
			'deadline'   => $data['deadline'],
			'pay_time'   => $data['pay_time'],
			'ca_id'  => $data['ca_id'],
			'post_ip'    => $in['post_ip']
		);
		$serv->update_by_conds( $in['acid'], $update_data );

		return true;
	}

	/**
	 * 过滤参数
	 *
	 * @param $in
	 * @param $out
	 *
	 * @return bool
	 * @throws help_exception
	 */
	public function filter_agant( $in, &$out ) {
		//获取数据
		if( ! empty( $in ) ) {
			$data['pay_status']  = $in['pay_status'];
			$data['deadline']    = $in['deadline'];
			$data['pay_time']    = empty( $in['pay_time'] ) ? 0 : rstrtotime( $in['pay_time'] );
			$data['ca_id']   = $in['ca_id'];
			$data['salesremark'] = $in['salesremark'];
			$data['acid']        = $in['acid'];
		} else {
			$this->errmsg( '10007', '内容不能为空' );

			return false;
		}

		$fields = array(
			'pay_status'  => array( 'pay_status', parent::VAR_INT, null, null, false ),
			'deadline'    => array( 'deadline', parent::VAR_INT, null, null, false ),
			'pay_time'    => array( 'pay_time', parent::VAR_INT, null, null, false ),
			'ca_id'   => array( 'ca_id', parent::VAR_STR, null, null, false ),
			'salesremark' => array( 'salesremark', parent::VAR_STR, null, null, false ),
			'acid'        => array( 'acid', parent::VAR_INT, null, null, false ),
		);

		// 检查过滤，参数
		if( ! $this->extract_field( $this->__request, $fields, $data ) ) {
			return false;
		}

		if( empty( $this->__request['pay_status'] ) ) {
			$this->errmsg( '30001', '付费状态不能为空' );

			return false;
		}
		if( empty( $this->__request['deadline'] ) ) {
			$this->errmsg( '30002', '代理期限不能为空' );

			return false;
		}
		if( empty( $this->__request['pay_time'] ) && $this->__request['pay_status'] == 2 ) {
			$this->errmsg( '30003', '付费时间不能为空' );

			return false;
		}
		if( empty( $this->__request['ca_id'] ) ) {
			$this->errmsg( '30004', '跟进销售不能为空' );

			return false;
		}
		if( empty( $this->__request['acid'] ) ) {
			$this->errmsg( '30005', '丢失重要参数' );

			return false;
		}

		$out = $this->__request;

		return true;
	}

}
