<?php
/**
 * open.php
 * 开启应用
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_frontend_application_open extends voa_uda_frontend_application_base {

	/** 待操作的插件信息 */
	protected $_plugin = array();

	public function __construct() {
		parent::__construct();
	}


	/**
	 * 开启应用
	 * @param number $cp_pluginid
	 * @param string $qywx_application_agentid 该应用对应的企业微信应用agentid（自主开启应用时需要）
	 * @return boolean
	 */
	public function open($cp_pluginid, $qywx_application_agentid) {

		if ($this->open_wxqy) {
			// 启用了微信企业号

			if ($this->use_qywx_api === 'qywx') {
				// 直接使用企业微信接口开通

				$qywx_application_agent = array();
				if ($this->_to_qywx_open($cp_pluginid, $qywx_application_agent) === false) {
					// 通知微信失败
					if (empty($this->error)) {
						$this->errmsg(1001, '开通应用发生通讯错误');
					}
					return false;
				}

				// 通知微信成功，继续开启本地
				if ($this->open_confirm($cp_pluginid, $qywx_application_agent) === false) {
					// 开通本地失败
					if (empty($this->error)) {
						$this->errmsg(1002, '开启本地应用发生错误');
					}
					return false;
				}

			} elseif ($this->use_qywx_api === 'cyadmin') {
				// 使用畅移客服开通

				$cyea_id = 0;
				if ($this->_to_cy_open($cp_pluginid, $cyea_id) === false) {
					// 通知开通失败
					if (empty($this->error)) {
						$this->errmsg(1003, '启用应用发生通讯错误');
					}
					return false;
				}
			} else {
				// 自行开启，同时通知给畅移客服

				if (empty($qywx_application_agentid)) {
					$this->errmsg(1004, '企业微信应用 AgentID 不能为空');
					return false;
				}

				// 通知标记畅移后台该应用为待启用状态，但暂时不处理本地应用状态
				$cyea_id = 0;
				if ($this->_to_cy_open($cp_pluginid, $cyea_id, true) === false) {
					// 通知开通失败
					if (empty($this->error)) {
						$this->errmsg(1004, '启用应用发生通讯错误');
					}
					return false;
				}

				// 正式开启本地应用
				$qywx_application_agent = array(
					'agentid' => $qywx_application_agentid,
					'cyea_id' => $cyea_id
				);
				if ($this->open_confirm($cp_pluginid, $qywx_application_agent) === false) {
					// 开通本地失败
					if (empty($this->error)) {
						$this->errmsg(1005, '开启本地应用发生错误');
					}
					return false;
				}

				// 通知畅移后台正式开启
				if (!$this->vchangyi_application_api($cp_pluginid, 'confirm_open')) {
					return false;
				}

			}

		} else {
			// 未启用微信企业号，则直接操作

			$qywx_application_agent = array();
			if ($this->open_confirm($cp_pluginid, $qywx_application_agent) === false) {
				// 开通本地失败
				if (empty($this->error)) {
					$this->errmsg(1006, '开启本地应用发生错误');
				}
				return false;
			}

		}

		return true;
	}

	/**
	 * 开启应用（确定完成开启）
	 * @param number $cp_pluginid
	 * @param array $qywx_application_agent 应用型代理信息数据， array('agentid' => '', 'cyea_id' => '')
	 * @return boolean
	 */
	public function open_confirm($cp_pluginid, $qywx_application_agent) {

		// 如果启用了微信企业号则检查应用型代理ID或者客服处理ID
		if ($this->open_wxqy) {
			if (empty($qywx_application_agent['agentid'])) {
				$this->error = '未知的应用代理 ID';
				$this->errmsg(1101, $this->error);
				return false;
			}

			if (!$this->use_qywx_api && empty($qywx_application_agent['cyea_id'])) {
				$this->error = '应用客服处理 ID 未知';
				$this->errmsg(1102, $this->error);
				return false;
			}
		}

		// 当前应用信息
		$plugin = $this->get_plugin($cp_pluginid);
		if (empty($plugin)) {
			$this->error = '应用不存在 或 已下架';
			$this->errmsg(1103, $this->error);
			return false;
		}

		if ($plugin['cp_available'] == $this->available_types['open']) {
			//$this->error = '应用已开启不需要再次启用';
			//$this->errmsg(1104, $this->error);
			//return false;
		}

		// 如果启用了使用企业微信菜单接口 则 尝试创建企业微信应用型代理的菜单
		if ($this->open_wxqy && $this->use_qywx_menu_api && !$this->_create_application_agent_menu($plugin, $qywx_application_agent)) {
			// 若失败，则退出
			return false;
		}

		try {

			$this->serv_plugin->begin();

			// 更新应用状态为开启
			$this->update_available($cp_pluginid, 'open', $qywx_application_agent);

			// 创建应用数据表并导入数据
			$this->_import_application_database($plugin, $qywx_application_agent);

			// 更新插件配置表内的插件ID
			$class_setting = 'voa_s_oa_'.$plugin['cp_identifier'].'_setting';
			if (class_exists($class_setting)) {
				$serv_plugin_setting = &service::factory($class_setting);
				logger::error(var_export($qywx_application_agent, true));
				$serv_plugin_setting->update_setting(array(
					'pluginid' => $cp_pluginid,
					'agentid' => (int)$qywx_application_agent['agentid']
				));
			}

			// 尝试创建应用的后台管理菜单
			$this->_create_application_cpmenu($plugin);

			// 移除与当前开启的应用的agentid相同的其他本应用的agentid
			if (!isset($qywx_application_agent['cp_agentid'])) {
				$qywx_application_agent['cp_agentid'] = 0;
			}
			$this->serv_plugin->clear_agentid($cp_pluginid, $qywx_application_agent['cp_agentid']);

			$this->serv_plugin->commit();

		} catch (Exception $e) {
			$this->serv_plugin->rollback();
			$this->error = '启用应用数据处理过程发生错误';
			$this->errmsg(1105, $this->error);
			logger::error($e);
			//throw new controller_exception($e->getMessage(), $e->getCode());
			return false;
		}

		// 更新系统缓存
		$this->update_cache();

		return true;
	}

	/**
	 * 发送到畅移客服请求开启
	 * @param number $cp_pluginid
	 * @param number $cyea_id <strong style="color:red">(引用结果)</strong>当前应用在畅移后台对应的ID
	 * @param boolean $ignore_local 忽略本地应用状态更新
	 * @return boolean
	 */
	public function _to_cy_open($cp_pluginid, &$cyea_id, $ignore_local = false) {

		if (!$this->check_status_change_expire($cp_pluginid)) {
			// 检查该应用更改状态是否频繁
			return false;
		}

		$cyea_id = 0;
		if (!$this->vchangyi_application_api($cp_pluginid, 'open', $cyea_id)) {
			return false;
		}

		// 更新应用状态为待开启
		if (!$ignore_local) {
			$this->update_available($cp_pluginid, 'wait_open', array('cyea_id' => $cyea_id));
		}
		return true;
	}

	/**
	 * 发送到企业微信请求开启
	 * @param number $cp_pluginid
	 * @param array $qywx_application_agent <strong>(应用结果)</strong> 应用型代理信息
	 * @return boolean
	 */
	protected function _to_qywx_open($cp_pluginid, &$qywx_application_agent) {

		if (!$this->check_status_change_expire($cp_pluginid)) {
			// 检查该应用更改状态是否频繁
			return false;
		}

		// TODO 企业微信开通应用接口
		//$qywx_application_agent['agentid'] = time();// debug调试

		return true;
	}

	/**
	 * 尝试为应用创建企业微信应用型代理菜单
	 * @param array $plugin
	 * @param array $qywx_application_agent 应用型代理信息
	 * @return boolean
	 */
	protected function _create_application_agent_menu($plugin, $qywx_application_agent) {
		// 获取应用菜单配置数据
		$agent_menu = config::get(startup_env::get('app_name').'.application.'.$plugin['cp_identifier'].'.menu.qywx');
		if (!empty($agent_menu)) {
			// 应用菜单数据存在存在，则链接接口创建菜单

			// 加载企业微信应用型代理菜单接口类
			$qywx_menu = new voa_wxqy_menu();

			if (!$qywx_menu->create($qywx_application_agent['agentid'], $agent_menu, $plugin['cp_pluginid'])) {
				$this->error = empty($qywx_menu->menu_error) ? '创建应用菜单发生错误' : $qywx_menu->menu_error;
				$this->errmsg(1106, $this->error);
				return false;
			}
		}

		return true;
	}

	/**
	 * 尝试为应用创建数据表结构 并 导入默认数据
	 * @param array $plugin 应用信息
	 * @param array $qywx_application_agent 应用型代理信息
	 * @return void
	 */
	protected function _import_application_database($plugin, $qywx_application_agent) {

		// 应用id
		$cp_pluginid = $plugin['cp_pluginid'];

		$serv_sql = &service::factory('voa_server_sql', array('pluginid' => $cp_pluginid));

		// 获取数据模块前缀
		$db_module = str_replace(array('.member', 'member'), '', voa_d_oa_member::$__table);

		// 尝试为该应用创建数据表结构
		$tablenames = $serv_sql->create_application_table($db_module, $plugin['cp_identifier'], array('pluginid' => $cp_pluginid));

		// 需要写入默认数据的表
		$insert_tables = array();
		// 需要清空历史数据的表
		$truncate_tables = array();

		if (!$this->truncate_data) {
			// 如果不需要清空数据表，则尝试确定表内是否存在数据
			if (empty($plugin['cp_lastopen'])) {
				// 第一次开启该应用，则标记导入默认数据
				$insert_tables = $tablenames;
			} else {
				// 不是第一次开启，则标记不导入数据
				$insert_tables = array();
			}
		} else {
			// 需要清空全部数据
			$truncate_tables = $tablenames;
		}

		// 尝试导入默认数据
		$serv_sql->import_application_data($db_module, $plugin['cp_identifier'], array('pluginid' => $cp_pluginid), $truncate_tables, $insert_tables);

		return;
	}

	/**
	 * 尝试为应用创建本地后台管理菜单
	 * @param array $plugin 应用信息
	 * @return void
	 */
	protected function _create_application_cpmenu($plugin) {

		// 获取后台管理菜单配置
		$conf_cpmenu = config::get(startup_env::get('app_name').'.application.'.$plugin['cp_identifier'].'.menu.admincp');
		if (empty($conf_cpmenu)) {
			// 未配置管理菜单，则尝试清理系统内存在的数据
			$this->clear_cpmenu($plugin['cp_pluginid']);
			return;
		}

		// 当前应用所属的分组信息
		$module_group = $this->_module_group_list($plugin['cmg_id'], true);
		// 分组标识名
		$cmg_dir = $module_group['cmg_dir'];

		/**
		 * 开始添加菜单
		 * 先检查是否存在此cp_pluginid的菜单 cpmenu_list
		 * 移除不在配置 $conf_cpmenu 里的菜单
		 * 尝试添加 cpmenu_list 里没有 $conf_cpmenu 的菜单
		 */

		// 尝试找到系统已经存在此应用的菜单数据
		$cpmenu_list = $this->serv_cpmenu->fetch_all_by_cp_pluginid($plugin['cp_pluginid']);

		// 假设添加全部配置内的菜单
		$add_menu = $conf_cpmenu;

		// 假设需要添加应用的管理目录菜单
		$add_menu_dir = true;

		// 需要标记为显示的菜单
		$open_menu_ccm_ids = array();

		$remove_ccm_ids = array();
		foreach ($cpmenu_list as $ccm_id => $menu) {
			if ($menu['ccm_module'] == 'setting') {
				// 不处理系统设置菜单
				// 由于新机制下应用设置菜单移动到了应用自身目录下
				// 所以旧数据内的数据是无效的
				$remove_ccm_ids[] = $ccm_id;
				continue;
			}
			if ($menu['ccm_subop'] == '') {
				// 这是一个目录，则标记不需要再添加应用管理目录菜单
				$add_menu_dir = false;
				if (!$menu['ccm_display']) {
					// 如果标记为隐藏，则启用
					$open_menu_ccm_ids[] = $ccm_id;
				}
				continue;
			}

			/**
			 * 非目录菜单则判断
			 * - 该菜单是否还是有效的
			 * - 该菜单是否还需要添加
			 */
			if (!isset($conf_cpmenu[$menu['ccm_subop']])) {
				// 该菜单不存在于配置内，则标记删除
				$remove_ccm_ids[] = $ccm_id;
			} else {
				// 系统内存在配置的菜单，则不再重复添加
				unset($add_menu[$menu['ccm_subop']]);

				if (!$menu['ccm_display']) {
					// 如果标记为隐藏，则启用
					$open_menu_ccm_ids[] = $ccm_id;
				}
			}
		}

		if ($remove_ccm_ids) {
			// 需要删除的历史旧的无效菜单
			$this->serv_cpmenu->delete($remove_ccm_ids);
		}

		if ($open_menu_ccm_ids) {
			// 需要标记为显示的菜单
			$this->serv_cpmenu->update(array('ccm_display' => 1), $open_menu_ccm_ids);
		}

		// 菜单的基本公共数据
		$menu_data_base = array(
			'cp_pluginid' => $plugin['cp_pluginid'],
			'ccm_system' => 0,
			'ccm_module' => $module_group['cmg_dir'],
			'ccm_operation' => $plugin['cp_identifier'],
			'ccm_subop' => '',
			'ccm_type' => '',
			'ccm_default' => 0,
			'ccm_name' => '',
			'ccm_icon' => '',
			'ccm_display' => 1,
			'ccm_subnavdisplay' => 1,
			'ccm_displayorder' => $plugin['cp_displayorder'],
			'ccm_status' => voa_d_oa_common_cpmenu::STATUS_NORMAL
		);

		if ($add_menu_dir) {
			// 需要添加应用分组菜单目录
			$module_group_menu = $menu_data_base;
			$module_group_menu['ccm_type'] = 'operation';
			if ($add_menu_dir) {
				$module_group_menu['ccm_default'] = 1;
			}
			$module_group_menu['ccm_name'] = $plugin['cp_name'];
			$this->serv_cpmenu->insert($module_group_menu);
		}

		if ($add_menu) {
			// 需要添加的应用菜单目录
			foreach ($add_menu as $_key => $_menu) {
				$plugin_cpmenu = $menu_data_base;
				$plugin_cpmenu['ccm_subop'] = $_key;
				$plugin_cpmenu['ccm_type'] = 'subop';
				$plugin_cpmenu['ccm_name'] = $_menu['name'];
				if ($_menu['default']) {
					$plugin_cpmenu['ccm_default'] = 1;
				}
				if ($_menu['icon']) {
					$plugin_cpmenu['ccm_icon'] = $_menu['icon'];
				}
				if (isset($_menu['displayorder']) && is_numeric($_menu['displayorder'])) {
					$plugin_cpmenu['ccm_displayorder'] = $_menu['displayorder'];
				}
				if (isset($_menu['subnavdisplay'])) {
					$plugin_cpmenu['ccm_subnavdisplay'] = $_menu['subnavdisplay'] ? 1 : 0;
				} else {
					$plugin_cpmenu['ccm_subnavdisplay'] = 0;
				}

				$this->serv_cpmenu->insert($plugin_cpmenu);
			}
		}

		return;
	}

}
