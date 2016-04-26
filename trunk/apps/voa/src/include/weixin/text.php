<?php
/**
 * 微信文本消息
 * $Author$
 * $Id$
 */

class voa_weixin_text {
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
	const POST_URL = 'https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=%s';
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
	public function response($data) {
		$xml = sprintf(
			self::XML_TPL, $data['to_user_name'], $data['from_user_name'], $data['create_time'],
			self::MSG_TYPE, $data['content']
		);
		echo $xml;
		return true;
	}

	/**
	 * 主动给用户发消息
	 * @param string $content 消息文本
	 * @param string $to 用户的 appid
	 * @param string $token access token 值
	 */
	public function post($content, $to, $token) {
		$data = array(
			'touser' => $to,
			'msgtype' => self::MSG_TYPE,
			'text' => array('content' => $content)
		);
		/** 数组转成 json 字串 */
		$json = rjson_encode($data);
		$res = array();
		if (!$this->_serv->post($res, sprintf(self::POST_URL, $token), $json)) {
			return false;
		}

		return true;
	}
}
