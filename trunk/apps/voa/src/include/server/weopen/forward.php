<?php
/**
 * voa_server_weopen_forward
 * 微信开放平台接口服务基类
 *
 * $Author$
 * $Id$
 */

class voa_server_weopen_forward extends voa_server_base {

	/**
	 * __construct
	 * 接口服务构造方法
	 *
	 * @param  mixed $serverName
	 * @return void
	 */
	public function __construct($server_name) {

		// 语言默认配置
		language::set_lang(config::get(startup_env::get('app_name').'.language'));
		language::load_lang('core');

		// 数据库配置初始化
		voa_h_conf::init_db();

		parent::__construct($server_name);

		// 记录请求日志
		$this->_log();
	}

	// 中断
	protected function _wx_exit($msg) {

		if (!empty($msg)) {
			echo $msg;
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
			// 解析消息
			$weserv = voa_weixinopen_service::instance();
			// check signature
			if (!$weserv->check_signature()) {
				logger::error("check signature error:".var_export($_GET, true));
				$this->_wx_exit();
			}

			// 如果解析结果为 false, 说明消息无效, 则直接退出
			$result = $weserv->recv();
			if (empty($result)) {
				logger::error("recv error.");
				$this->_wx_exit();
			}

			// 根据不同的消息类型进行处理
			switch ($weserv->info_type) {
				case 'component_verify_ticket':
					$this->_request['method'] = 'woevent.ticket';
					break;
				case 'unauthorized':
					$this->_request['method'] = 'woevent.unauthorized';
					break;
				default:
					$this->_wx_exit();
					break;
			}

			// 配置路径
			$app_name = startup_env::get('app_name');
			$this->_config_path = $app_name.'.rpc.'.strtolower($this->_server_name);
		}

		return $this->_request;
	}
}
