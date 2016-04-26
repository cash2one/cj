<?php
/**
 * application.php
 * 应用维护的内部接口
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_server_oa_application {

	/**
	 * __construct
	 * 构造函数
	 * @return void
	 */
	public function __construct() {
		if (!voa_h_conf::init_db()) {
			exit('config file is missing.');
			return false;
		}
	}

	/**
	 * 通知企业OA站点，应用开通成功，并写入企业微信应用型代理信息
	 * @param array $params = array('cp_pluginid' => '', 'qywx_application_agent' => array('agentid' => '', 'cyea_id' => ''))
	 * @throws rpc_exception
	 * @return void
	 */
	public function app_open_confirm($params) {
		if (!isset($params['cp_pluginid'])) {
			throw new rpc_exception('客服返回未知的本地应用 ID', 401);
		}
		if (!isset($params['qywx_application_agent'])) {
			throw new rpc_exception('客服返回未知的应用型代理数据', 402);
		}
		if (!isset($params['qywx_application_agent']['agentid'])) {
			throw new rpc_exception('客服返回未知的应用型代理 ID', 403);
		}
		if (!isset($params['qywx_application_agent']['cyea_id'])) {
			throw new rpc_exception('客服返回未知的应用处理ea_id', 404);
		}
		$uda_application_open = &uda::factory('voa_uda_frontend_application_open');
		if ($uda_application_open->open_confirm($params['cp_pluginid'], $params['qywx_application_agent'])) {
			return true;
		} else {
			// 启动确认添加应用操作失败，则抛出错误信息
			throw new rpc_exception($uda_application_open->error, 409);
		}
	}

	/**
	 * 通知OA站点，应用关闭成功
	 * @param array $params = array('cp_pluginid' => '')
	 * @throws rpc_exception
	 * @return boolean
	 */
	public function app_close_confirm($params) {
		if (!isset($params['cp_pluginid'])) {
			throw new rpc_exception('客服返回未知的本地应用 ID', 501);
		}
		$params['qywx_application_agent'] = array();
		$uda_application_close = &uda::factory('voa_uda_frontend_application_close');
		if ($uda_application_close->close_confirm($params['cp_pluginid'], $params['qywx_application_agent'])) {
			return true;
		} else {
			// 启动确认添加应用操作失败，则抛出错误信息
			logger::error('app_close_confirm|'.$uda_application_close->error.'|509');
			throw new rpc_exception($uda_application_close->error, 509);
		}
	}

	/**
	 * 通知OA站点，应用删除成功
	 * @param array $params = array('cp_pluginid' => '')
	 * @throws rpc_exception
	 * @return boolean
	 */
	public function app_delete_confirm($params) {
		if (!isset($params['cp_pluginid'])) {
			throw new rpc_exception('客服返回未知的本地应用 ID', 601);
		}
		$params['qywx_application_agent'] = array();
		$uda_application_delete = &uda::factory('voa_uda_frontend_application_delete');
		if ($uda_application_delete->delete_confirm($params['cp_pluginid'], $params['qywx_application_agent'])) {
			return true;
		} else {
			// 启动确认添加应用操作失败，则抛出错误信息
			throw new rpc_exception($uda_application_delete->error, 609);
		}
	}

}
