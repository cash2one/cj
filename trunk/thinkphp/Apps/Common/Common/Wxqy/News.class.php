<?php
/**
 * 微信企业图文混排消息
 * $Author$
 * $Id$
 */

namespace Common\Common\Wxqy;
use Think\Log;

class News {

	// 消息类型
	const MSG_TYPE = 'news';
	// 接口地址
	const POST_URL = 'https://qyapi.weixin.qq.com/cgi-bin/message/send?access_token=%s';
	// service 方法
	protected $_serv;

	public function __construct(&$serv) {

		$this->_serv = $serv;
	}

	/**
	 * 主动给用户发消息
	 * @param array $news 图文混排消息
	 * @param array $to_users 用户的 appid
	 * @param string $agentid 应用型代理id
	 */
	public function post($news, $agentid, $to_users = array(), $to_partys = array()) {

		$agentid = intval($agentid);
		// 数组转成 json 字串
		$json = self::pack($news, $agentid, $to_users, $to_partys);
		$result = array();
		// 接口 URL
		$url = self::POST_URL;
		$this->_serv->create_token_url($url);
		// 读取数据
		if (!rfopen($result, $url, $json, array(), 'POST')) {
			return false;
		}

		// 如果出错了, 则记录日志
		if (0 < (int)$result['errcode']) {
			Log::write('post error: '.var_export($result, true)."\nurl: ".$url."\nparams: ".$json);
			return false;
		}

		return true;
	}

	/**
	 * 把需要发送的数据进行打包
	 * @param string $news
	 * @param string $agentid
	 * @param string|array $to_users
	 * @param string|array $to_partys
	 * @return string
	 */
	public static function pack($news, $agentid, $to_users = array(), $to_partys = array()) {

		// 判断数据是否为单条图文信息
		if (isset($news['title']) && isset($news['description'])) {
			$news = array($news);
		}

		$data = array(
			'touser' => is_array($to_users) ? implode('|', (array)$to_users) : $to_users,
			'toparty' => is_array($to_partys) ? implode('|', (array)$to_partys) : $to_partys,
			'msgtype' => self::MSG_TYPE,
			'agentid' => $agentid,
			'news' => array('articles' => $news)
		);
		// 数组转成json字串
		return rjson_encode($data);
	}
}
