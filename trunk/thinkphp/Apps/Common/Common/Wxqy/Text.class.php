<?php
/**
 * 微信企业文本消息
 * $Author$
 * $Id$
 */

namespace Common\Common\Wxqy;
use Think\Log;

class Text {

	// 消息类型
	const MSG_TYPE = 'text';
	// 被动响应模板
	const XML_TPL = '<xml>
		<ToUserName><![CDATA[%s]]></ToUserName>
		<FromUserName><![CDATA[%s]]></FromUserName>
		<CreateTime>%d</CreateTime>
		<MsgType><![CDATA[%s]]></MsgType>
		<Content><![CDATA[%s]]></Content>
		</xml>';
	// 接口地址
	const POST_URL = 'https://qyapi.weixin.qq.com/cgi-bin/message/send?access_token=%s';
	// service 方法
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

		// 格式化
		$xml = sprintf(
			self::XML_TPL, $data['to_user_name'], $data['from_user_name'], $data['create_time'],
			self::MSG_TYPE, $data['content']
		);

		// 如果需要加密
		if (true == $encode) {
			// 获取加密配置
			$sets = array();
			if (!$this->_serv->get_set($sets)) {
				return false;
			}

			// 加密
			list($result, $xml) = qywx_callback::instance($sets)->to_tx($xml);
			Log::write($xml);
		}

		echo $xml;
		return true;
	}

	/**
	 * 主动给用户发消息
	 * @param string $content 消息文本
	 * @param array $to_users 用户的 appid
	 * @param string $agentid 应用型代理id
	 */
	public function post($content, $agentid, $to_users = array(), $to_partys = array()) {

		$agentid = intval($agentid);
		// 数组转成 json 字串
		$json = self::pack($content, $agentid, $to_users, $to_partys);

		$res = array();
		// 接口URL
		$url = self::POST_URL;
		$this->_serv->create_token_url($url);

		if (!rfopen($res, $url, $json, array(), 'POST')) {
			return false;
		}

		// 如果返回的错误码 > 0
		if (0 < (int)$res['errcode']) {
			Log::write('post error: '.var_export($res, true)."\nurl: ".$url."\nparams: ".$json);
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

		// 数组转成json字串
		return rjson_encode($data);
	}
}
