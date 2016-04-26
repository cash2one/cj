<?php
/**
 * close.php
 * 关闭应用
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_frontend_application_close extends voa_uda_frontend_application_base {

	public function __construct() {
		parent::__construct();
	}


	/**
	 * 关闭应用
	 * @param number $cp_pluginid
	 * @return boolean
	 */
	public function close($cp_pluginid) {

		if ($this->open_wxqy) {
			// 启用了微信企业号

			if ($this->use_qywx_api == 'qywx') {
				// 直接使用企业微信接口关闭

				$qywx_application_agent = array();
				if ($this->_to_qywx_close($cp_pluginid, $qywx_application_agent) === false) {
					// 通知微信失败
					if (empty($this->error)) {
						$this->errmsg(1001, '禁用应用发生通讯错误');
					}
					return false;
				}

				// 通知微信成功，继续关闭本地
				if ($this->close_confirm($cp_pluginid, $qywx_application_agent) === false) {
					// 关闭失败
					if (empty($this->error)) {
						$this->errmsg(1001, '禁用本地应用发生错误');
					}
					return false;
				}

			} elseif ($this->use_qywx_api === 'cyadmin') {
				// 使用畅移客服关闭

				if ($this->_to_cy_close($cp_pluginid) === false) {
					// 通知禁用失败
					if (empty($this->error)) {
						$this->errmsg(1002, '关闭应用发生通讯错误');
					}
					return false;
				}
			} else {
				// 自行关闭，同时通知给畅移客服

				// 通知标记畅移后台该应用为待关闭状态，但暂时不处理本地应用状态
				$cyea_id = 0;
				if ($this->_to_cy_close($cp_pluginid, true) === false) {
					// 通知关闭失败
					if (empty($this->error)) {
						$this->errmsg(1004, '关闭应用发生通讯错误');
					}
					return false;
				}

				$plugin = $this->get_plugin($cp_pluginid);

				// 正式关闭本地应用
				$qywx_application_agent = array(
					'agentid' => $plugin['cp_agentid'],
					'cyea_id' => $plugin['cyea_id']
				);
				if ($this->close_confirm($cp_pluginid, $qywx_application_agent) === false) {
					// 关闭本地失败
					if (empty($this->error)) {
						$this->errmsg(1005, '关闭本地应用发生错误');
					}
					return false;
				}

				// 通知畅移后台正式关闭
				if (!$this->vchangyi_application_api($cp_pluginid, 'confirm_close')) {
					return false;
				}

			}

		} else {
			// 未启用微信企业号

			$qywx_application_agent = array();
			if ($this->close_confirm($cp_pluginid, $qywx_application_agent) === false) {
				// 关闭失败
				if (empty($this->error)) {
					$this->errmsg(1001, '禁用本地应用发生错误');
				}
				return false;
			}

		}

		// 更新系统缓存
		$this->update_cache();

		return true;
	}

	/**
	 * 关闭应用（确定完成关闭）
	 * @param number $cp_pluginid
	 * @param array $qywx_application_agent 应用型代理信息数据
	 * @return boolean
	 */
	public function close_confirm($cp_pluginid, $qywx_application_agent = array()) {

		// 当前应用信息
		$plugin = $this->get_plugin($cp_pluginid);
		if (empty($plugin)) {
			$this->error = '应用不存在 或 已下架';
			return false;
		}

		if ($plugin['cp_available'] == $this->available_types['close']) {
			$this->error = '应用已关闭';
			return false;
		}

		// 更新应用状态为关闭
		$this->update_available($cp_pluginid, 'close', $qywx_application_agent);

		// 更新系统缓存
		$this->update_cache();

		return true;
	}

	/**
	 * 发送到畅移客服请求关闭
	 * @param number $cp_pluginid
	 * @param boolean $ignore_local 忽略本地应用状态更新
	 * @return boolean
	 */
	protected function _to_cy_close($cp_pluginid, $ignore_local = false) {

		if (!$this->check_status_change_expire($cp_pluginid)) {
			// 检查该应用更改状态是否频繁
			return false;
		}

		$cyea_id = 0;
		if (!$this->vchangyi_application_api($cp_pluginid, 'close', $cyea_id)) {
			return false;
		}

		// 更新应用状态为待关闭
		if (!$ignore_local) {
			$this->update_available($cp_pluginid, 'wait_close');
		}
		return true;
	}

	/**
	 * 发送到企业微信请求关闭
	 * @param number $cp_pluginid
	 * @param array $qywx_application_agent <strong>(应用结果)</strong> 应用型代理信息
	 * @return boolean
	 */
	protected function _to_qywx_close($cp_pluginid, &$qywx_application_agent) {

		if (!$this->check_status_change_expire($cp_pluginid)) {
			// 检查该应用更改状态是否频繁
			return false;
		}

		// TODO 企业微信禁用应用接口

		return true;
	}

}
