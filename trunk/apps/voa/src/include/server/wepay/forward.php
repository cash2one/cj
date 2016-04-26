<?php
/**
 * voa_server_wepay_forward
 * 微信支付接口服务基类
 *
 * $Author$
 * $Id$
 */

class voa_server_wepay_forward extends voa_server_base {

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

		/** 数据库配置初始化 */
		voa_h_conf::init_db();

		parent::__construct($server_name);

		/** 记录请求日志 */
		$this->_log();
	}

	/** 中断 */
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
			/** 解析消息 */
			$c = controller_request::get_instance();
			$act = (string)$c->get('act');
			switch ($act) {
				case 'notify.order':
					$this->_request['method'] = 'wepay_notify.order';
					break;
				default: $this->_request['method'] = 'wepay_notify.order'; // 临时这么用
			}

			/** 配置路径 */
			$app_name = startup_env::get('app_name');
			$this->_config_path = $app_name.'.rpc.'.strtolower($this->_server_name);
		}

		return $this->_request;
	}
}
