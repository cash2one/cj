<?php
/**
 * 企业号基础接口, 非套件
 * Base.php
 * $author$
 */

namespace Common\Common\Wxqy;
use Think\Log;
use Common\Common\Cache;

abstract class Base extends \Com\Wxqy {

	// js api ticket 的默认有效时长
	protected $_jsapi_expires_in = 7200;
	// js api ticket
	protected $_jsapi_ticket = '';
	// js api ticket 失效时间
	protected $_jsapi_ticket_expire = 0;

    // 根据 userid 读取 openid
    const CONVERT_TO_OPENID_URL = 'https://qyapi.weixin.qq.com/cgi-bin/user/convert_openid?access_token=%s&userid=%s&agentid=%d';

    // 根据 userid 读取 openid (企业支付)
    const CONVERT_TO_OPENID_FOR_PAY_URL = 'https://qyapi.weixin.qq.com/cgi-bin/user/convert_openid?access_token=%s&userid=%s';


    // echostr, 鉴权字串
	public $retstr = 'Success';

	public function __construct() {

		parent::__construct();
		$cache = &Cache::instance();
		// 读取 setting 缓存
		$sets = $cache->get('Common.setting');
		$this->_corp_id = isset($sets['corp_id']) ? $sets['corp_id'] : '';
		$this->_corp_secret = isset($sets['corp_secret']) ? $sets['corp_secret'] : '';
		$this->_token = isset($sets['token']) ? $sets['token'] : '';
		$this->_open_id = $this->_corp_id;
		// 读取企业号配置
		cfg('WXQY', load_config(COMMON_PATH.'Conf/wxqy.php'));
		$this->_expires_in = cfg('wxqy.expires_in');
		$this->_access_token_errcode = cfg('wxqy.access_token_errcode');

		// 从缓存中取配置
		$this->_wx_sets = $cache->get('Common.weixin');
	}

	/**
	 * 获取 token
	 *
	 * @param boolean $force 强制重新获取 token
	 * @param boolean $istmp 判断是否测试操作
	 */
	public function get_access_token($force = false) {

		// 套件信息
		$oa_suite = array();
		// 如果套件信息存在, 则按套件规则读取
		if ($this->get_local_suite($oa_suite)) {
			// 如果当前的 corp_id 和套件的不符, 则修改 corp_id
			if (empty($this->_corp_id) && $this->_corp_id != $oa_suite['auth_corpid']) {
				$this->_corp_id = $oa_suite['auth_corpid'];
			}

			// 如果 access_token 未过期
			if (!$force && ! empty($oa_suite['access_token']) && NOW_TIME < $oa_suite['expires']) {
				$this->_access_token = $oa_suite['access_token'];
				$this->_access_token_expires = $oa_suite['expires'];
				return true;
			}

			// 通过企业套件读取 access_token
			$wxqysuite = &\Common\Common\WxqySuite\Service::instance();
			$wxqysuite->get_access_token($oa_suite['suiteid']);
			$this->_access_token = $wxqysuite->access_token;
			$this->_token_expires = $wxqysuite->access_token_expires;
			return true;
		}

		// 通过非套件接口调用, 获取 access token
		return parent::get_access_token($force);
	}

	// 如果非临时操作, 则 token 入库
	protected function _update_access_token() {

		$serv_wx = D('Common/WeixinSetting', 'Service');
		$serv_wx->update_kv(array('access_token' => $this->_access_token, 'token_expires' => $this->_token_expires));

		// 更新 token 临时缓存
		$cache = &Cache::instance();
		$cache->get('Common.weixin', null);
	}

	/**
	 * 获取指定用户的信息
	 *
	 * @param string $code code值
	 * @param int $agentid 应用ID
	 * @return boolean
	 */
	public function get_user_info($code, $agentid = 0) {

		// 应用ID小于 0
		if (0 >= $agentid) {
			// 读取配置
			$agentid = cfg('AGENT_ID');
			// 如果配置的应用ID也小于 0
			if (0 >= $agentid) {
				// 读取插件信息
				$plugin = array();
				if (get_plugin($plugin)) {
					$agentid = $plugin['cp_agentid'];
				}
			}
		}

		// 读取当前用户信息
		if (!parent::get_user_info($code, $agentid)) {
			return false;
		}

		return true;
	}

	/**
	 * 获取 jsapi-ticket
	 *
	 * @param boolean $force 是否强制读取
	 * @return boolean
	 */
	public function get_jsapi_ticket($force = false) {

		// 先读取 access token
		if (!$this->get_access_token()) {
			return false;
		}

		$oa_suite = array();
		// 如果能获取到套件(有套件)
		if ($this->get_local_suite($oa_suite)) {
			// 检查套件jsapi-ticket是否过期
			if (! $force && ! empty($oa_suite['jsapi_ticket']) && ! empty($oa_suite['jsapi_ticket_expire']) && $oa_suite['jsapi_ticket_expire'] >= NOW_TIME) {
				$this->_jsapi_ticket = $oa_suite['jsapi_ticket'];
				$this->_jsapi_ticket_expire = $oa_suite['jsapi_ticket_expire'];
				return true;
			}
		} else { // 无套件
			// 自缓存读取 jsapi-ticket
			$cache = &Cache::instance();
			$sets = $cache->get('Common.weixin');
			if (! $force && ! empty($sets['jsapi_ticket']) && ! empty($sets['jsapi_ticket_expire']) && $sets['jsapi_ticket_expire'] >= NOW_TIME) {
				$this->_jsapi_ticket = $sets['jsapi_ticket'];
				$this->_jsapi_ticket_expire = $sets['jsapi_ticket_expire'];
				return true;
			}
		}

		// 读取 jsapi ticket
		if (!parent::get_jsapi_ticket($force)) {
			return false;
		}

		// ticket 入库
		if (empty($oa_suite)) {
			// 无套件方式
			$serv_wx = D('Common/WeixinSetting', 'Service');
			$serv_wx->update_kv(array(
				'jsapi_ticket' => $this->_jsapi_ticket,
				'jsapi_ticket_expire' => $this->_jsapi_ticket_expire
			));

			// 更新 weixin 临时缓存
			$cache->get('Common.weixin', null);
		} else {
			// 套件方式
			$serv_suite = D('Common/Suite', 'Service');
			$serv_suite->update_by_suiteid($oa_suite['suiteid'], array(
				'jsapi_ticket' => $this->_jsapi_ticket,
				'jsapi_ticket_expire' => $this->_jsapi_ticket_expire
			));
		}

		return true;
	}

