<?php
/**
 * 签到基类
 * $Author$
 * $Id$
 */

class voa_sign_base {
	/** 签到配置 */
	protected $_set;
	/** 最大过期时间间隔, 单位:秒 */
	const MAX_EXPIRES = 300;
	/** 过期时间戳 */
	protected $_expires_ts;
	/** 迟到时间限制 */
	protected $_late_range = 0;
	/** 早退时间限制 */
	protected $_leave_early_range = 0;

	/**
	 * 签到状态
	 * 1: 出勤
	 * 2: 迟到
	 * 4: 早退
	 * 8: 旷工
	 * 16: 请假
	 * 32: 出差
	 */
	public static $s_sign_st = array(
		1 => '出勤', 2 => '迟到', 4 => '早退', 6 => '迟/退', 8 => '旷工'//, 16 => '请假', 32 => '出差'
	);

	public function __construct() {

		/** 取打卡配置 */
		$this->_set = voa_h_cache::get_instance()->get('plugin.sign.setting', 'oa');
		$this->_expires_ts = $this->_set['sign_expires'];
		$this->_expires_ts = empty($this->_expires_ts) || $this->_expires_ts > self::MAX_EXPIRES ? self::MAX_EXPIRES : $this->_expires_ts;
		$this->_late_range = (int)$this->_set['late_range']['ss_value'];

		$this->_leave_early_range = (int)$this->_set['leave_early_range']['ss_value'];
	
	}

	/**
	 * 获取当前下班卡状态
	 * @param int $ts 打卡时间戳
	 */
	/* public function _off_status($ts) {

		$status = 0;
		$remainder = $this->_to_seconds(rgmdate($ts, 'H:i'));
		if ($remainder < $this->_work_e_sec - $this->_leave_early_range) {
			$status = voa_d_oa_sign_record::STATUS_LEAVE;
		}

		return $status;
	} */
	/**
	 * 获取当前下班卡状态
	 * @param int $ts 打卡时间戳
	 */
	public function off_status($ts, $work_end) {
		$status = 1;
		$remainder = $this->_to_seconds(rgmdate($ts, 'H:i'));
		if ($remainder < $work_end - $this->_leave_early_range*60) {
			$status = voa_d_oa_sign_record::STATUS_LEAVE;
		}
	
		return $status;
	}

	/**
	 * 获取当前上班卡状态
	 * @param int $ts 打卡时间戳
	 */
	/* public function _on_status($ts) {

		$status = 0;
		$remainder = $this->_to_seconds(rgmdate($ts, 'H:i'));
		if ($remainder > $this->_work_b_sec + $this->_late_range) { // 如果在规定上班时间之后 

			$status = voa_d_oa_sign_record::STATUS_LATE;
		}

		return $status;
	} */
	/**
	 * 获取当前上班卡状态
	 * @param int $ts 打卡时间戳
	 */
	public function on_status($ts, $work_begin) {

		$status = 1;
		$remainder = $this->_to_seconds(rgmdate($ts, 'H:i'));

		if ($remainder > $work_begin + $this->_late_range*60) { /** 如果在规定上班时间之后 */
			$status = voa_d_oa_sign_record::STATUS_LATE;
		}
	
		return $status;
	}

	/** 把时间转成对应的秒数 */
	protected function _to_seconds($hi) {
		//$a = explode(':', $hi);	
		@list($h, $i) = explode(':', $hi);
		return $h * 3600 + $i * 60;
	}
	/**
	 * 格式数字为时间
	 * @param unknown $num
	 * @return string
	 */
	public function formattime($num) {
		if (strlen ( $num ) == 0) {
			$time = '00:00';
			return $time;
		} elseif (strlen ( $num ) == 1) {
			$time = '00:0' . $num;
			return $time;
		} elseif (strlen ( $num ) == 2) {
			$time = '00:' . $num;
			return $time;
		} elseif (strlen ( $num ) == 3) {
			$hour = substr ( $num, 0, 1 );
			$min = substr ( $num, 1, 2 );
			$time = '0' . $hour . ':' . $min;
			return $time;
		} else {
			$hour = substr ( $num, 0, 2 );
			$min = substr ( $num, 2, 2 );
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
		if (strlen ( $num ) == 0) {
			$time = '0000';
			return $time;
		} elseif (strlen ( $num ) == 1) {
			$time = '000' . $num;
			return $time;
		} elseif (strlen ( $num ) == 2) {
			$time = '00' . $num;
			return $time;
		} elseif (strlen ( $num ) == 3) {
			$hour = substr ( $num, 0, 1 );
			$min = substr ( $num, 1, 2 );
			$time = '0' . $hour . $min;
			return $time;
		} else {
			$hour = substr ( $num, 0, 2 );
			$min = substr ( $num, 2, 2 );
			$time = $hour . $min;
			return $num;
		}
	}
}
