<?php
/**
 * delete.php
 * 删除应用
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_frontend_application_delete extends voa_uda_frontend_application_base {

	public function __construct() {
		parent::__construct();
	}


	/**
	 * 删除应用
	 * @param number $cp_pluginid
	 * @return boolean
	 */
	public function delete($cp_pluginid) {

		if ($this->open_wxqy) {
			// 启用了微信企业号

			if ($this->use_qywx_api == 'qywx') {
				// 直接使用企业微信接口删除

				$qywx_application_agent = array();
				if ($this->_to_qywx_delete($cp_pluginid, $qywx_application_agent) === false) {
					// 通知微信失败
					if (empty($this->error)) {
						$this->errmsg(1001, '移除应用发生通讯错误');
					}
					return false;
				}

				// 通知微信成功，继续删除本地
				if ($this->delete_confirm($cp_pluginid, $qywx_application_agent) === false) {
					// 删除本地失败
					if (empty($this->error)) {
						$this->errmsg(1001, '删除本地应用发生错误');
					}
					return false;
				}

			} elseif ($this->use_qywx_api == 'cyadmin') {
				// 使用畅移客服删除

				if ($this->_to_cy_delete($cp_pluginid) === false) {
					// 通知删除失败
					if (empty($this->error)) {
						$this->errmsg(1002, '删除应用发生通讯错误');
					}
					return false;
				}
			} else {
				// 自行手动删除

				// 通知标记畅移后台该应用为待删除状态，但暂时不处理本地应用状态
				$cyea_id = 0;
				if ($this->_to_cy_delete($cp_pluginid, true) === false) {
					// 通知删除失败
					if (empty($this->error)) {
						$this->errmsg(1004, '删除应用发生通讯错误');
					}
					return false;
				}

				$plugin = $this->get_plugin($cp_pluginid);

				// 正式删除本地应用
				$qywx_application_agent = array(
					'agentid' => $plugin['cp_agentid'],
					'cyea_id' => $plugin['cyea_id']
				);
				if ($this->delete_confirm($cp_pluginid, $qywx_application_agent) === false) {
					// 删除本地失败
					if (empty($this->error)) {
						$this->errmsg(1005, '删除本地应用发生错误');
					}
					return false;
				}

				// 通知畅移后台正式删除
				if (!$this->vchangyi_application_api($cp_pluginid, 'confirm_delete')) {
					return false;
				}

			}
		} else {
			// 未启用企业微信号

			$qywx_application_agent = array();
			if ($this->delete_confirm($cp_pluginid, $qywx_application_agent) === false) {
				// 删除本地失败
				if (empty($this->error)) {
					$this->errmsg(1001, '删除本地应用发生错误');
				}
				return false;
			}

		}

		// 更新系统缓存
		$this->update_cache();

		return true;
	}

	/**
	 * 删除应用（确定完成关闭）
	 * @param number $cp_pluginid
	 * @param array $qywx_application_agent 应用型代理信息数据
	 * @return boolean
	 */
	public function delete_confirm($cp_pluginid, $qywx_application_agent = array()) {

		// 当前应用信息
		$plugin = $this->get_plugin($cp_pluginid);
		if (empty($plugin)) {
			$this->error = '应用不存在 或 已下架';
			$this->errmsg(1201, $this->error);
			return false;
		}

		if ($plugin['cp_available'] == $this->available_types['delete']) {
			$this->error = '该应用未启用不需要删除';
			$this->errmsg(1202, $this->error);
			return false;
		}

		// 隐藏后台管理菜单
		$this->clear_cpmenu($cp_pluginid);

		// 更新应用状态为删除
		$this->update_available($cp_pluginid, 'delete', $qywx_application_agent);

		// 更新系统缓存
		$this->update_cache();

		return true;
	}

	/**
	 * 发送到畅移客服请求删除
	 * @param number $cp_pluginid
	 * @param boolean $ignore_local 是否忽略本地应用状态更新
	 * @return boolean
	 */
	protected function _to_cy_delete($cp_pluginid, $ignore_local = false) {

		if (!$this->check_status_change_expire($cp_pluginid)) {
			// 检查该应用更改状态是否频繁
			return false;
		}

		$cyea_id = 0;
		if (!$this->vchangyi_application_api($cp_pluginid, 'delete', $cyea_id)) {
			return false;
		}

		// 更新应用状态为待删除
		if (!$ignore_local) {
			$this->update_available($cp_pluginid, 'wait_delete');
		}
		return true;
	}

	/**
	 * 发送到企业微信请求删除
	 * @param number $cp_pluginid
	 * @param array $qywx_application_agent <strong>(应用结果)</strong> 应用型代理信息
	 * @return boolean
	 */
	protected function _to_qywx_delete($cp_pluginid, &$qywx_application_agent) {

		if (!$this->check_status_change_expire($cp_pluginid)) {
			// 检查该应用更改状态是否频繁
			return false;
		}

		// TODO 企业微信删除应用接口

		return true;
	}

}
