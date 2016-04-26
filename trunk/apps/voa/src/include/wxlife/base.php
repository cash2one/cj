<?php
/**
 * 微生活接口基类
 * $Author$
 * $Id$
 */

class voa_wxlife_base {
	/** 获取用户信息的接口地址 */
	const GET_USER_URL = "http://open.life.qq.com/wticket.php";
	/** API 接口地址 */
	const API_URL = "http://open.life.qq.com/api.php";
	/** 接口版本 */
	const VER = '1.0';
	/** 微生活商家id */
	protected $_cardid;
	/** 微生活密钥 */
	protected $_skey;
	/** 返回的数据格式 */
	protected $_format = 'JSON';
	/** 用户信息 */
	public $userinfo;

	public function __construct() {
		$this->_cardid = config::get('voa.wxlife.cardid');
		$this->_skey = config::get('voa.wxlife.skey');
	}

	/** 获取用户信息 */
	protected function get_user() {
		$c = controller_request::get_instance();
		$wticket = $c->get('wticket');
		if (empty($wticket)) {
			return false;
		}

		if (!empty($this->userinfo)) {
			return true;
		}

		/** post 数据 */
		$post = $this->_get_pub_param_of_post();
		$post['method'] = 'weixin.getUser';
		$post['args'] = array(
			'wticket' => $c->get('wticket'),
			'fields' => array('nickname', 'gender', 'boundQQ', 'appid', 'openid', 'subscribe', 'appname', 'cardno', 'cardsn')
		);
		/** 取数据 */
		$ret = $this->fetch_by_sock(self::GET_USER_URL, $post);
		$this->userinfo = $ret->result;

		return empty($this->userinfo) ? false : true;
	}

	/**
	 * 发送模板消息
	 * @param string $openid 目标用户的 openid
	 * @param string $tplid 模板id
	 * @param array $data 模板数据
	 */
	protected function send_msg($openid, $tplid, $data) {
		$post = $this->_get_pub_param_of_post();
		$post['method'] = 'user.sendMessage';
		$post['args'] = array(
			'cardid' => $this->_cardid,
			'openid' => $openid,
			'tplid' => $tplid,
			'data' => $data
		);
		/** 取数据 */
		$result = $this->fetch_by_sock(self::API_URL, $post);
		/** 如果返回错误, 则 */
		if (!empty($result) && 0 != $result->errCode) {
			logger::error(http_build_query($result).'|'.$openid.'|'.$tplid.'|'.var_export($data, true));
			return false;
		}

		return empty($result) ? false : true;
	}

	/** 解析字串 */
	protected function fetch_by_sock($url, $post = array()) {
		$ret = null;
		/** 获取数据 */
		$snoopy = new snoopy();
		$result = $snoopy->submit($url, $post);
		/** 如果读取错误 */
		if (!$result || 200 != $snoopy->status) {
			logger::error('$snoopy->submit error: '.$url.'|'.$result.'|'.$snoopy->status);
			return false;
		}

		if (empty($snoopy->results)) {
			logger::error($url."\t".http_build_query($post));
			return $ret;
		}

		/** 解析返回数据 */
		switch ($this->_format) {
			case 'JSON':$ret = json_decode($snoopy->results);break;
			default:$ret = $snoopy->results;break;
		}

		/** 如果解析后的值为空, 则 */
		if (empty($ret)) {
			logger::error($url."\t".http_build_query($post)."\t".$snoopy->results);
		}

		return $ret;
	}

	/** 接口的公共参数 */
	protected function _get_pub_param_of_post() {
		return array(
			'ts' => startup_env::get('timestamp'),
			'format' => $this->_format,
			'client' => $this->_cardid,
			'sig' => $this->_get_sig(),
			'ver' => self::VER
		);
	}

	/** 获取 sig */
	protected function _get_sig() {
		return md5($this->_cardid.startup_env::get('timestamp').$this->_skey);
	}

	/** set 方法 */
	public function set_cardid($cardid) {
		$this->_cardid = $cardid;
	}

	public function set_skey($skey) {
		$this->_skey = $skey;
	}
}
