<?php
/**
 * 统一数据访问基类(uniform data access)
 * $Author$
 * $Id$
 */

class voa_uda_frontend_base extends uda {
	// 是否分页
	protected $_limit = true;
	// 不格式化
	const FMT_NONE = 0;
	// 格式化一次
	const FMT_ONCE = 1;
	// 格式化多次
	const FMT_MULTI = 2;
	// 格式化标识
	protected $_fmt = 2;
	// start
	protected $_start = 0;
	// page
	protected $_page = 0;
	// perpage
	protected $_perpage = 0;
	// total
	protected $_total = 0;
	// service
	protected $_serv = null;

	public function __construct() {

		parent::__construct();
	}

	// 设置是否限制返回数目的开关
	public function set_limit($limit) {

		$this->_limit = $limit;
	}

	/**
	 * 设置格式化
	 * @param int $fmt 格式化标识
	 */
	public function set_fmt($fmt) {

		$this->_fmt = (int)$fmt;
	}

	/**
	 * 格式化
	 * @param array $list 数据数组
	 * @param string $multi
	 */
	protected function _format(&$list, $multi = false, $serv = null) {

		// 如果不需要格式化
		if (self::FMT_NONE == $this->_fmt) {
			return true;
		}

		// 如果只需要格式化一次
		if (self::FMT_ONCE == $this->_fmt) {
			$this->_fmt = self::FMT_NONE;
		}

		// 如果未指定 service
		if (empty($serv)) {
			// 默认 service 为空
			if (empty($this->_serv)) {
				return true;
			}

			$serv = $this->_serv;
		}

		// 如果是单条记录
		if (false == $multi) {
			$serv->format($list);
			return true;
		}

		// 遍历记录数组, 逐个格式化
		foreach ($list as &$_v) {
			$serv->format($_v);
		}

		return true;
	}

	/**
	 * 获取分页参数
	 * @param array $option 分页参数
	 * @param array $conds 输入参数
	 * @return boolean
	 */
	protected function _get_page_option(&$option, &$conds) {

		$this->_page = 0;
		$this->_perpage = 0;
		// 如果不限制
		if (false == $this->_limit) {
			$option = null;
			return true;
		}

		$conds['page'] = empty($conds['page']) ? 0 : $conds['page'];
		$conds['perpage'] = empty($conds['perpage']) ? 0 : $conds['perpage'];
		list($this->_start, $this->_perpage, $this->_page) = voa_h_func::get_limit($conds['page'], $conds['perpage']);
		$option = array($this->_start, $this->_perpage);

		unset($conds['page'], $conds['perpage']);
		return true;
	}

	public function get_start() {

		return $this->_start;
	}

	public function get_page() {

		return $this->_page;
	}

	public function get_perpage() {

		return $this->_perpage;
	}

	public function get_total() {

		return $this->_total;
	}

	/**
	 * 搜索条件验证：检查一个日期格式是否符合搜索条件要求
	 * @param string $date
	 * @return boolean
	 */
	public function search_val_date(&$date) {
		if (!$date || !validator::is_date($date)) {
			return false;
		}
		$date = rstrtotime($date);
		return true;
	}

	/**
	 * 提出发生改变的数据
	 * @param array $old_data 旧的数据
	 * @param array $new_data 新数据
	 * @param array $updated 数据发生改变的数组
	 * @return boolean
	 */
	public function updated_fields($old_data, $new_data, &$updated) {
		$updated = array();
		foreach ($old_data as $k => $v) {
			if (isset($new_data[$k]) && $new_data[$k] != $v) {
				$updated[$k] = $new_data[$k];
			}
		}

		return true;
	}

	/**
	 * 验证字符串长度是否介于某个范围
	 * @param string $string
	 * @param array $rule 验证规则
	 * @uses $rule = array(unit, min, max)<br />
	 * unit 长度单位，byte使用字节长，count使用字符数<br />
	 * min 最小长度<br />
	 * max 最大长度
	 * @return boolean
	 */
	public function validator_length(&$string, $rule) {
		$string = (string)$string;
		list($unit_type, $min, $max) = $rule;

		if (stripos($unit_type, 'byte') !== false) {
			// 使用字节长验证
			$length = strlen($string);
			$error_msg = '长度应该介于 '.$min.'到'.$max.' 字节之间';
		} else {
			// 使用字符数验证
			$length = mb_strlen($string, 'utf-8');
			$error_msg = '长度应该介于 '.$min.'到'.$max.' 个字符之间';
		}

		if ($length >= $min && $length <= $max) {
			return true;
		} else {
			$this->errmsg('9001', $error_msg);
			return false;
		}
	}

	/**
	 * 更新企业OA系统缓存
	 * @return boolean
	 */
	public function update_cache() {

		if (empty($this->_system_update_cache_over)) {
			$this->_system_update_cache_over = true;
		} else {
			// 已经更新过，不再进行更新
			return true;
		}

		// 不需要缓存的应用唯一标识列表
		//TODO 新应用开发时需要留意此处，不需要缓存的请在这里设置忽略
		$ignores = array(
			'customize'
		);

		// 所有表缓存
		$systems = array(
			'setting' => 0,
			'weixin' => 0,
			'cpmenu' => 0,
			'plugin' => 0,
			'department' => 0,
			'job' => 0,
			'region' => 0,
			'shop' => 0,
			'common_place_setting' => 0,
			'common_place_type' => 0,
			'common_place_region' => 0,
			'common_place' => 0
		);
		$caches = $systems;

		$availables = array(
			voa_d_oa_common_plugin::AVAILABLE_WAIT_CLOSE,
			voa_d_oa_common_plugin::AVAILABLE_OPEN,
			voa_d_oa_common_plugin::AVAILABLE_WAIT_DELETE,
			voa_d_oa_common_plugin::AVAILABLE_CLOSE
		);

		// 读取所有插件
		/**$serv_p = &service::factory('voa_s_oa_common_plugin', array('pluginid' => 0));
		$plugins = $serv_p->fetch_all();
		foreach ($plugins as $p) {
			if (!in_array($p['cp_available'], $availables)) {
				continue;
			}

			if (in_array($p['cp_identifier'], $ignores)) {
				continue;
			}

			$caches[$p['cp_identifier'].'_setting'] = $p['cp_pluginid'];
		}

		$table = array();*/

		// 遍历, 更新所有
		/**foreach ($caches as $c => $pluginid) {

			if (array_key_exists($c, $systems)) {
				voa_h_cache::get_instance()->get($c, 'oa', true);
				continue;
			}

			voa_h_cache::get_instance()->get('plugin.'.str_replace('_', '.', $c), 'oa', true);
		}*/

		// 清理所有权限组的后台菜单缓存
		$site_cache_dir = voa_h_func::get_sitedir(startup_env::get('domain'));
		$handle = opendir($site_cache_dir);
		if ($handle) {
			while (false !== ($file = readdir($handle))) {
				if (!preg_match('/\.php$/i', $file)) {
					continue;
				}

				if (false === stripos($file, 'dbconf.inc.php')) {
					@unlink($site_cache_dir.'/'.$file);
				}
			}
			closedir($handle);
		}

		// 清除 redis 缓存
		$serv_sys = service::factory('voa_s_oa_common_syscache');
		$list = $serv_sys->list_all();
		foreach ($list as $_cache) {
			voa_h_cache::get_instance()->remove($_cache['csc_name'], 'oa');
		}

		return true;
	}

}
