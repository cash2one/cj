<?php
/**
 * 签到详情信息
 * $Author$
 * $Id$
 */

class voa_sign_detail extends voa_sign_base {

	public function __construct() {

		parent::__construct();
	}

	/** 统计每天的上/下班状态 */
	public function sort_summary($signs, $btime, $etime) {
		/** 所有状态统计 */
		$summary = array();
		foreach (self::$s_sign_st as $k => $v) {
			/** 迟到并且早退, 特殊情况不要 */
			if (6 == $k) continue;
			$summary[$k] = 0;
		}

		/** 整理签到数据 */
		$signs = $this->sign_sort_by_type($signs);
		$sign_day = array();

		/** 整理上班卡信息 */
		$sign_day_on = array();
		if (array_key_exists(voa_d_oa_sign_record::TYPE_ON, $signs)) {
			$sign_day_on = $this->_sort_summary_on($signs[voa_d_oa_sign_record::TYPE_ON]);
		}

		/** 整理下班卡信息 */
		$sign_day_off = array();
		if (array_key_exists(voa_d_oa_sign_record::TYPE_OFF, $signs)) {
			$sign_day_off = $this->_sort_summary_off($signs[voa_d_oa_sign_record::TYPE_OFF]);
		}

		/** 取当天凌晨的时间戳 */
		$cur_ts = rstrtotime(rgmdate(startup_env::get('timestamp'), 'Y-m-d').' 00:00:00');

		/** 补齐空缺记录 */
		for (; $btime < $etime; $btime += 86400) {
			/** 如果时间大于当前时间 */
			if ($btime > $cur_ts) {
				continue;
			}

			$w = rgmdate($btime, 'w');
			/** 如果是周末, 则忽略 */
			if (0 == $w || 6 == $w) {
				continue;
			}

			$ynj = rgmdate($btime, 'Y-n-j');
			/** 上/下班打卡正常, 则视为正常出勤 */
			if (!empty($sign_day_on[$ynj]) && !empty($sign_day_off[$ynj])
					&& voa_d_oa_sign_record::STATUS_WORK == $sign_day_on[$ynj]
					&& voa_d_oa_sign_record::STATUS_WORK == $sign_day_off[$ynj]) {
				$summary[voa_d_oa_sign_record::STATUS_WORK] ++;
				$sign_day[$ynj] = voa_d_oa_sign_record::STATUS_WORK;
			} else {
				if (!empty($sign_day_on[$ynj]) && voa_d_oa_sign_record::STATUS_WORK != $sign_day_on[$ynj]) {
					$summary[$sign_day_on[$ynj]] ++;
					$sign_day[$ynj] = $sign_day_on[$ynj];
				}

				if (!empty($sign_day_off[$ynj]) && voa_d_oa_sign_record::STATUS_WORK != $sign_day_off[$ynj]) {
					$summary[$sign_day_off[$ynj]] ++;
					if (empty($sign_day[$ynj])) { /** 如果前面上班卡正常 */
						$sign_day[$ynj] = $sign_day_off[$ynj];
					} else {
						$sign_day[$ynj] |= $sign_day_off[$ynj];
					}
				}
			}

			/** 如果没有签到记录, 则判定为旷工 */
			if (empty($sign_day[$ynj])) {
				$sign_day[$ynj] = voa_d_oa_sign_record::STATUS_ABSENT;
				$summary[voa_d_oa_sign_record::STATUS_ABSENT] ++;
			}
		}

		return array($sign_day, $summary);
	}

	/** 按类型整理签到数据 */
	public function sign_sort_by_type($signs) {
		$ret = array();
		foreach ($signs as $v) {
			$ynj = rgmdate($v['sr_signtime'], 'Y-n-j');
			if (empty($ret[$v['sr_type']])) {
				$ret[$v['sr_type']] = array();
			}

			$ret[$v['sr_type']][$ynj] = $v;
		}

		return $ret;
	}

	protected function _sort_summary_on($signs) {
		$sign_day = array();
		/** 先检查上班卡 */
		foreach ($signs as $ynj => $v) {
			/** 计算上班卡状态 */
			$status_on = 0 < $v['sr_status'] ? $v['sr_status'] : $this->on_status($v['sr_signtime']);
			/** 如果等于 0 , 则说明打卡状态 */
			if (0 == $status_on) {
				$sign_day[$ynj] = voa_d_oa_sign_record::STATUS_WORK;
				continue;
			}

			$sign_day[$ynj] = $status_on;
		}

		return $sign_day;
	}

	protected function _sort_summary_off($signs) {
		$sign_day = array();
		foreach ($signs as $ynj => $v) {
			/** 判定是否有下班卡记录 */
			if (0 < $v['sr_status']) {
				$status_off = $v['sr_status'];
			} else {
				$status_off = $this->off_status($v['sr_signtime']);
			}

			/** 如果等于 0 , 则说明打卡状态 */
			if (0 == $status_off) {
				$sign_day[$ynj] = voa_d_oa_sign_record::STATUS_WORK;
				continue;
			}

			$sign_day[$ynj] = $status_off;
		}

		return $sign_day;
	}
}
