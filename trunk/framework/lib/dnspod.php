<?php
/**
 * dnspod 类
 *
 * Author:Arice
 *
 * $Id: dnspod.class.php 286 2013-12-24 08:00:26Z xiaomi $
 */

/**
 *
<sql>
CREATE TABLE IF NOT EXISTS `cdb_dnspod` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `zone` int(10) unsigned NOT NULL COMMENT '主域名id',
  `name` char(64) DEFAULT NULL COMMENT '主机记录',
  `type` enum('A','AAAA','CNAME','HINFO','MX','NAPTR','NS','PTR','RP','SRV','TXT') DEFAULT NULL COMMENT '域名类型',
  `data` char(128) DEFAULT NULL COMMENT '记录值',
  `ttl` int(10) unsigned NOT NULL DEFAULT '600' COMMENT 'TTL',
  `record_id` bigint(13) unsigned NOT NULL DEFAULT '0' COMMENT 'dnspod记录id',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '0待绑定,1已绑定,10待删除,20待修改',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后更新时间',
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  KEY `rr` (`zone`,`name`,`type`,`data`),
  KEY `status`(`status`)
) ENGINE=MyISAM DEFAULT CHARSET=gbk;

CREATE TABLE IF NOT EXISTS `cdb_dnspodapi` (
  `dateline` int(10) unsigned NOT NULL COMMENT '调用api时间',
  KEY `dateline` (`dateline`)
) ENGINE=MyISAM DEFAULT CHARSET=gbk;
</sql>

<code>
	try{
		$a = new dnspodHaotui();
		// $b = $a->version();
		// $b = $a->addCname('sfjy', 'a1.haotui.com');
		// $b = $a->addCname('sfmm', 'a2.haotui.com');
		// $c = $a->addCname('sfnn', 'a3.haotui.com');
		$b = $a->rmCname('sfnn');
		var_dump($b, $c);
	} catch (Exception $e) {
		var_dump('error',$e);
	}
</code>
 */


class dnspod {

	/**
	 * _instance
	 *
	 * @var object
	 */
	protected static $_instance = null;

	/** 用户名/密码/zoneid */
	private $__user = '';
	private $__passwd = '';
	private $__zoneid = 0;

	/**
	 * 返回的数据格式，可选，默认为xml，建议用json，暂时只支持json
	 */
	private $format = 'json';

	/**
	 * 返回的错误语言，可选，默认为en，建议用cn
	 */
	private $lang = 'cn';

	/**
	 * 没有数据时是否返回错误，可选，默认为yes，建议用no
	 */
	private $error_on_empty = 'no';

	/**
	 * 用户的ID，可选，仅代理接口需要， 用户接口不需要提交此参数
	 */
	private $user_id = '';

	/**
	 * api 地址
	 */
	private $api_url = 'https://dnsapi.cn/';

	/**
	 * UserAgent 程序英文名称/版本(联系邮箱)，比如：MJJ DDNS Client/1.0.0 (test@dnspod.com)。
	 */
	private $user_agent = 'Haotui DDNS Client/1.0 (tkxxd@qq.com)';

	/**
	 * &get_instance
	 * 获取一个短信发送类的实例
	 *
	 * @return object
	 */
	public static function &get_instance() {

		if (!self::$_instance) {
			self::$_instance = new dnspod();
		}

		return self::$_instance;
	}

	/**
	-1 登陆失败
	-2 API使用超出限制
	-3 不是合法代理 (仅用于代理接口)
	-4 不在代理名下 (仅用于代理接口)
	-7 无权使用此接口
	-8 登录失败次数过多，账号被暂时封禁
	-99 此功能暂停开放，请稍候重试
	1 操作成功
	2 只允许POST方法
	3 未知错误
	6 用户ID错误 (仅用于代理接口)
	7 用户不在您名下 (仅用于代理接口)

	curl -X POST https://dnsapi.cn/Info.Version -d 'login_email=api@dnspod.com&login_password=password&format=json'

	返回
	{
	"status": {
		"code": "1",
		"message": "4.6",
		"created_at": "2012-09-10 11:20:39"
	}
	*/

	public function __construct() {

		if (!function_exists('curl_init')) {
			throw new Exception('API need the cURL PHP extension.');
		}

		if (!function_exists('json_decode')) {
			throw new Exception('API need the JSON PHP extension.');
		}

		if (!function_exists('iconv')) {
			throw new Exception('API need the iconv PHP extension.');
		}

		$cfg_name = startup_env::get('cfg_name');
		$this->__user = config::get($cfg_name.'.dnspod.user');
		$this->__passwd = config::get($cfg_name.'.dnspod.passwd');
		$this->__zoneid = config::get($cfg_name.'.dnspod.zoneid');
	}

