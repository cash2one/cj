<?php
/**
 * Menu
 * 微信企业/应用代理菜单
 * $Author$
 * $Id$
 */

namespace Common\Common\Wxqy;
use Think\Log;
use Common\Common\Cache;
use Com\Validator;

class Menu {

	// 应用代理菜单：创建菜单的接口 URL
	const MENU_CREATE_URL = 'https://qyapi.weixin.qq.com/cgi-bin/menu/create?access_token=%s&agentid=%s';
	// - 应用代理菜单：删除菜单的接口 URl
	const MENU_DELETE_URL = 'https://qyapi.weixin.qq.com/cgi-bin/menu/delete?access_token=%s&agentid=%s';
	// - 应用代理菜单：获取菜单列表的接口 URL
	const MENU_GET_URL = 'https://qyapi.weixin.qq.com/cgi-bin/menu/get?access_token=%s&agentid=%s';

	// 主菜单最多允许创建的数量
	private $_button_count_max = 5;
	// 子菜单最多允许创建的数量
	private $_sub_button_count_max = 5;
	// 菜单的响应动作类型
	private $_types = array(
		'view', 'click', 'scancode_push', 'scancode_waitmsg', 'pic_sysphoto',
		'pic_photo_or_album', 'pic_weixin', 'location_select'
	);
	// 主菜单标题长度限制 array(长度判断类型byte|count, min, max)
	private $_button_name_length = array('byte', 1, 16);
	// 子菜单标题长度限制 array(长度判断类型byte|count, min, max)
	private $_sub_button_name_length = array('byte', 1, 40);
	// 菜单的响应动作类型：click类型，需要的 key 的长度限制 array(长度判断类型byte|count, min, max)
	private $_key_length = array('byte', 1, 128);
	// 菜单的响应动作类型：view类型，需要的 url 的长度限制 array(长度判断类型byte|count, min, max)
	private $_url_length = array('byte', 1, 256);
	// 菜单最多允许的级数
	private $_menu_level_max = 2;
	// 请求错误信息，一般用于调试
	public $menu_error = '';
	// service 方法
	protected $_serv;
	// 配置信息
	protected $_sets = array();
	// 企业号接口错误
	protected $_wxqy_errors = null;

	// 初始化
	public function __construct(&$serv) {

		$this->_serv = $serv;
		$this->_wxqy_errors = cfg('wxqy_errors');
		// 读取配置信息
		$cache = &Cache::instance();
		$this->_sets = $cache->get('Common.setting');
	}

	/**
	 * 为指定的应用代理创建菜单
	 * @param string $agentid 应用代理id
	 * @param array $data 菜单数据数组
	 * @param number $pluginid 插件id
	 * @return boolean
	 */
	public function create($menus = array(), $agentid, $pluginid = 0) {

		// 实际访问的接口 URl
		$url = self::MENU_CREATE_URL;
		$this->_serv->create_token_url($url, $agentid);

		// 构造并检查菜单数据
		if (!$this->_check_menu($menus, $pluginid)) {
			E(L('_ERR_MENU_INVALID'));
			return false;
		}

		// 重新包装以适应接口要求的数据
		$menus = array('button' => $menus);
		// 提交请求并获取结果
		$result = array();
		if (!rfopen($result, $url, rjson_encode($menus), array(), 'POST')) {
			// 请求出错
			if ($this->_wxqy_errors['agentid'] == $result['errcode']) {
				$errmsg = '应用ID填写错误';
				E(L('_ERR_API_REQUEST_FAILED'));
			}

			if ($this->_wxqy_errors['addressbook'] == $result['errcode']) {
				E(L('_ERR_API_NO_ADDRBOOK_PERMISSION'));
			}

			return false;
		}

		return true;
	}

	/**
	 * 删除指定应用代理的菜单
	 * @param string $agentid 应用ID
	 * @return boolean
	 */
	public function delete($agentid) {

		// 实际访问的接口url
		$url = self::MENU_DELETE_URL;
		$this->_serv->create_token_url($url, $agentid);
		// 提交请求并获取结果
		$data = array();
		if (!rfopen($data, $url)) {
			return false;
		}

		return true;
	}

	/**
	 * 获取指定应用代理的菜单数据
	 * @param array $data <strong style="color:red">(引用结果)</strong>请求到的菜单数据
	 * @param string $agentid 应用代理id
	 * @return boolean
	 */
	public function get(&$data, $agentid) {

		// 实际访问的接口url
		$url = self::MENU_GET_URL;
		$this->_serv->create_token_url($url, $agentid);
		// 提交请求并获取结果
		if (!rfopen($data, $url)) {
			return false;
		}

		return true;
	}

