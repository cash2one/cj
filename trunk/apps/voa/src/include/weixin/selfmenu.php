<?php
/**
 * 微信-自定义菜单操作类
 * $Author$
 * $Id$
 */

class voa_weixin_selfmenu extends voa_weixin_base {
	/** 创建接口URL POST */
	const SELFMENU_CREATE_URL = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token=%s';
	/** 查询接口 URL GET */
	const SELFMENU_GET_URL = 'https://api.weixin.qq.com/cgi-bin/menu/get?access_token=%s';
	/** 删除接口 URL GET */
	const SELFMENU_DELETE_URL = 'https://api.weixin.qq.com/cgi-bin/menu/delete?access_token=%s';
	/** 菜单允许的最大级数 */
	protected $_levels = 2;
	/** 菜单标题长度限制，最大长度。以上标表示菜单级别。 */
	protected $_name_length = array(1 => 16, 2 => 40);
	/** 每级菜单的子菜单最大数量 */
	protected $_menu_count = array(1 => 3, 2 => 5);
	/** 菜单KEY值，用于消息接口推送，不超过128字节 */
	protected $_menu_key_length = 128;
	/** 网页链接，用户点击菜单可打开链接，不超过256字节 */
	protected $_menu_url_length = 256;


	/**
	 * 初始化
	 */
	public function __construct() {
		parent::__construct();

		/** 获取 access_token $this->_access_token */
		parent::get_access_token();
	}

	/**
	 * 自微信获取自定义菜单
	 * @return array|false|null
	 */
	public function get_selfmenu() {
		$url = sprintf(self::SELFMENU_GET_URL, $this->_access_token);
		$r = array();
		voa_h_func::get_json_by_post($r, $url);
		if (isset($r['errcode']) && $r['errcode']) {
			if ($r['errcode'] == '46003') {
				/** 自定义菜单不存在 */
				return false;
			} else {
				return null;
			}
		} else {
			/** 成功获取结果 */
			return $r;
		}
	}

	/**
	 * 删除自定义菜单
	 * @return boolean
	 */
	public function delete_selfmenu() {
		$url = sprintf(self::SELFMENU_DELETE_URL, $this->_access_token);
		$r = array();
		voa_h_func::get_json_by_post($r, $url);
		if (isset($r['errcode']) && $r['errcode'] == 0) {
			/** 删除成功 */
			return true;
		} else {
			return false;
		}
	}

	/**
	 * 创建自定义菜单
	 * @param array $data
	 * @return boolean|array
	 */
	public function create_selfmenu($data = array()) {
		if (!is_array($data)) {
			return false;
		}

		$url = sprintf(self::SELFMENU_CREATE_URL, $this->_access_token);
		$menuData = self::_parseMenu($data, 1);
		$menuData = rjson_encode($menuData);
		$r = array();
		voa_h_func::get_json_by_post($r, $url, $menuData);
		if (isset($r['errcode']) && $r['errcode'] == 0) {
			$r['menuData'] = $menuData;
			return $r;
		} else {
			return false;
		}
	}

	/**
	 * 整理构造请求微信的自定义菜单数组
	 * @param array $menu
	 * @param number $curDeep
	 * @return array
	 */
	protected function _parseMenu($menu, $curDeep = 1) {
		if ($curDeep > $this->_levels) {
			return array();
		}

		$resetMenu = array();
		$o = 0;
		foreach ($menu AS $k => $arr) {
			/** type 和 name 必须存在 */
			if (!isset($arr['type']) || !isset($arr['name'])) {
				continue;
			}
			/** 菜单标题长度大于该级别允许的最大长度 */
			if ($this->_name_length[$curDeep] && strlen($arr['name']) > $this->_name_length[$curDeep]) {
				continue;
			}

			$newMenu = array();
			/** 菜单标题 */
			$newMenu['name'] = $arr['name'];
			/** 存在下级菜单 */
			if (isset($arr['sub_button']) && ($tmp = self::_parseMenu($arr['sub_button'], $curDeep+1))) {
				$newMenu['sub_button'] = $tmp;
			}

			if (empty($newMenu['sub_button'])) {
				/** 不存在下级菜单，则校验 */

				/** 重新校验类型 */
				$value = !empty($arr['url']) ? $arr['url'] : (!empty($arr['key']) ? $arr['key'] : '');
				if (empty($value) && empty($arr['sub_button'])) {
					/** key 与 url 均不存在，且无下级菜单，则忽略 */
					continue;
				}

				if (!empty($arr['sub_button']) || (is_array($parse_url = @parse_url($value)) && !empty($parse_url['scheme']) && !empty($parse_url['host']))) {
					/** url */
					$arr['type'] = 'view';
					$arr['url'] = !empty($arr['sub_button']) ? '' : $value;
					unset($arr['key']);
				} else {
					/** 非url */
					$arr['type'] = 'click';
					$arr['key'] = $value;
					unset($arr['url']);
				}

				/** 菜单类型 */
				$newMenu['type'] = $arr['type'];
				if (empty($arr['sub_button'])) {
					if ($arr['type'] == 'click') {
						/** click类型 */
						if (strlen($arr['key']) > $this->_menu_key_length) {
							/** key的长度超过允许长度 */
							continue;
						}
						if (empty($newMenu['sub_button']) && !isset($arr['key'])) {
							/**要求必须存在key */
							continue;
						}
						$newMenu['key'] = $arr['key'];
					}
					if ($arr['type'] == 'view') {
						/** view类型 */
						if (empty($newMenu['sub_button']) && !isset($arr['url'])) {
							/** 要求必须存在url */
							continue;
						}
						if (strlen($arr['url']) > $this->_menu_url_length) {
							/** url长度超过允许长度 */
							continue;
						}
						$newMenu['url'] = $arr['url'];
					}
				}
			}
			if ($newMenu) {
				if (isset($newMenu['sub_button'])) {
					/** 存在子菜单则不再使用主值 */
					unset($newMenu['key'], $newMenu['url'], $newMenu['type']);
				}

				$resetMenu[] = $newMenu;
				if (count($resetMenu) > $this->_menu_count[$curDeep]) {
					/** 如果菜单数量大于当前级别允许的最大数，则忽略后面的菜单 */
					break;
				}
			}
		}
		if ($curDeep == 1) {
			return array('button' => $resetMenu);
		}
		return $resetMenu;
	}

}
