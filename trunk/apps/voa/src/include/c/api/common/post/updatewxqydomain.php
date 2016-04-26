<?php
/**
 * updatewxqydomain.php
 * 外部请求更新微信企业号自定义菜单
 * Create By Deepseath
 * $Author$
 * $Id$
 */

class voa_c_api_common_post_updatewxqydomain extends voa_c_api_common_abstract {


	/** 当前请求更新的应用信息 */
	private $__plugin = array();

	/** 是否开启该接口 */
	private $__is_open = true;

	protected function _before_action($action) {

		$this->_require_login = false;
		if (!parent::_before_action($action)) {
			return false;
		}

		return true;
	}

	public function execute() {

		return true;
		// 引入应用套件服务
		$wxserv = voa_wxqysuite_service::instance();

		$serv_suite = service::factory('voa_s_oa_suite');
		$suites = $serv_suite->fetch_all();

		$plugin2appid = config::get(startup_env::get('app_name').'.suite.plgin2appid');

		foreach ($suites as $_suite) {
			$authinfo = unserialize($_suite['authinfo']);
			foreach ($authinfo['auth_info']['agent'] as $_agent) {
				// 应用信息
				$agent = array(
					'agentid' => $_agent['agentid'],
					//'name' => $plugin['cp_name'],
					//'description' => $plugin['cp_description'],
					'redirect_domain' => $_SERVER['HTTP_HOST'],
				);

				if ('tj0129f84436fb3a58' == $_suite['suiteid'] && 3 == $_agent['appid']) {
					$agent['report_location_flag'] = 1;
				}

				// 设置微信应用
				$wxserv->set_agent($agent, $_suite['suiteid']);
			}
		}

	}

}
