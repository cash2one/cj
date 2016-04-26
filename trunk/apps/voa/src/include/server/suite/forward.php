<?php
/**
 * voa_server_suite_forward
 * 微信企业号套件接口服务基类
 *
 * $Author$
 * $Id$
 */

class voa_server_suite_forward extends voa_server_base {

	/**
	 * __construct
	 * 接口服务构造方法
	 *
	 * @param  mixed $serverName
	 * @return void
	 */
	public function __construct($server_name) {

		/** 语言默认配置 */
		language::set_lang(config::get(startup_env::get('app_name').'.language'));
		language::load_lang('core');

		parent::__construct($server_name);

		/** 记录请求日志 */
		$this->_log();
	}

	/** 中断 */
	protected function _wx_exit($msg) {

		if (!empty($msg)) {
			echo $msg;
		} else {
			$wxserv = voa_wxqysuite_service::instance();
			logger::error($wxserv->retstr);
			echo $wxserv->retstr;
		}

		exit;
	}

	/**
	 * _output_result
	 *
	 * @param  mixed $res
	 * @return void
	 */
	protected function _output_result($res) {

		$this->_wx_exit(is_bool($res) ? '' : $res);
	}

	/**
	 * _get_parameters
	 * 获取客户端提交参数
	 *
	 * @return array
	 */
	protected function _get_parameters() {

		if (!$this->_request) {
			$this->_request['args'] = array();
			/** 解析消息 */
			$wxserv = voa_wxqysuite_service::instance();
			/** 验证消息的有效性, 无效则直接退出 */
			if (!$wxserv->check_signature()) {
				logger::error("check signature error[{$_SERVER['HTTP_HOST']}]:".var_export($_GET, true));
				$this->_wx_exit();
			}

			/** 如果解析结果为 false, 说明消息无效, 则直接退出 */
			$result = $wxserv->recv();
			if (empty($result)) {
				logger::error("recv error[{$_SERVER['HTTP_HOST']}].");
				$this->_wx_exit();
			}

			/** 参数 */
			$this->_request['args'] = $result;

			/** 根据不同的参数, 组织成不同的调用方法 */
			switch ($wxserv->info_type) {
				/** ticket 消息 */
				case 'suite_ticket':
					$this->_request['method'] = 'wxqysuite.suite_ticket';
					break;
				/** change auth 消息 */
				case 'change_auth':
					$this->_request['method'] = 'wxqysuite.change_auth';
					break;
				/** cancel auth 消息 */
				case 'cancel_auth':
					$this->_request['method'] = 'wxqysuite.cancel_auth';
					break;
				/** 普通消息 */
				default:$this->_wx_exit();break;
			}

			/** 配置路径 */
			$app_name = startup_env::get('app_name');
			$this->_config_path = $app_name.'.rpc.'.strtolower($this->_server_name);
		}

		return $this->_request;
	}
}
