<?php
/**
 * voa_server_weixin_forward
 * 微信接口服务基类
 *
 * $Author$
 * $Id$
 */

class voa_server_weixin_forward extends voa_server_base {

	/**
	 * __construct
	 * 接口服务构造方法
	 *
	 * @param  mixed $serverName
	 * @return void
	 */
	public function __construct($server_name) {

		parent::__construct($server_name);

		/** 记录请求日志 */
		$this->_log();

		/** 数据库配置初始化 */
		voa_h_conf::init_db();
	}

	/** 中断 */
	protected function _wx_exit($msg) {
		$c = controller_request::get_instance();
		$echostr = $c->get('echostr');
		$echostr = trim($echostr);
		if (empty($echostr)) {
			echo empty($msg) ? ' ' : $msg;
		} else {
			echo $echostr;
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
		$this->_wx_exit();
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
			$wxserv = voa_weixin_service::instance();
			/** 验证消息的有效性, 无效则直接退出 */
			if (!$wxserv->check_signature()) {
				logger::error("check signature error:".var_export($_GET, true));
				$this->_wx_exit();
			}

			/** 如果解析结果为 false, 说明消息无效, 则直接退出 */
			$result = $wxserv->recv();
			if (empty($result)) {
				logger::error("recv error.");
				$this->_wx_exit();
			}

			$this->_request['args']['openid'] = $wxserv->from_user_name;
			/** 根据 openid 读取用户信息 */
			$servm = &service::factory('voa_s_oa_member', array('pluginid' => 0));
			$user = $servm->fetch_by_openid($wxserv->from_user_name);

			/** 根据不同的参数, 组织成不同的调用方法 */
			switch ($wxserv->msg_type) {
				/** 事件消息 */
				case 'event':
					$this->_request['method'] = 'wxevent.'.$wxserv->event;
					break;
				/** 普通消息 */
				default:$this->_request['method'] = 'wxmsg.'.$wxserv->msg_type;break;
			}

			/** 如果没有该用户, 则发注册链接 */
			if (empty($user) && 'wxevent.subscribe' != $this->_request['method']) {
				voa_weixin_wxmsg::instance()->register_link($wxserv->from_user_name);
				$this->_wx_exit();
			}

			$this->_request['args']['user'] = $user;
			/** 配置路径 */
			$app_name = startup_env::get('app_name');
			$this->_config_path = $app_name.'.rpc.'.strtolower($this->_server_name);
		}

		return $this->_request;
	}
}
