<?php

/**
 * list.php
 * 消息列表
 * $Author$
 * $Id$
 */
class voa_c_admincp_system_message_view extends voa_c_admincp_system_base {

	public function execute() {

		$get = $this->request->getx();
		if( empty( $get['meid'] ) ) {
			$this->message( 'error', '获取详情失败' );
		}
		if( empty( $get['logid'] ) ) {
			$this->message( 'error', '缺少参数，获取详情失败' );
		}

		$meid = $get['meid'];
		if (isset($get['logid'])) $logid = $get['logid'];
		else $logid = 0;

		if (isset($get['yd'])) $yd = $get['yd'];
		else $yd = 0;

		$uid = $this->_user['ca_id']; // 当前登录用户的uid  `ca_id`

		// 通过rpc进行处理
		$rpc = $this->by_rpc_fun("/OaRpc/Rpc/EnterMessage");

		$rpc_view = $rpc->get_view($meid, $logid, $uid, $yd);

		if( $rpc_view['atid'] ) {
			$rpc_view['imgurl'] = config::get( 'voa.main_url' ) . 'attachment/read/' . $rpc_view['atid'];
		}

		$json_data['data'] = $rpc_view;
		$return = json_encode($json_data);

		$this->view->set('return', $return);

		// var_dump($this->cpurl($this->_module, $this->_operation, 'list', $this->_module_plugin_id));die;
		$this->view->set('list_url', $this->cpurl($this->_module, $this->_operation, 'list', $this->_module_plugin_id));
		$this->output( 'system/message/view' );
	}

}
