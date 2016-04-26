<?php
/**
 * AbstractController.class.php
 * $author$
 */

namespace Sign\Controller\Api;

use Common\Common\Plugin;
use Common\Common\Cache;

abstract class AbstractController extends \Common\Controller\Api\AbstractController {

	public function before_action() {

		return parent::before_action();
	}

	public function after_action() {

		return parent::after_action();
	}

	// 部门 缓存
	protected $_deplist = array();
	// 人的信息 和 所在的班次
	protected $_member_data = array();

	// 获取插件配置
	protected function _get_plugin() {

		// 获取插件信息
		$this->_plugin = &Plugin::instance('sign');

		// 更新 pluginid, agentid 配置
		cfg('PLUGIN_ID', $this->_plugin->get_pluginid());
		cfg('AGENT_ID', $this->_plugin->get_agentid());
		cfg('PLUGIN_IDENTIFIER', $this->_plugin->get_name());

		return true;
	}

	/**
	 * 获取当前登录人物信息和所在的部门
	 * @param int $uid 当前登录人ID
	 * @return mixed
	 */
	protected function _get_all_member_department($uid) {

		// 登录人 信息
		$serv_mem = D('Common/Member', 'Service');
		$member = $serv_mem->get($uid);
		// 人员 部门 关联表
		$serv_department = D('Common/MemberDepartment', 'Service');
		$departments = $serv_department->list_by_uid($uid);

		// 去 匹配关联的部门ID
		foreach ($departments as $_k => $_v) {
			if ($member['m_uid'] == $_v['m_uid']) {
				$member['cd_ids'][] = $_v['cd_id'];
			}
		}

		return $member;
	}

	/**
	 * 获取部门关联的排班信息(开启的有班次信息)
	 * @return array
	 */
	protected function _get_department_batch() {

		// 获取启用的班次表
		$serv_batch = D('Sign/SignBatch', 'Service');
		$batch_data = $serv_batch->list_by_enable_cond();
		// 判断班次是否过期
		foreach ($batch_data as $k => $v) {
			if (isset($v['start_end']) && $v['start_end'] != 0 && (int)$v['start_end'] < NOW_TIME) {
				unset($batch_data[$k]);
			}
		}

		// 获取部门 班次关联表
		$serv_depart = D('Sign/SignDepartment', 'Service');
		$department_batch = $serv_depart->list_all();

		// 获取部门关联表的 班次工作日信息
		$department_batch_result = array();
		foreach ($department_batch as $k => &$v) {
			foreach ($batch_data as $_k => $_v) {
				// 匹配成功 班次信息赋值给关联数组
				if ($v['sbid'] == $_v['sbid'] && !empty($_v)) {
					$v['works'] = $_v;
				}
			}
			// 成功匹配班次的以部门ID为键值 班次信息为值 赋值新数组
			if (isset($v['works'])) {
				$department_batch_result[$v['department']] = $v;
			}
		}

		return $department_batch_result;
	}

	/**
	 * 匹配人员班次
	 * @param $m_uid 人员ID
	 * @param $department 班次对应的部门ID
	 * @return mixed
	 */
	protected function _get_member_batch($m_uid, &$department) {

		// 人员 部门数组
		$mem = $this->_get_all_member_department($m_uid);
		// 可用班次 部门数组
		$bdepartment = $this->_get_department_batch();

		foreach ($mem['cd_ids'] as $_cd_ids) {
			//当前部门是否在可用部门班次中
			if (isset($bdepartment[$_cd_ids])) {
				$mem['batch'][$_cd_ids] = $bdepartment[$_cd_ids]['sbid'];
				// get batchid 用于手机端获取 班次详情的所在部门id post sbid 用于手机端签到时 需要的部门id
				if (I('get.batchid') == $bdepartment[$_cd_ids]['sbid'] || I('post.sbid') == $bdepartment[$_cd_ids]['sbid']) {
					$department = $_cd_ids;
				}
			} else {
				//查上级部门是否有班次
				$cdid = $_cd_ids;
				while (!isset($bdepartment[$cdid])) {
					$cdid = $this->_get_upid($cdid);
					if (empty($cdid)) {
						break;
					}
				}
				//如果都没有班次
				if ($cdid != 0) {
					$mem['batch'][$cdid] = $bdepartment[$cdid]['sbid'];
					if (I('get.batchid') == $bdepartment[$_cd_ids]['sbid'] || I('post.sbid') == $bdepartment[$_cd_ids]['sbid']) {
						$department = $_cd_ids;
					}
				}
			}
		}

		return $mem;
	}

	/**
	 * 获取上级部门id
	 * @param $cd_id 部门ID
	 * @return mixed
	 */
	protected function _get_upid($cd_id) {

		// 如果部门缓存 变量是空的 那么获取
		if (empty($this->_deplist)) {
			$cache = &Cache::instance();
			$this->_deplist = $cache->get('Common.department');
		}
		// 获取部门 的上级部门ID
		$upid = $this->_deplist[$cd_id] ['cd_upid'];

		return $upid;
	}
}
