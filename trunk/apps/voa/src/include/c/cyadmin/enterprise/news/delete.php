<?php

/**
 * $Author$
 * $Id$
 */
class voa_c_cyadmin_enterprise_news_delete extends voa_c_cyadmin_base {

	public function execute() {
		$ids    = 0;
		$delete = $this->request->post( 'delete' );

		$meid = $this->request->get( 'meid' );

		if( $delete ) {
			$ids = rintval( $delete, true );
		} elseif( $meid ) {
			$ids = rintval( $meid, false );
			if( ! empty( $ids ) ) {
				$ids = array( $ids );
			}
		}

		if( empty( $ids ) ) {
			$this->message( 'error', '请指定要删除的 ' . $this->_module_plugin['cp_name'] . ' 数据' );
		}
		//删除
		$serv = &service::factory( 'voa_s_cyadmin_enterprise_message' );
		// $serv_read = &service::factory('voa_s_cyadmin_enterprise_read');
		$conds['meid IN (?)'] = $ids;
		if( $serv->delete( $ids ) ) {
			/*if($serv_read->list_by_conds($conds)){
				$serv_read->delete_by_conds($conds);
			}*/

			$this->message( 'success', '指定信息删除完毕', $this->cpurl( $this->_module, $this->_operation, 'list' ) );
		} else {
			$this->message( 'error', '指定信息删除操作失败' );
		}
	}

}
