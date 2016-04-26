<?php

/**
 * view.php
 * 帐号详情查看
 * $Author$
 * $Id$
 */
class voa_c_cyadmin_enterprise_news_view extends voa_c_cyadmin_enterprise_base {

	public function execute() {
		//获取数据
		$meid = $this->request->get( 'meid' );
		$uda  = &uda::factory( 'voa_uda_cyadmin_enterprise_news' );
		$info = array();
		$uda->getview( $meid, $info );
		if( $uda->errmsg ) {
			$this->message( 'error', $uda->errmsg );
		}
		$list    = array();
		$data[0] = $info;
		$uda->format( $data, $list );
		$list = $list[0];
		//展示数据
		$this->view->set( 'list', $list );
		$this->output( 'cyadmin/enterprise/news/view' );
	}

}
