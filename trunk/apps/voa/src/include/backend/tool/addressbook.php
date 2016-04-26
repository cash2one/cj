<?php
/**
 * 命令行方式管理通讯录数据
 * @uses php tool.php -n addressbook
 * -action 操作动作，默认为：delete，用于同步本地和微信的通讯录数据
 *
 * -action = delete 时，删除操作， -userid = 111,222,333,444 待删除的id，以半角逗号分隔
 *
 * Create By Deepseath
 * $Author$
 * $Id$
 */

class voa_backend_tool_addressbook extends voa_backend_base {

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
	 * 删除指定userid的用户
	 * @return boolean
	 */
	public function ___delete() {
		if (empty($this->__opts['userid'])) {
			return $this->__output('-userid value is illegal.', false);
		}

		$userid = (string)$this->__opts['userid'];

		$addressbook = new voa_wxqy_addressbook();
		$result = array();
		if ($addressbook->user_delete($userid, $result)) {

			$addressbook_serv = &service::factory('voa_s_oa_common_addressbook');
			$addressbook = $addressbook_serv->fetch_by_openid($userid);
			if (!empty($addressbook)) {

				// 删除addressbook表记录
				$addressbook_serv->delete($addressbook['cab_id']);
				if ($addressbook['m_uid']) {
					// 删除member表记录
					$member_serv = &service::factory('voa_s_oa_member');
					$member_serv->delete($addressbook['m_uid']);
				}
			}

			$this->__output('success');
		} else {
			$this->__output($result);
			return false;
		}
	}

}
