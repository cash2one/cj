<?php

/**
 * voa_c_api_invite_list
 * 获取被邀请人列表
 * author ppker
 * date 07-08
 */
class voa_c_api_invite_get_list extends voa_c_api_invite_abstract {

	public function execute() {
		// 需要的参数
		$fields = array(
			// 当前页码
			'page'           => array( 'type' => 'int', 'required' => false ),
			// 每页显示数据数
			'limit'          => array( 'type' => 'int', 'required' => false ),
			// 核审参数
			'approval_state' => array( 'type' => 'int', 'required' => false )

		);
		if( ! $this->_check_params( $fields ) ) {
			// 检查参数
			return false;
		}

		if( ! $this->_params['page'] ) {
			$this->_params['page'] = 1;
		}
		if(!$this->_params['limit'] || 20 > $this->_params['limit']) {
			$this->_params['limit'] = 20;
		}
		//当前用户
		$m_uid = startup_env::get( 'wbs_uid' );
		// var_dump($m_uid);die;
		// 获取分页参数
		list( $this->_start, $this->_params['limit'] ) = voa_h_func::get_limit( $this->_params['page'], $this->_params['limit'] );
		$page_options = array(
			$this->_start,
			$this->_params['limit']
		);

		// 条件判断
		if( $this->_params['approval_state'] == 3 ) {
			$conds = array(
				'invite_uid'     => $m_uid,
				'approval_state' => 3
			);
		} else {
			$conds = array(
				'invite_uid'         => $m_uid,
				'approval_state < ?' => 3
			);
		}

		$order = array(
			'per_id' => 'DESC'
		);

		try {
			// 获取数据
			$result = array();
			$uda    = &uda::factory( 'voa_uda_frontend_invite_get' );
			$uda->get_personnel( $conds, $page_options, $order, $result );


		} catch( help_exception $h ) {
			$this->_errcode = $h->getCode();
			$this->_errmsg  = $h->getMessage();
		} catch( Exception $e ) {
			logger::error( $e );

			return $this->_api_system_message( $e );
		}

		// 输出结果
		$this->_result = array(
			'page'  => $this->_params['page'],
			'limit' => $this->_params['limit'],
			'list'  => $result
		);

		return true;
	}

}

