<?php
/**
 * SignAlertService.class.php
 * $author$
 */

namespace Sign\Service;

use Common\Common\Cache;

class SignAlertService extends AbstractService {

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->_d = D('Sign/SignAlert');
	}

	/**
	 * 读取所有并检查 pluginid, agentid 参数是否正确
	 * @see \Common\Service\AbstractSettingService::list_kv()
	 */
	public function list_kv()
	{

		// 取表中的数据
		$sets = parent::list_kv();

		// 获取插件列表
		$cache = &Cache::instance();
		$plugins = $cache->get('Common.plugin');

		// 获取 pluginid, agentid
		$pluginid = empty($sets['pluginid']) ? 0 : (int)$sets['pluginid'];
		$agentid = empty($sets['agentid']) ? 0 : (int)$sets['agentid'];
		// 如果插件信息不存在, 则从插件重新获取 pluginid 和 agentid
		if (empty($plugins[$pluginid]) || $agentid != $plugins[$pluginid]['cp_agentid'] || 'sign' != rstrtolower($plugins[$pluginid]['cp_identifier'])) {

			// 遍历所有插件
			foreach ($plugins as $_p) {
				// 如果不是留言本, 则取下一个
				if ('sign' != rstrtolower($_p['cp_identifier'])) {
					continue;
				}

				// 取 pluginid, agentid 信息
				$pluginid = $_p['cp_pluginid'];
				$agentid = (int)$_p['cp_agentid'];

				// 更新表数据
				$this->update_kv(array(
					'pluginid' => $pluginid,
					'agentid' => $agentid
				));
			}
		}

		// 更新相关值
		$sets['pluginid'] = $pluginid;
		$sets['agentid'] = $agentid;

		return $sets;
	}


	public function delete_sign_alert_by_params($params){
		$this->_d->delete_by_params($params);
		return true;
	}
}
