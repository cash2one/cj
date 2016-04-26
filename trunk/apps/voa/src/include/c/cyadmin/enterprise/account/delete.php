<?php

/**
 * $Author$
 * $Id$
 */
class voa_c_cyadmin_enterprise_account_delete extends voa_c_cyadmin_base {

	public function execute() {
		$ids    = 0;
		$delete = $this->request->post( 'delete' );

		$acid = $this->request->get( 'acid' );

		if( $delete ) {
			$ids = rintval( $delete, true );
		} elseif( $acid ) {
			$ids = rintval( $acid, false );
			if( ! empty( $ids ) ) {
				$ids = array( $ids );
			}
		}

		if( empty( $ids ) ) {
			$this->message( 'error', '请指定要删除的 ' . $this->_module_plugin['cp_name'] . ' 数据' );
		}
		//删除
		$serv         = &service::factory( 'voa_s_cyadmin_enterprise_account' );
		$serv_company = &service::factory( 'voa_s_cyadmin_enterprise_profile' );
		//$conds['ep_agent IN (?)'] = $ids;

		//$com_list = $serv_company->fetch_by_conditions($conds);
		$com_list = $serv_company->fetch_all();
		$com_epid = array();
		foreach( $com_list as $_val ) {
			if( in_array( $_val['ep_agent'], $ids ) ) {
				$com_epid[] = $_val['ep_id'];
			}
		}

		foreach( $com_epid as $_id ) {
			$serv_company->update( array( 'ep_agent' => '0' ), $_id );
		}

		if( $serv->delete( $ids ) ) {

			$this->message( 'success', '指定信息删除完毕', $this->cpurl( $this->_module, $this->_operation, 'list' ) );
		} else {
			$this->message( 'error', '指定信息删除操作失败' );
		}
	}

}
