<?php

class voa_c_cyadmin_enterprise_company_message extends voa_c_cyadmin_enterprise_base {

	public function execute() {
		$post = $this->request->postx();

		if( ! empty( $post ) ) {
			//数据验证
			if( $this->_formdata( $post ) ) {

				$data['epid']    = $post['ep_id'];
				$data['content'] = $post['content'];
				$data['title']   = $post['title'];
				$data['type']    = 1;
				$serv            = &service::factory( 'voa_s_cyadmin_enterprise_message' );
				$result          = $serv->insert( $data );
				if( $result ) {
					$this->message( 'success', '发送消息成功', get_referer( $this->cpurl( $this->_module, $this->_operation, 'list' ) ), false );
				}
			}
		}
	}

	public function _formdata( $in ) {
		if( empty( $in['title'] ) ) {
			$this->message( 'error', '请输入标题' );

			return false;
		}
		if( empty( $in['content'] ) ) {
			$this->message( 'error', '请输入内容' );

			return false;
		}

		return true;
	}

}
