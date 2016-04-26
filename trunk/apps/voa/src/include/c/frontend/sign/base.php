<?php

/**
 * 签到操作相关
 * $Author$
 * $Id$
 */
class voa_c_frontend_sign_base extends voa_c_frontend_base {
	/**
	 * 签到状态
	 * 1: 出勤
	 * 2: 迟到
	 * 4: 早退
	 * 8: 旷工
	 * 16: 请假
	 * 32: 出差
	 */
	protected $_sign_st = array(
		1 => '出勤',
		2 => '迟到',
		4 => '早退',
		6 => '迟/退',
		8 => '旷工',
		16 => '请假',
		32 => '出差'
	);

	/**
	 * 对应样式
	 */
	protected $_styles = array(
		2 => 'chidao',
		4 => 'zaotui',
		6 => 'chidao',
		8 => 'kuanggong'
	);
	protected $_y;
	protected $_n;
	protected $_year_sel;
	protected $_month_sel;

	/**
	 * 是否为工作日允许签到
	 */
	protected $_allow_sign = false;

	/**
	 * 地理位置上报表操作
	 */
	protected $_serv_sign_location = null;
	/**
	 * 签到表操作
	 */
	protected $_serv_sign_record = null;

	public function __construct() {
		parent::__construct();

		/**
		 * 可选年份
		 */
		$this->_y = rgmdate(startup_env::get('timestamp'), 'Y');
		$this->_n = rgmdate(startup_env::get('timestamp'), 'n');
		$this->_year_sel = array(
			$this->_y,
			$this->_y - 1
		);
		$this->_month_sel = range(0, 11);
		/**
		 * 启用详情类的状态值
		 */
		$this->_sign_st = voa_sign_base::$s_sign_st;

		/*
		 * if ($this->_serv_sign_record === null) {
		 * $shard_key = array('pluginid' => startup_env::get('pluginid'));
		 * $this->_serv_sign_record = &service::factory('voa_s_oa_sign_record', $shard_key);
		 * $this->_serv_sign_location = &service::factory('voa_s_oa_sign_location', $shard_key);
		 * }
		 */
	}

	protected function _before_action($action) {
		if (!parent::_before_action($action)) {
			return false;
		}

		$this->_mobile_tpl = true;
		if (empty ($this->_p_sets ['up_position_rate']) || $this->_p_sets ['up_position_rate'] < 60) {
			// 系统限制两次上报位置时间间隔不能少于60秒
			$this->_p_sets ['up_position_rate'] = 60;
		}

		return true;
	}

	// 获取插件信息
	protected function _get_plugin() {
		$this->_p_sets = voa_h_cache::get_instance()->get('plugin.sign.setting', 'oa');
		/**
		 * 取应用插件信息
		 */
		$pluginid = $this->_p_sets ['pluginid'];
		startup_env::set('pluginid', $pluginid);
		$plugins = voa_h_cache::get_instance()->get('plugin', 'oa');

		// 如果应用信息不存在
		if (!array_key_exists($pluginid, $plugins)) {
			$this->_error_message('应用信息丢失，请重新开启');

			return true;
		}

		// 获取应用信息
		$this->_plugin = $plugins [$pluginid];

		// 判断应用是否关闭
		if ($this->_plugin ['cp_available'] != voa_d_oa_common_plugin::AVAILABLE_OPEN) {
			$this->_error_message('本应用尚未开启 或 已关闭，请联系管理员启用后使用');

			return true;
		}

		startup_env::set('agentid', $this->_plugin ['cp_agentid']);
		/**
		 * 加载提示语言
		 */
		language::load_lang($this->_plugin ['cp_identifier']);

		return true;
	}

	/**
	 * 获取签到配置
	 */
	public static function fetch_cache_sign_setting() {

		$serv = &service::factory('voa_s_oa_sign_setting', array('pluginid' => startup_env::get('pluginid')));
		$data = $serv->list_all();
		$arr = array();
		$_pluginid = 0;
		if (! empty($data)) {
			foreach ($data as $v) {
				if (voa_d_oa_common_setting::TYPE_ARRAY == $v['ss_type']) {
					$arr[$v['ss_key']] = unserialize($v['ss_value']);
				} else {
					$arr[$v['ss_key']] = $v['ss_value'];
				}
				if ($v['ss_key'] == 'pluginid') {
					$_pluginid = $v['ss_value'];
				}
			}
		}

		self::_check_agentid($arr, 'sign');
		return $arr;
	}

	/**
	 * 获取上级部门id
	 * @param unknown $cd_id
	 * @return unknown
	 */
	public function get_upid($cd_id) {

		static $deplist = null;

		if ($deplist === null) {
			$deplist = voa_h_cache::get_instance()->get('department', 'oa');
		}

		$upid = isset($deplist[$cd_id]) ? $deplist[$cd_id]['cd_upid'] : 0;

		return $upid;
	}

	/**
	 * 格式数字为时间
	 * @param unknown $num
	 * @return string
	 */
	public function formattime($num) {
		if (strlen($num) == 0) {
			$time = '00:00';

			return $time;
		} elseif (strlen($num) == 1) {
			$time = '00:0' . $num;

			return $time;
		} elseif (strlen($num) == 2) {
			$time = '00:' . $num;

			return $time;
		} elseif (strlen($num) == 3) {
			$hour = substr($num, 0, 1);
			$min = substr($num, 1, 2);
			$time = '0' . $hour . ':' . $min;

			return $time;
		} else {
			$hour = substr($num, 0, 2);
			$min = substr($num, 2, 2);
			$time = $hour . ':' . $min;

			return $time;
		}
	}

	/**
	 * 格式数字补为四位
	 * @param unknown $num
	 * @return string|unknown
	 */
	public function formatnum($num) {
		if (strlen($num) == 0) {
			$time = '0000';

			return $time;
		} elseif (strlen($num) == 1) {
			$time = '000' . $num;

			return $time;
		} elseif (strlen($num) == 2) {
			$time = '00' . $num;

			return $time;
		} elseif (strlen($num) == 3) {
			$hour = substr($num, 0, 1);
			$min = substr($num, 1, 2);
			$time = '0' . $hour . $min;

			return $time;
		} else {
			$hour = substr($num, 0, 2);
			$min = substr($num, 2, 2);
			$time = $hour . $min;

			return $num;
		}
	}

	/**
	 * 将时间大于24的时间格式化
	 * @param unknown $ymd
	 * @param unknown $num
	 * @return number
	 */
	public function totime($ymd, $num) {
		$time = $num;
		$h = substr($time, 0, 2);
		// 2015-08-15 25:23;
		if ($h - 24 > 0) {
			$diff = $h - 24;
			$m = substr($time, 3, 2);
			$formattime = strtotime('+1 day', strtotime($ymd)) + $diff * 3600 + $m * 60 - 8 * 3600;
		}

		return $formattime;
	}
}
