<?php
/**
 * 微信企业文本消息
 * $Author$
 * $Id$
 */

class voa_wxqy_text {
	/** 消息类型 */
	const MSG_TYPE = 'text';
	/** 被动响应模板 */
	const XML_TPL = '<xml>
		<ToUserName><![CDATA[%s]]></ToUserName>
		<FromUserName><![CDATA[%s]]></FromUserName>
		<CreateTime>%d</CreateTime>
		<MsgType><![CDATA[%s]]></MsgType>
		<Content><![CDATA[%s]]></Content>
		</xml>';
	/** 接口地址 */
	const POST_URL = 'https://qyapi.weixin.qq.com/cgi-bin/message/send?access_token=%s';
	/** service 方法 */
	protected $_serv;

	public function __construct(&$serv) {
		$this->_serv = $serv;
	}

	/**
	 * 主动发送消息给用户
	 * to_user_name => ToUserName 接收方帐号（收到的OpenID）
	 * from_user_name => FromUserName 开发者微信号
	 * create_time => CreateTime 消息创建时间 （整型）
	 * content => Content 回复的消息内容（换行：在content中能够换行，微信客户端就支持换行显示）
	 */
	public function response($data, $encode = false) {
		$xml = sprintf(
			self::XML_TPL, $data['to_user_name'], $data['from_user_name'], $data['create_time'],
			self::MSG_TYPE, $data['content']
		);

		if (true == $encode) {
			$plugins = voa_h_cache::get_instance()->get('plugin', 'oa');
			$suiteid = '';
			foreach ($plugins as $_p) {
				if ($_p['cp_pluginid'] == startup_env::get('pluginid')) {
					$suiteid = $_p['cp_suiteid'];
					break;
				}
			}
			// 初始化 suite
			$serv_suite = &service::factory('voa_s_oa_suite');
			// 如果未读到
			$oa_suite = $serv_suite->fetch_by_suiteid($suiteid);

			// 解析接收消息
			$sets = voa_h_cache::get_instance()->get('setting', 'oa');
			if ($oa_suite) {

				// 读取套件配置]
				if (!$this->_serv->get_uc_suite($suiteid)) {
					return false;
				}

				// 解析接收消息
				$sets = array(
					'corp_id' => $sets['corp_id'],
					'token' => $this->_serv->uc_suite['su_token'],
					'aes_key' => $this->_serv->uc_suite['su_suite_aeskey']
				);
			}

			list($result, $xml) = qywx_callback::instance($sets)->to_tx($xml);
			logger::error($xml);
		}

		echo $xml;
		return true;
	}

	/**
	 * 主动给用户发消息
	 * @param string $content 消息文本
	 * @param array $to_users 用户的 appid
	 * @param string $token access token 值
	 * @param string $agentid 应用型代理id
	 */
	public function post($content, $agentid, $token, $to_users = array(), $to_partys = array()) {
		$agentid = intval($agentid);
		/** 数组转成 json 字串 */
		$json = self::pack($content, $agentid, $to_users, $to_partys);
		$res = array();
		if (!voa_h_func::get_json_by_post($res, sprintf(self::POST_URL, $token), $json)) {
			return false;
		}

		if (0 < (int)$res['errcode']) {
			logger::error('post error: '.var_export($res, true).$json);
			return false;
		}

		return true;
	}

	/**
	 * 把需要发送的数据进行打包
	 * @param string $content
	 * @param string $agentid
	 * @param string|array $to_users
	 * @param string|array $to_partys
	 * @return string
	 */
	public static function pack($content, $agentid, $to_users = array(), $to_partys = array()) {
		$data = array(
			'touser' => is_array($to_users) ? implode('|', (array)$to_users) : $to_users,
			'toparty' => is_array($to_partys) ? implode('|', (array)$to_partys) : $to_partys,
			'msgtype' => self::MSG_TYPE,
			'agentid' => $agentid,
			'text' => array('content' => $content)
		);
		/** 数组转成json字串 */
		return rjson_encode($data);
	}
}
