<?php

class voa_c_cyadmin_enterprise_sms_list extends voa_c_cyadmin_enterprise_base {

	public function execute() {
		//$id = $this->request->get('id');
		$this->view->set( 'controler', $this->controller_name );

		// 获取当前列表的管理员总数，分页，列表
		$condi = array();
		$post  = array();
		if( $this->request->post( 'submit' ) ) {
			$post = $this->request->postx();
		} elseif( $this->request->get( 'submit' ) ) {
			$post = $this->request->getx();
		}
		if( $post ) {
			$profile = $post['form'];
			foreach( $profile as $k => $v ) {
				if( '' != trim( $v ) ) {
					$condi[ $k ] = array( "%$v%", 'like' );
				}
			}
			$this->view->set( 'condi', $post );
		}
		list( $total, $multi, $list ) = $this->_list( $condi );
		$this->view->set( 'list', $list );
		$this->view->set( 'total', $total );
		$this->view->set( 'multi', $multi );

		$this->output( 'cyadmin/sms/list' );
	}

	/**
	 * 企业列表
	 * @return array
	 */
	protected function _list( $condi ) {
		// 每页显示数
		$perpage = 20;

		// 管理员总数
		$total = $this->_serv_sms->count_by_conditions( $condi );
		// 分页显示
		$multi = '';
		// 管理员列表
		$list = array();

		if( ! $total ) {
			// 如果无数据
			return array( $total, $multi, $list );
		}

		// 分页配置
		$pager_options = array(
			'total_items'      => $total,
			'per_page'         => $perpage,
			'current_page'     => $this->request->get( 'page' ),
			'show_total_items' => true,
		);
		$multi         = pager::make_links( $pager_options );
		// 引用结果，分页配置
		pager::resolve_options( $pager_options );


		/**
		 * 根据条件计算总数
		 *
		 * @param  array $conditions
		 *  $conditions = array(
		 *      'field1' => '查询条件', // 运算符为 =
		 *      'field2' => array('查询条件', '查询运算符'),
		 *      'field3' => array(array('查询条件1', '查询条件2', ...), '查询运算符'),
		 *      ...
		 *  );
		 *
		 * @return number
		 */

		// 管理员列表
		$list = $this->_serv_sms->fetch_by_conditions( $condi, $pager_options['start'], $pager_options['per_page'] );


		// 格式化列表输出
		foreach( $list as &$_ca ) {
			$_ca['sms_created'] = rgmdate( $_ca['sms_created'], 'Y-m-d H:i' );
		}
		unset( $_ca );

		return array( $total, $multi, $list );
	}


}
