<?php
/**
 * enterprise.php
 * 应用维护的内部接口
 * Create By mojianyuan
 * $Author$
 * $Id$
 */
class voa_server_oa_enterprise {

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
	 * 通知企业OA站点，更新corp参数
	 * @param array $params = array('cp_pluginid' => '', ''))
	 * @throws rpc_exception
	 * @return void
	 */
	public function update_corp($params) {

		/*
		if (!isset($params['ep_wxcorpid'])) {
			throw new rpc_exception('未收到 ep_wxcorpid', 403);
		}
		if (!isset($params['ep_wxcorpsecret'])) {
			throw new rpc_exception('未收到ep_wxcorpsecret', 404);
		}*/
		$uda = &uda::factory('voa_uda_frontend_setting_wxcorp');
		if ($uda->update($params)) {
			return true;
		} else {
			// 启动确认添加应用操作失败，则抛出错误信息
			throw new rpc_exception($uda->error, 409);
		}
	}
}