	/**
	 * 获取加密配置信息
	 * @return boolean
	 */
	public function get_sets(&$sets) {

		static $s_set;
		// 如果配置信息已经存在
		if (!empty($s_set)) {
			$sets = $s_set;
			return true;
		}

		// 读取套件信息
		$oa_suite = array();
		if ($this->get_local_suite($oa_suite)) {
			// 读取套件配置
			$uc_suite = array();
			if (! $this->get_uc_suite($uc_suite, $oa_suite['cp_suiteid'])) {
				Log::record('error:' . var_export($_GET, true) . "\n" . $xml);
				return false;
			}

			// 解析接收消息
			$sets = array(
				'corp_id' => $this->_corp_id,
				'token' => $uc_suite['su_token'],
				'aes_key' => $uc_suite['su_suite_aeskey']
			);
		} else {
			$cache = &Cache::instance();
			$sets = $cache->get('Common.setting');
		}

		$s_set = $sets;
		return true;
	}

	/**
	 * 获取套件信息
	 * @param array $suite 套件信息
	 * @param string $suiteid 套件ID
	 * @param number $pluginid 插件ID
	 * @param boolean $force 是否强制重新读取
	 * @return boolean
	 */
	public function get_local_suite(&$suite, $suiteid = null, $pluginid = 0, $force =false) {

		static $suites = array();
		// 当前已经有套件, 并且没有任何获取条件, 也不强制重新读取
		if (!empty($suites) && empty($suiteid) && empty($pluginid) && !$force) {
			$suite = reset($suites);
			return true;
		}

		// 如果套件ID为空
		if (empty($suiteid)) {
			$plugin = array();
			// 如果获取插件信息失败
			if (!get_plugin($plugin, $pluginid)) {
				Log::record(L('_ERR_PLUGIN_IS_NOT_EXIST'));
				return false;
			}

			// 如果套件ID为空
			if (empty($plugin['cp_suiteid'])) {
				Log::record(L('_ERR_SUITEID_IS_EMPTY'));
				return false;
			}

			$suiteid = $plugin['cp_suiteid'];
		}

		// 如果套件信息存在
		if (isset($suites[$suiteid]) && !$force) {
			$suite = $suites[$suiteid];
			return true;
		}

		// 初始化 suite
		$serv_suite = D('Common/Suite', 'Service');
		$suite = $serv_suite->get_by_suiteid($suiteid);
		$suites[$suiteid] = $suite;
		return true;
	}

	/**
	 * 根据 suite_id 读取 suite 记录
	 *
	 * @param array $suite 套件信息
	 * @param string $suiteid 套件id
	 * @param boolean $force 是否强制读取
	 * @return boolean
	 */
	public function get_uc_suite(&$suite, $suiteid, $force = false) {

		static $suties;
		// 如果套件信息存在
		if (isset($suites[$suiteid]) && !$force) {
			$sutie = $suites[$suiteid];
			return true;
		}

		// 通过 rpc, 获取套件信息
		$url = cfg('UCENTER_RPC_HOST').'/OaRpc/Rpc/Suite';
		$suite = array();
		if (!\Com\Rpc::query($suite, $url, 'get_by_suiteid', $suiteid)) {
			$suite = array();
			return false;
		}

		// 推入缓存
		$suites[$suiteid] = $suite;

		return true;
	}

    /**
     * 把 userid 转成 openid
     * @param string $openid 用户的 openid
     * @param string $userid 用户的 userid
     * @param number $agentid 应用id
     * @return boolean
     */
    public function convert_to_openid(&$openid, $userid, $agentid = 0) {

        // 先获取 access token
        $this->get_access_token();
        // 生成获取 openid 的 url
        $url = sprintf(self::CONVERT_TO_OPENID_URL, $this->_access_token, $userid, $agentid);
        // 接口调用
        $data = array();
        if (!self::post($data, $url)) {
            return false;
        }

        $openid = $data['openid'];
        $this->_wx_openid = $openid;
        return true;
    }

    /**
     * 把 userid 转成 openid (企业支付)
     * @param string $openid 用户的 openid
     * @param string $userid 用户的 userid
     * @return boolean
     */
    public function convert_to_openid_for_pay(&$openid, $userid) {

        // 先获取 access token
        $this->get_access_token();
        // 生成获取 openid 的 url
        $url = sprintf(self::CONVERT_TO_OPENID_FOR_PAY_URL, $this->_access_token, $userid);
        // 接口调用
        $data = array();
        if (!self::post($data, $url)) {
            return false;
        }

        $openid = $data['openid'];
        $this->_wx_openid = $openid;
        return true;
    }

}
