<?php
/**
 * department.php
 * 命令行方式管理部门数据
 * @uses php tool.php -n department
 * -action 可选值：sync，用于同步本地和微信的部门数据
 *
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_backend_tool_department extends voa_backend_base {

	private $__opts = array();
	/** 动作方法前缀名 */
	private $__prefix = '___';
	/** 企业微信通讯录处理类 */
	private $_qywx_menu = null;

	public function __construct($opts) {

		parent::__construct();
		$this->__opts = $opts;
	}

	public function main() {

		return;
		// 要执行的动作名
		$action = isset($this->__opts['action']) ? $this->__opts['action'] : '';

		// 动作所要执行的方法
		$class_name = $this->__prefix.$action;

		if (!method_exists($this, $class_name)) {
			// 指定的动作不存在
			$msg = '-action not defined.';
			if ($action) {
				$msg .= PHP_EOL.'Class "'.get_class().'" method "'.$class_name.'" not exists.';
			}
			return $this->___help($msg);
		}

		$this->_qywx_menu = new voa_wxqy_menu();

		// 执行具体的动作
		return call_user_func(array($this, $class_name));
	}

	/**
	 * 帮助指令
	 * @param string $message 额外显示的信息
	 * @return boolean
	 */
	private function ___help($message = '') {

		// 获取当前类的所有方法名
		$methods = get_class_methods(get_class());

		// 分析当前类内的所有已定义的动作名
		$actions = array();
		foreach ($methods as $method_name) {
			if (stripos($method_name, $this->__prefix) !== 0) {
				continue;
			}
			$actions[] = "\t".'-action '.str_ireplace($this->__prefix, '', $method_name);
		}

		// 列表所有动作指令
		$actions_list = implode(PHP_EOL, $actions);

		$msg = $message ? 'ERROR: '.$message.PHP_EOL.PHP_EOL : '';
		$msg .= <<<EOF
Options:
{$actions_list}
EOF;
		return $this->__output($msg, false, false);
	}

	/**
	 * 执行部门数据与企业微信接口同步
	 * @return boolean
	 */
	private function ___sync() {

		// 获取企业微信的部门列表
		$qywx_addressbook = new voa_wxqy_addressbook();
		// 获取企业微信的部门列表的结果
		$result = array();
		$qywx_addressbook->department_list($result);

		// 整理后的企业微信部门列表数据，以部门id为键名
		$qywx_department_list = array();
		if (isset($result['department'])) {
			// 企业微信存在部门数据

			foreach ($result['department'] as $r) {
				if (!$r['parentid']) {
					// 忽略根部门
					continue;
				}
				$qywx_department_list[$r['id']] = $r;
			}
		}

		$uda_update = &uda::factory('voa_uda_frontend_department_update');
		$uda_get = &uda::factory('voa_uda_frontend_department_get');

		// 本地部门列表数据
		$local_department_list = array();
		$uda_get->list_all($local_department_list, 'primary');

		// 本地的部门列表，以微信企业部门id为键名
		$local_have_id_list = array();
		$local_no_id_list = array();
		foreach ($local_department_list as $cd_id => $cd) {
			if ($cd['cd_qywxid']) {
				// 本地存在
				$local_list[$cd['cd_qywxid']] = array('id' => $cd['cd_qywxid'], 'name' => $cd['cd_name'], 'parentid' => $cd['cd_qywxparentid'], 'cd_id' => $cd_id);
			} else {
				// 该部门本地存在，微信不存在 —— 无微信部门id
				$local_no_id_list[$cd_id] = array('id' => $cd['cd_qywxid'], 'name' => $cd['cd_name'], 'parentid' => $cd['cd_qywxparentid'], 'cd_id' => $cd_id);
			}
		}

		// @ 需要更新本地 cd_qywxid 字段的 array('cd_id'=>array('cd_qywxid' => id, 'cd_qywxparentid' => parentid), ... ...)
		$update_id_list = array();

		// @ 需要本地添加的部门，但忽略企业微信接口 array(array('cd_name' => '', 'cd_qywxid' => id, 'cd_qywxparentid' => parentid), ... ...)
		$add_local_list = array();

		// @ 需要本地更新部门名称的，但忽略企业微信接口 array(cd_id=>array('cd_name' => ''), ... ...)
		$update_local_list = array();

		// @ 需要添加到微信接口的部门，但本地不添加只更新其对应的cd_qywsid 和cd_qywsparentid array()
		$add_qywx_list = array();

		// 找到存在于微信但本地没有的部门
		foreach ($qywx_department_list as $r) {
			if (!$r['parentid']) {
				// 忽略根部门
				continue;
			}
			if (!isset($local_list[$r['id']])) {
				// 本地不存在此部门

				$cd_id = null;
				$cd = null;

				// 遍历本地是否存在同名的
				$finded = false;
				foreach ($local_department_list as $cd_id => $cd) {
					if (rstrtolower($r['name']) == rstrtolower($cd['cd_name'])) {
						// 存在同名的部门名，则标记更改本地数据对应的 cd_qywxid 字段
						$update_id_list[$cd_id] = array('cd_qywxid' => $r['id'], 'cd_qywxparentid' => $r['parentid']);
						unset($local_no_id_list[$cd_id]);
						$finded = true;
						break;
					}
				}

				if (!$finded) {
					// 未找到本地有同名的部门，则认为微信存在本地不存在，本地需要添加，忽略微信接口
					$add_local_list[] = array('cd_name' => $r['name'], 'cd_qywxid' => $r['id'], 'cd_qywxparentid' => $r['parentid']);
				}
			} else {
				// 本地存在此部门

				if (rstrtolower($r['name']) != rstrtolower($local_list[$r['id']]['name'])) {
					// 如果不同名，则修改本地数据
					$update_local_list[$local_list[$r['id']]['cd_id']] = array('cd_name' => $local_list[$r['id']]['name']);
				}
			}
		}

		// 找到存在于本地但微信没有的
		foreach ($local_no_id_list as $n) {

			// 遍历微信查找是否存在同名的
			$finded = false;
			foreach ($qywx_department_list as $k => $r) {
				if (!$r['parentid']) {
					// 忽略根部门
					continue;
				}
				if (rstrtolower($r['name']) == rstrtolower($n['name'])) {
					// 本地存在同名的，则标记修改本地数据对应的 cd_qywxid 字段
					$update_id_list[$n['cd_id']] = array('cd_qywxid' => $r['id'], 'cd_qywxparentid' => $r['parentid']);
					unset($qywx_department_list[$k]);
					$fined = true;
					break;
				}
			}

			if (!$finded) {
				// 未在微信里找到同名的，则标记为添加到微信接口，本地不添加，只更新id
				$add_qywx_list[$n['cd_id']] = array('name' => $n['name'], 'parentid' => $qywx_addressbook->department_parentid);
			}
		}

		/** 开始进行更新 */

		$serv = &service::factory('voa_s_oa_common_department', array('pluginid' => 0));

		// 更新提交消息
		$msg = array();

		// 更新本地的 cd_qywxid 和 cd_qywxparentid 字段
		if ($update_id_list) {
			foreach ($update_id_list as $_cd_id => $_cd_update) {
				$serv->update($_cd_update, $_cd_id);
			}
			$msg[] = "update local qywxid count: ".count($update_id_list);
			unset($_cd_id, $_cd_update, $update_id_list);
		}

		// 更新本地部门名称
		if ($update_local_list) {
			foreach ($update_local_list as $_cd_id => $_cd_update) {
				$serv->update($_cd_update, $_cd_id);
			}
			$msg[] = "update local name count: ".count($update_local_list);
			unset($_cd_id, $_cd_update, $update_local_list);
		}

		// 在本地添加新部门，但忽略提交到微信接口
		if ($add_local_list) {
			foreach ($add_local_list as $_add_data) {
				$serv->insert($_add_data);
			}
			$msg[] = "add local department count: ".count($add_local_list);
			unset($_add_data, $add_local_list);
		}

		// 添加到微信的部门
		if ($add_qywx_list) {
			foreach ($add_qywx_list as $_cd_id => $_qywx_data) {
				$result = array();
				if ($qywx_addressbook->department_create($_qywx_data, $result)) {
					$serv->update(array('cd_qywxid' => $result['id'], 'cd_parentid' => $_qywx_data['parentid']), $_cd_id);
				}
			}
			$msg[] = "add qywx department count:".count($add_qywx_list);
		}

		$msg[] = 'over - '.rgmdate(startup_env::get('timestamp'), 'Y-m-d H:i:s');
		$this->__output($msg, 'success', true);

		return true;
	}

}
