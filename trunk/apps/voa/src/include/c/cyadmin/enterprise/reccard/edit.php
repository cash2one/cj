<?php

class voa_c_cyadmin_enterprise_reccard_edit extends voa_c_cyadmin_enterprise_base {

	public function execute() {
		$ajax = $this->request->get( 'ajax' );
		if( $ajax ) {
			$results                = array(
				'status'       => 'ok',
				'msg'          => '',
				'lists'        => array(),
				'all_total'    => 0,
				'append_total' => 0,
				'last_end_id'  => 0
			);
			$act                    = $this->request->post( 'act' );
			$results['last_end_id'] = $this->_get_reccard_end_id();
			$results['lastdate']    = rgmdate( startup_env::get( 'timestamp' ), 'm-d H:i' );
			if( $act == 'get_lists' ) {
				$start                   = $this->request->post( 'start' );
				$last_end_id             = $this->request->post( 'last_end_id' );
				$results['lists']        = $this->_get_reccard_lists( $start );
				$results['all_total']    = $this->_get_reccard_total_all();
				$results['append_total'] = $this->_get_reccard_total_append( $last_end_id );

			} elseif( $act == 'get_all_total' ) {
				$results['all_total']    = $this->_get_reccard_total_all();
				$results['append_total'] = $this->_get_reccard_total_append( $last_end_id );
			} elseif( $act == 'pull_back' ) {
				$rnc_id      = $this->request->post( 'rnc_id' );
				$reason_type = $this->request->post( 'reason_type' );
				$this->_pull_reccard_back( $rnc_id, $reason_type );
			} elseif( $act == 'save' ) {
				$rnc_id            = $this->request->post( 'rnc_id' );
				$data              = $this->request->post( 'info' );
				$ret               = $this->_save_namecard( $rnc_id, $data );
				$results['status'] = $ret['status'];
				$results['msg']    = $ret['status'];
			}
			echo json_encode( $results );
			exit;
		}
		$this->view->set( 'controler', $this->controller_name );
		$this->view->set( 'request_limit', voa_d_cyadmin_recognition_namecard::REQUEST_LIMIT );

		$this->view->set( 'ajax_edit_url_base', $this->cpurl( $this->_module, $this->_operation, 'edit', array( 'ajax' => 1 ) ) );

		$this->output( 'cyadmin/reccard/edit' );
	}


}
