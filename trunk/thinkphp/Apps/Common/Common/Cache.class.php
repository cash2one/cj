<?php
/**
 * Cache.class.php
 * 缓存
 * $Author$
 */

namespace Common\Common;

class Cache extends \Com\Cache {

	// 实例化
	public static function &instance() {

		static $instance;
		if(empty($instance)) {
			$instance	= new self();
		}

		return $instance;
	}

	public function __construct() {

		parent::__construct();
	}

	/**
	 * 获取 common_setting 的缓存信息;
	 * @return array
	 */
	public function setting() {

		// 获取全局配置表数据
		$serv = D('Common/CommonSetting', 'Service');
		return $serv->list_kv();
	}

	/**
	 * 获取 weixin_setting 的缓存信息
	 * @return array
	 */
	public function weixin() {

		// 获取微信接口缓存配置
		$serv = D('Common/WeixinSetting', 'Service');
		return $serv->list_kv();
	}

	/**
	 * 获取用户配置表 member_setting 的缓存信息;
	 * @return array
	 */
	public function member_setting() {

		// 获取留言本配置表数据
		$serv = D('Common/MemberSetting', 'Service');
		return $serv->list_kv();
	}

	/**
	 * 获取 common_plugin 的缓存信息
	 * @return array
	 */
	public function plugin() {

		// 获取插件信息
		$serv = D('Common/CommonPlugin', 'Service');
		return $serv->list_all();
	}

	/**
	 * 获取 common_cpmenu 的菜单信息
	 * @return array
	 */
	public function cpmenu() {

		// 获取后台管理菜单信息
		$serv = D('Common/CommonCpmenu', 'Service');
		return $serv->list_all();
	}

	/**
	 * 获取 common_department 的部门信息
	 * @return array
	 */
	public function department() {

		// 获取后台管理部门信息
		$serv = D('Common/CommonDepartment', 'Service');
		return $serv->list_all(null, array('cd_displayorder' => 'ASC', 'cd_lastordertime' => 'DESC'));
	}

	/**
	 * 获取部门ID上下级对应信息
	 */
	public function department_p2c() {

		$serv = D('Common/CommonDepartment', 'Service');
		return $serv->list_p2c();
	}

	/**
	 * 获取 common_job 的职位信息
	 * @return array
	 */
	public function job() {

		// 获取后台管理职位信息
		$serv = D('Common/CommonJob', 'Service');
		return $serv->list_all();
	}

	/**
	 * 获取 member postion 的职务信息
	 * @return array
	 */
	public function positions() {

		// 获取后台管理职位信息
		$serv = D('Common/MemberPosition', 'Service');
		return $serv->list_all();
	}

	// 默认处理
	public function __call($method, $args) {

		// 读取 UC 的数据
		/**if (preg_match('/^tj/i', $method)) {
			$data = array();
			$url = cfg('UCENTER_RPC_HOST').'/OaRpc/Rpc/Suite';
			if (!\Com\Rpc::query($data, $url, 'get_suite_token', $method)) {
				\Think\Log::record(var_export($data, true));
				E('_ERR_SUITE_TOKEN_ERROR');
				return array();
			}

			return $data;
		}*/

		E('_ERR_CACHE_UNDEFINED');
		return array();
	}

	/**
	 * 初始化, 保证缓存文件都在同一个目录下
	 *
	 * @param mixed $options 缓存配置
	 * @return boolean
	 */
	public function init_options(&$options = null) {

		// 如果配置为空
		if (empty($options)) {
			$options = array();
		}

		// 如果缓存目录不存在
		if (empty($options['temp'])) {
			$options['temp'] = get_sitedir();
			// 特殊处理, 把缓存移到旧框架下
			$pattern = addcslashes(cfg('DATA_CACHE_PATH'), '"\/\'\.');
			$replacement = APP_PATH . '../../apps/voa/tmp/site/';
			$options['temp'] = preg_replace('/^' . $pattern . '/i', $replacement, $options['temp']);
		}

		return true;
	}

}
