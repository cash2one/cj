<?php
/**
 * voa_wxqy_menu
 * 微信企业/应用代理菜单
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_wxqy_menu extends voa_wxqy_base {

	/** 主菜单最多允许创建的数量 */
	private $_button_count_max = 5;

	/** 子菜单最多允许创建的数量 */
	private $_sub_button_count_max = 5;

	/** 菜单的响应动作类型 */
	private $_types = array('view', 'click', 'scancode_push', 'scancode_waitmsg', 'pic_sysphoto', 'pic_photo_or_album', 'pic_weixin', 'location_select');

	/** 主菜单标题长度限制 array(长度判断类型byte|count, min, max) */
	private $_button_name_length = array('byte', 1, 16);

	/** 子菜单标题长度限制 array(长度判断类型byte|count, min, max) */
	private $_sub_button_name_length = array('byte', 1, 40);

	/** 菜单的响应动作类型：click类型，需要的 key 的长度限制 array(长度判断类型byte|count, min, max) */
	private $_key_length = array('byte', 1, 128);

	/** 菜单的响应动作类型：view类型，需要的 url 的长度限制 array(长度判断类型byte|count, min, max) */
	private $_url_length = array('byte', 1, 256);

	/** 菜单最多允许的级数 */
	private $_menu_level_max = 2;


	/** 请求错误信息，一般用于调试 */
	public $menu_error = '';

	static function &instance() {
		static $object;
		if(empty($object)) {
			$object	= new self();
		}

		return $object;
	}

	/**
	 * 初始化继承父类，获取access_token
	 */
	public function __construct() {
		parent::__construct();

		/** 获取 access_token $this->_access_token */
		parent::get_access_token();
	}

	/**
	 * 为指定的应用代理创建菜单
	 * @param string $agentid 应用代理id
	 * @param array $data 菜单数据数组
	 * @param number $pluginid 插件id
	 * @return boolean
	 */
	public function create($agentid, $menu_data = array(), $pluginid = 0) {

		// 实际访问的接口 URl
		$api_url = parent::MENU_CREATE_URL;
		if (!$this->_menu_api_url($agentid, $api_url)) {
			logger::error('menu_api_url error = agentid: '.$agentid.'|pluginid: '.$pluginid.'|token:'.$this->_access_token.';;'.var_export($result, true));
			return false;
		}

		// 构造并检查菜单数据
		$menu_data = $this->_check_menu($menu_data, $pluginid);
		if (empty($menu_data) || $this->menu_error) {
			$this->menu_error = '菜单数据错误：'.$this->menu_error;
			$this->errcode = 1001;
			$this->errmsg = $this->menu_error;
			return false;
		}

		// 重新包装以适应接口要求的数据
		$menu_data = array('button' => $menu_data);

		// 提交请求并获取结果
		$result = array();

		$this->post($result, $api_url, rjson_encode($menu_data), true);

		if (empty($result) || !isset($result['errcode']) || $result['errcode'] != 0) {
			// 请求出错
			logger::error('get result = agentid: '.$agentid.'|pluginid: '.$pluginid.'|token:'.$this->_access_token.';;'.var_export($result, true));
			$errcode = isset($result['errcode']) ? $result['errcode'] : 'unknown';
			$errmsg = isset($result['errmsg']) ? $result['errmsg'] : '服务器繁忙';
			if (40056 == $errcode) {
				$errmsg = '应用ID填写错误';
			}
			if (60011 == $errcode) {
				$errmsg = '畅移没有权限同步, 请到微信企业号开启通讯录权限';
			}
			$errmsg .= "(errno:{$errcode})";
			$this->menu_error = $errmsg;
			//$this->menu_error = "Url: {$api_url}\tResult: {$errcode} & {$errmsg}\tData: ".rjson_encode($menu_data)."\t";
			$this->errcode = $result['errcode'];
			$this->errmsg = $errmsg;
			return false;
		}

		return true;
	}

	/**
	 * 删除指定应用代理的菜单
	 * @param string $agentid
	 * @return boolean
	 */
	public function delete($agentid) {

		// 实际访问的接口 url
		$api_url = parent::MENU_DELETE_URL;
		if (!$this->_menu_api_url($agentid, $api_url)) {
			return false;
		}

		// 提交请求并获取结果
		$result = array();
		$this->post($result, $api_url, '', true);

		if (empty($result) || !isset($result['errcode']) || $result['errcode'] != 0) {
			// 请求出错
			$this->menu_error = "菜单删除错误(errno:{$result['errcode']})";
			$this->errcode = $result['errcode'];
			$this->errmsg = $result['errmsg']."(errno:{$result['errcode']})";
			return false;
		}

		return true;
	}

	/**
	 * 获取指定应用代理的菜单数据
	 * @param string $agentid 应用代理id
	 * @param array $data <strong style="color:red">(引用结果)</strong>请求到的菜单数据
	 * @return boolean
	 */
	public function get($agentid, &$data) {

		// 实际访问的接口url
		$api_url = parent::MENU_GET_URL;
		if (!$this->_menu_api_url($agentid, $api_url)) {
			return false;
		}

		// 提交请求并获取结果
		$result = array();
		$this->post($result, $api_url, '', true);

		if (empty($result) || ((!isset($result['errcode']) || $result['errcode'] != 0) && !isset($result['menu']))) {
			// 请求出错
			$this->menu_error = "菜单读取错误(errno:{$result['errcode']})";
			$this->errcode = $result['errcode'];
			$this->errmsg = $result['errmsg']."(errno:{$result['errcode']})";
			return false;
		}

		// 输出引用菜单数据结果
		$data = $result;
		unset($result);

		return true;
	}

	/**
	 * 构造访问接口的Url
	 * @param string $agentid 应用代理id
	 * @param string $url_string <strong style="color:red">(引用结果)</strong>接口url字符串
	 * @return string
	 */
	protected function _menu_api_url($agentid, &$url_string) {
		if (parent::get_access_token() === false) {
			$this->errcode = -501;
			$this->errmsg = '菜单 Token 错误';
			$this->menu_error = $this->errmsg;
			return false;
		}
		$url_string = sprintf($url_string, $this->_access_token, $agentid);
		return true;
	}

	/**
	 * 检查并构造创建应用代理菜单请求需要的数据
	 * @param array $menu_data 代理菜单数据
	 * @param number $pluginid 插件id
	 * @param number $level 菜单级别
	 * @return boolean
	 */
	protected function _check_menu($menu_data, $pluginid, $level = 1) {

		$data = $menu_data;

		if ($level > $this->_menu_level_max) {
			// 菜单级数超出则忽略
			$this->menu_error = '菜单级数超出限制';
			return array();
		}

		if (empty($data)) {
			$this->menu_error = '菜单数据为空';
			return array();
		}

		if ($level > 1) {
			// 子菜单
			if (count($data) > $this->_sub_button_count_max) {
				$this->menu_error = '子菜单数不能超过 '.$this->_sub_button_count_max.' 个';
				return array();
			}
		} else {
			// 主菜单数
			if (count($data) > $this->_button_count_max) {
				$this->menu_error = '主菜单数不能超过 '.$this->_button_count_max.' 个';
				return array();
			}
		}

		// @ 重新整理后的实际菜单数组
		$menu_data = array();
		// 遍历验证菜单项
		foreach ($data as $item) {
			$_item = $this->_check_menu_item($item, $pluginid, $level);
			if ($_item) {
				$menu_data[] = $_item;
			}
			unset($_item);
		}

		return $menu_data;
	}

	/**
	 * 检查并整理菜单项目
	 * @param array $item
	 * @return array
	 */
	protected function _check_menu_item($item, $pluginid, $level) {

		if (empty($item['sub_button']) && (!isset($item['type']) || !in_array($item['type'], $this->_types))) {
			// 如果没有下级菜单，则检查菜单类型，未定义菜单的响应动作类型 或 类型非指定范围 则忽略
			$this->menu_error .= '['.$item['type'].']未定义的响应动作';
			return array();
		}

		// 验证菜单标题长度
		if ($level > 1) {
			// 子菜单
			$name_length_rule = $this->_sub_button_name_length;
		} else {
			// 主菜单
			$name_length_rule = $this->_button_name_length;
		}
		if (!isset($item['name']) || !$this->_validator_length($item['name'], $name_length_rule)) {
			$this->menu_error .= '['.$item['name'].']菜单名称长度不符';
			return array();
		}

		if (empty($item['sub_button']) || !($sub_button = $this->_check_menu($item['sub_button'], $pluginid, $level + 1))) {
			// 如果下级菜单未定义 或 验证失败则移除下级菜单
			unset($item['sub_button']);
		} else {
			// 写入验证后的菜单数据
			$item['sub_button'] = $sub_button;
		}

		if (empty($item['sub_button'])) {
			// 如果子菜单不存在，则验证菜单的响应动作类型值
			$sets = voa_h_cache::get_instance()->get('setting', 'oa');
			switch ($item['type']) {
				case 'view':
					// view 类型的 url 参数验证
					if (!isset($item['url']) || !is_scalar($item['url']) || !$this->_validator_length($item['url'], $this->_url_length)) {
						$this->menu_error .= '['.$item['name'].']url 未定义';
						return array();
					}

					$scheme = config::get('voa.oa_http_scheme');
					$item['url'] = str_ireplace(
							array(
								'{domain_url}',
								'{pluginid}'
							),
							array(
								$scheme.$sets['domain'],
								$pluginid ? $pluginid : ''
							), $item['url']);

					// 转换为带授权信息的链接
					//$item['url'] = parent::_oauth_url(($item['url']), 'snsapi_base', '');

					// 移除view类型不需要的参数key
					unset($item['key']);
				break;
				case 'click':
					// click 类型的 key 参数验证
					if (!isset($item['key']) || !$this->_validator_length($item['key'], $this->_key_length)) {
						$this->menu_error .= '['.$item['name'].']key 未定义';
						return array();
					}
					// 移除click类型不需要的参数url
					unset($item['url']);
				break;
				case 'scancode_push':
					unset($item['url']);
			}
		} else {
			// 如果存在子菜单，则尝试移除其他参数
			unset($item['type'], $item['url'], $item['key']);
		}

		return $item;
	}

}
