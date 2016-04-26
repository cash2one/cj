<?php

class voa_c_cyadmin_enterprise_company_edit extends voa_c_cyadmin_enterprise_base {

	public function execute() {
		$id = $this->request->get( 'id' );

		$formedit = $this->request->post( 'formedit' );

		$this->view->set( 'controler', $this->controller_name );
		$act = $this->request->post( 'act' );
		if( in_array( $act, array( 'profilesave', 'appsave' ) ) ) {


			$value = $this->request->post( 'update_value' );

			$field = $this->request->post( 'field' );
			if( $act == 'profilesave' ) {
				$this->_save_profile( array( $field => $value ), $id );
			} elseif( $act == 'appsave' ) {
				$ea_id = $this->request->post( 'ea_id' );
				$this->_save_app( array( $field => $value ), $ea_id, $id );
			}

			// 用于返回前台结果
			echo $this->request->post( 'update_value' );
			exit;
		}
		if( ! empty( $formedit ) ) {
			$post = $this->request->postx();
			//过滤数据
			$data = array();
			$this->form( $post, $data );
			if( $data ) {
				$this->message( 'success', '修改成功', get_referer( $this->cpurl( $this->_module, $this->_operation, 'list' ) ), false );
			}

		}

		// 获取当前列表的管理员总数，分页，列表
		list( $total, $multi, $list ) = $this->_enterpriseapp_list( $id );
		$this->view->set( 'list', $list );
		$this->view->set( 'total', $total );
		$this->view->set( 'multi', $multi );
		$serv         = &service::factory( 'voa_s_cyadmin_enterprise_account' );
		$ac_list      = $serv->list_all();
		$ac_key       = array();
		$ac_value     = array();
		$account_list = array();
		foreach( $ac_list as $_val ) {
			$ac_key[]   = $_val['acid'];
			$ac_value[] = $_val['co_name'];
		}
		$account_list = array_combine( $ac_key, $ac_value );
		// 初始化编辑器
		$ueditor     = new ueditor();
		$content_key = 'content';
		// 编辑器资源路径
		$ueditor->ueditor_home_url = '/static/ueditor/';
		// 处理上传文件路径
		$ueditor->server_url = '/ueditor/';

		$ueditor->ueditor_config = array(
			'toolbars'           => '_cyadmin',
			'textarea'           => $content_key,
			'initialFrameHeight' => 150,
			'initialContent'     => '',
			'elementPathEnabled' => false
		);
		if( ! $ueditor->create_editor( 'content', '' ) ) {
			$ueditor_output = $ueditor->ueditor_error;
		} else {
			$ueditor_output = $ueditor->ueditor_html;
		}

		$this->view->set( 'ueditor_output', $ueditor_output );
		// 编辑基础链接
		$this->view->set( 'edit_url_base', $this->cpurl( $this->_module, $this->_operation, 'edit', array( 'id' => $id ) ) );
		$this->view->set( 'message_url_base', $this->cpurl( $this->_module, $this->_operation, 'message' ) );
		$data = array();
		$this->formdata( $this->_profile_get( $id ), $data );
		$date = config::get( 'voa.company.data' );

		$this->view->set( 'date', $date );
		$this->view->set( 'profile', $data );
		$this->view->set( 'account_list', $account_list );
		$this->output( 'cyadmin/company/edit' );
	}

	public function form( $in, &$out ) {

		//过滤数据
		$data                 = array();
		$data['ep_money']     = $in['ep_money'];
		$data['ep_deadline']  = $in['ep_deadline'];
		$data['ep_space']     = $in['ep_space'];
		$data['ep_start']     = strtotime( $in['ep_start'] );
		$data['ep_end']       = strtotime( $in['ep_end'] );
		$data['ep_paystatus'] = $in['ep_paystatus'];
		//查原字段值

		$serv     = &service::factory( 'voa_s_cyadmin_enterprise_profile' );
		$serv_log = &service::factory( 'voa_s_cyadmin_enterprise_alog' );
		$loglist  = $serv->fetch( $in['ep_id'] );
		$newfield = array();
		foreach( $data as $key => $val ) {

			if( $data[ $key ] != $loglist[ $key ] ) {
				$newfield[ $key ] = $val;
			}
		}

		//记录操作
		$da['record'] = serialize( $newfield );
		$user         = $this->_user;
		$da['uid']    = $user['ca_id'];
		$da['epid']   = $in['ep_id'];
		if( ! empty( $newfield ) ) {
			$serv_log->insert( $da );
		}
		$serv->update( $data, $in['ep_id'] );
		$out = $data;

		return true;
	}

	public function formdata( $in, &$out ) {

		$in['_ep_start'] = date( 'Y-m-d', $in['ep_start'] );
		$in['_ep_end']   = date( 'Y-m-d', $in['ep_end'] );
		$out             = $in;
	}

}