	/**
	 * 检查并构造创建应用代理菜单请求需要的数据
	 * @param array $menus 代理菜单数据
	 * @param number $pluginid 插件id
	 * @param number $level 菜单级别
	 * @return boolean
	 */
	protected function _check_menu(&$menus, $pluginid = 0, $level = 0) {

		// 菜单数据不能为空
		if (empty($menus)) {
			E('_ERR_MENU_IS_EMPTY');
			return false;
		}

		// 菜单级数大于最大级数
		if ($level > $this->_menu_level_max) {
			E('_ERR_MENU_LEVEL_INVALID');
			return false;
		}

		if (0 < $level) {
			// 判断子菜单数是否超出
			if (count($menus) > $this->_sub_button_count_max) {
				E(L('_ERR_MENU_SUB_BTN_INVALID', array('max' => $this->_sub_button_count_max)));
				return false;
			}
		} else {
			// 判断主菜单数是否超出
			if (count($menus) > $this->_button_count_max) {
				E(L('_ERR_MENU_MAIN_BTN_INVALID', array('max' => $this->_button_count_max)));
				return false;
			}
		}

		// 遍历验证菜单项
		foreach ($menus as $_k => &$_item) {
			// 如果菜单项不符合规则
			if (!$this->_check_menu_item($_item, $pluginid, $level)) {
				unset($menus[$_k]);
			}
		}

		return true;
	}

	/**
	 * 检查并整理菜单项目
	 * @param array $item 菜单数据
	 * @return array
	 */
	protected function _check_menu_item(&$item, $pluginid = 0, $level = 0) {

		if (empty($item['sub_button']) && (!isset($item['type']) || !in_array($item['type'], $this->_types))) {
			// 如果没有下级菜单，则检查菜单类型，未定义菜单的响应动作类型 或 类型非指定范围 则忽略
			E(L('_ERR_MENU_TYPE_INVALID', array('type' => $item['type'])));
			return false;
		}

		// 验证菜单标题长度
		if ($level > 0) { // 子菜单
			$name_length_rule = $this->_sub_button_name_length;
		} else { // 主菜单
			$name_length_rule = $this->_button_name_length;
		}

		// 检查菜单名称长度
		if (!isset($item['name']) || !$this->__validator_length($item['name'], $name_length_rule)) {
			E(L('_ERR_MENU_NAME_LENGTH_INVALID', array('name' => $item['name'])));
			return false;
		}

		// 检查子菜单是否规范
		if (empty($item['sub_button']) || !$this->_check_menu($item['sub_button'], $pluginid, $level + 1)) {
			// 如果下级菜单未定义 或 验证失败则移除下级菜单
			unset($item['sub_button']);
		}

		// 如果不存在子菜单按钮
		if (empty($item['sub_button'])) {
			// 验证菜单的响应动作类型值
			switch ($item['type']) {
				case 'view':
					// view 类型的 url 参数验证
					if (!isset($item['url']) || !is_scalar($item['url']) || !$this->__validator_length($item['url'], $this->_url_length)) {
						E(L('_ERR_MENU_URL_UNDEFINED', array('name' => $item['name'])));
						return false;
					}

					// 生成 URL
					$item['url'] = str_ireplace(
						array('{domain_url}', '{pluginid}'),
						array(cfg('PROTOCAL').$this->_sets['domain'], $pluginid ? $pluginid : ''),
						$item['url']
					);


					// 移除 view 类型不需要的参数 key
					unset($item['key']);
					break;
				case 'click':
					// click 类型的 key 参数验证
					if (!isset($item['key']) || !$this->__validator_length($item['key'], $this->_key_length)) {
						E(L('_ERR_MENU_KEY_UNDEFINED', array('name' => $item['name'])));
						return false;
					}

					// 移除click类型不需要的参数url
					unset($item['url']);
					break;
				case 'scancode_push':
					unset($item['url']);
					break;
				default: break;
			}
		} else {
			// 如果存在子菜单，则尝试移除其他参数
			unset($item['type'], $item['url'], $item['key']);
		}

		return true;
	}

	/**
	 * 验证微信要求的字符长度
	 * @param string $string 待验证的字符串
	 * @param array $rule 验证规则 array(unit, min, max)
	 * @param string $error_msg <strong style="color:red">(引用结果)</strong>验证错误信息
	 * @uses $rule = array(unit, min, max)<br />
	 * unit 长度单位，byte使用字节长，count使用字符数<br />
	 * min 最小长度<br />
	 * max 最大长度
	 * @return boolean
	 */
	private function __validator_length($string, $rule) {

		list($unit_type, $min, $max) = $rule;
		if (stripos($unit_type, 'byte') !== false) {
			// 使用字节长验证
			if (!Validator::is_len_in_range($string, $min, $max)) {
				E(L('_ERR_MENU_BYTE_LENGTH_INVALID', array('min' => $min, 'max' => $max)));
				return false;
			}
		} else {
			// 使用字符数验证
			if (!Validator::is_string_count_in_range($string, $min, $max, 'utf-8')) {
				E(L('_ERR_MENU_CHAR_LENGTH_INVALID', array('min' => $min, 'max' => $max)));
				return false;
			}
		}

		return true;
	}

}
