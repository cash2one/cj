<?php
/**
 * Created by PhpStorm.
 * User: ppker
 * Date: 2015/10/28
 * Time: 16:11
 */

namespace UcRpc\Service;

class EnterpriseAppService extends AbstractService {

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->_d = D("Common/EnterpriseApp");
	}

	/**
	 * 为企业新增应用信息
	 *
	 * @param array $params 请求参数
	 * + ep_id int
	 * + name string
	 * + agentid int
	 * + appstatus int
	 * + icon string
	 * + desc string
	 * + pluginid int
	 */
	public function newApp($params) {

		$app = array();
		// 获取参数
		extract_field($app, array(
			'ep_id' => array('ep_id', 'int'),
			'name' => array('ea_name', 'string'),
			'agentid' => array('ea_agentid', 'int'),
			'appstatus' => array('ea_appstatus', 'int'),
			'icon' => array('ea_icon', 'string'),
			'desc' => array('ea_description', 'string'),
			'pluginid' => array('oacp_pluginid', 'int')
		), $params);

		// 检查 ep_id
		if (empty($app['ep_id']) || 0 >= $app['ep_id']) {
			E('_ERR_EP_ID_ERROR');
			return false;
		}

		// 检查插件ID
		if (empty($app['oacp_pluginid']) || 0 >= $app['oacp_pluginid']) {
			E('_ERR_PLUGINID_ERROR');
			return false;
		}

		// 检查应用名称
		if (empty($app['ea_name'])) {
			E('_ERR_EP_NAME_EMPTY');
			return false;
		}

		// 检查微信应用ID
		if (empty($app['ea_agentid']) || 0 >= $app['ea_agentid']) {
			E('_ERR_AGENTID_ERROR');
			return false;
		}

		// 检查应用状态
		if (empty($app['ea_appstatus']) || !in_array($app['ea_appstatus'], $this->_d->list_app_status())) {
			E('_ERR_APPSTATUS_ERROR');
			return false;
		}

		$this->_d->insert($app);
		return true;
	}

	/**
	 * 更新企业应用信息
	 *
	 * @param array $params 请求参数
	 * + ep_id int
	 * + name string
	 * + agentid int
	 * + appstatus int
	 * + icon string
	 * + desc string
	 * + pluginid int
	 */
	public function updateApp($params) {

		$app = array();
		// 获取参数
		extract_field($app, array(
			'ep_id' => array('ep_id', 'int', true),
			'name' => array('ea_name', 'string', true),
			'agentid' => array('ea_agentid', 'int', true),
			'appstatus' => array('ea_appstatus', 'int', true),
			'icon' => array('ea_icon', 'string', true),
			'desc' => array('ea_description', 'string', true),
			'pluginid' => array('oacp_pluginid', 'int', true)
		), $params);

		// 检查 ep_id
		if (empty($app['ep_id']) || 0 >= $app['ep_id']) {
			E('_ERR_EP_ID_ERROR');
			return false;
		}

		// 检查插件ID
		if (empty($app['oacp_pluginid']) || 0 >= $app['oacp_pluginid']) {
			E('_ERR_PLUGINID_ERROR');
			return false;
		}

		// 更新条件
		$conds = array(
			'ep_id' => $app['ep_id'],
			'pluginid' => $app['oacp_pluginid']
		);
		unset($app['ep_id'], $app['oacp_pluginid']);

		// 如果没有待更新的数据
		if (empty($app)) {
			E('_ERR_UPDATE_DATA_EMPTY');
			return false;
		}

		$this->_d->update_by_conds($conds, $app);
		return true;
	}

}