	/**
	 * 发送请求
	 * @param string $url url地址
	 * @param array $params 参数
	 * @return boolean|mixed
	 */
	private function _post($url, $params) {
		if (!$url || !is_array($params)) {
			return false;
		}

		$ch = @curl_init();
		if (!$ch) {
			return false;
		}

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
		curl_setopt($ch, CURLOPT_USERAGENT, $this->user_agent);
		$result = curl_exec($ch);
		curl_close($ch);

		return $result;
	}

	/**
	 * 调用api
	 * @param string $api 接口方法
	 * @param array $params 参数
	 * @param array &$result 返回结果
	 * @throws exception
	 * @return Ambigous <multitype:, array, string>
	 */
	protected function _call_api($api, $params, &$result) {
		if (!$api || !is_array($params)) {
			throw new Exception('内部错误：参数错误');
			return false;
		}

		/** 域名id */
		if (empty($params['domain_id'])) {
			$params['domain_id'] = $this->__zoneid;
		}

		$url = $this->api_url.$api;

		$params = array_merge($params, array(
			'login_email' => $this->__user,
			'login_password' => $this->__passwd,
			'format' => $this->format,
			'lang' => $this->lang,
			'error_on_empty' => $this->error_on_empty
		));

		$html = $this->_post($url, $params);
		if (!$html) {
			throw new Exception('内部错误：调用失败');
			return false;
		}

		$result = @json_decode($html, 1);
		if (!is_array($result)) {
			throw new Exception('内部错误：返回错误');
			return false;
		}

		logger::error($html);
		if ($result['status']['code'] != 1) {
			throw new Exception($result['status']['message']);
			return false;
		}

		return true;
	}

	public function version(){

		$result = $this->_call_api('Info.Version', array());
		return $result['status']['message'];
	}

	/**
	 * create_record
	 * 添加记录
	 *
	 * @param array $params 请求参数
	 *  + domain_id 域名ID, 必选
	 *  + sub_domain 主机记录, 如 www, 默认@，可选
	 *  + record_type 记录类型，通过API记录类型获得，大写英文，比如：A, 必选
	 *  + record_line 记录线路，通过API记录线路获得，中文，比如：默认, 必选
	 *  + value 记录值, 如 IP:200.200.200.200, CNAME: cname.dnspod.com., MX: mail.dnspod.com., 必选
	 *  + mx {1-20} MX优先级, 当记录类型是 MX 时有效，范围1-20, MX记录必选
	 *  + ttl {1-604800} TTL，范围1-604800，不同等级域名最小值不同, 可选
	 *
	 * @return array
	 *  + status
	 *  ++ code 1
	 *  ++ message 操作已经成功完成
	 *  ++ created_at 2013-11-24 08:51:17
	 *  + record
	 *  ++ id 记录ID
	 *  ++ name 记录值
	 *  ++ status enable
	 */
	public function create_record($params, &$result) {

		return $this->_call_api('Record.Create', $params, $result);
	}

	/**
	 * remove_record
	 * 删除记录
	 *
	 * @param array $params 请求参数
	 *  + domain_id 域名ID, 必选
	 *  + record_id 记录ID，必选
	 *
	 * @return array
	 *  + status
	 *  ++ code 1
	 *  ++ message 操作已经成功完成
	 *  ++ created_at 2013-11-24 08:51:17
	 */
	public function remove_record($params, &$result) {

		return $this->_call_api('Record.Remove', $params, $result);
	}

	/**
	 * modifyRecord
	 * 修改记录
	 *
	 * @param array $params 请求参数
	 *  + domain_id 域名ID, 必选
	 *  + record_id 记录ID，必选
	 *  + sub_domain 主机记录, 如 www, 默认@，可选
	 *  + record_type 记录类型，通过API记录类型获得，大写英文，比如：A, 必选
	 *  + record_line 记录线路，通过API记录线路获得，中文，比如：默认, 必选
	 *  + value 记录值, 如 IP:200.200.200.200, CNAME: cname.dnspod.com., MX: mail.dnspod.com., 必选
	 *  + mx {1-20} MX优先级, 当记录类型是 MX 时有效，范围1-20, MX记录必选
	 *  + ttl {1-604800} TTL，范围1-604800，不同等级域名最小值不同, 可选
	 *
	 * @return array
	 *  + status
	 *  ++ code 1
	 *  ++ message 操作已经成功完成
	 *  ++ created_at 2013-11-24 08:51:17
	 *  + record
	 *  ++ id 记录ID
	 *  ++ name 记录值
	 *  ++ status enable
	 */
	public function modify_record($params, &$result) {

		return $this->_call_api('Record.Modify', $params, $result);
	}

}
