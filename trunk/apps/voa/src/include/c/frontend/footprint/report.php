<?php
/**
 * 销售轨迹列表统计
 * $Author$
 * $Id$
 */

class voa_c_frontend_footprint_report extends voa_c_frontend_footprint_base {

	public function execute() {
		/** 轨迹最终状态值(签约) */
		$lastid = (string)current($this->_p_sets['types']['system']);

		/** 需要查看的用户 */
		$cur_uid = (int)$this->request->get('uid');

		/** 取月份开始时间/结束时间 */
		$month = '';
		$btime = $etime = 0;
		$this->_get_month_range($month, $btime, $etime);

		$this->_get_week_info($btime, $etime);

		/** 取该月签约数 */
		$serv_fp = &service::factory('voa_s_oa_footprint', array('pluginid' => startup_env::get('pluginid')));
		$month_sign = $serv_fp->count_by_visittime_and_type($btime, $etime, $lastid);
		$month_total = $serv_fp->count_by_visittime_and_type($btime, $etime);

		/** 取该月每周签约数 */
		$list_week_sign = $serv_fp->count_week_by_visittime_and_type($btime, $etime, $lastid);
		$list_week_total = $serv_fp->count_week_by_visittime_and_type($btime, $etime);

		/** 取该月每天签约数 */
		$list_day_sign = $serv_fp->count_day_by_visittime_and_type($btime, $etime, $lastid);
		$list_day_total = $serv_fp->count_day_by_visittime_and_type($btime, $etime);

		/** 设置部门和职位 */
		$this->_set_dept_job();

		$this->view->set('cur_uid', $cur_uid);
		$this->view->set('month_sign', $month_sign);
		$this->view->set('month_total', $month_total);
		$this->view->set('list_week_sign', $list_week_sign);
		$this->view->set('list_week_total', $list_week_total);
		$this->view->set('list_day_sign', $list_day_sign);
		$this->view->set('list_day_total', $list_day_total);
		$this->view->set('navtitle', '报表');

		$this->_output('footprint/report');
	}

	protected function _get_week_info($btime, $etime) {
		$day_th = 1;
		list($w, $w_th, $y, $n) = explode(' ', rgmdate($btime, 'W w Y n'));
		/** 根据开始和结束时间, 按周整理时间 */
		$weeks = array();
		$week_to_days = array();
		for ($i = $btime; $i < $etime; $i += 86400) {
			if (!array_key_exists($w, $weeks)) {
				$weeks[$w] = array();
				$weeks[$w][] = $n.'/'.$day_th;
				$week_to_days[$w] = array();
			}

			if (0 == $w_th) {
				$weeks[$w][] = $n.'/'.$day_th;
			}

			$week_to_days[$w][$y.'-'.$n.'-'.$day_th] = $n.'/'.$day_th;
			$day_th ++;
			$w_th ++;
			if (7 == $w_th) {
				$w_th = 0;
			}

			if (1 == $w_th) {
				$w ++;
			}
		}

		$this->view->set('weeks', $weeks);
		$this->view->set('week_to_days', $week_to_days);
	}

	/**
	 * 取月份开始/结束时间
	 * @param string $month
	 * @param int $btime
	 * @param int $etime
	 * @return boolean
	 */
	protected function _get_month_range(&$month, &$btime, &$etime) {
		/** 月份(格式:2014-05) */
		$month = (string)$this->request->get('month');
		$month = empty($month) ? rgmdate(startup_env::get('timestamp'), 'Y-n') : $month;
		$btime = rstrtotime($month.'-01 00:00:00');
		if (0 == $btime || $btime > startup_env::get('timestamp')) {
			$month = rgmdate(startup_env::get('timestamp'), 'Y-n');
			$btime = rstrtotime($month.'-01 00:00:00');
		}

		/** 取下一月份 */
		list($y, $n) = explode('-', $month);
		$this->view->set('cur_year_month', $y.'年<i>'.$n.'</i>月');
		if (12 <= $n) {
			$y ++;
			$n = 1;
		} else {
			$n ++;
		}

		$etime = rstrtotime($y.'-'.$n.'-01 00:00:00');

		/** 处理月份输出 */
		$start = $btime - 180 * 86400;
		list($s_y, $s_n) = explode('-', rgmdate($start, 'Y-n'));
		list($c_y, $c_n) = explode('-', rgmdate(startup_env::get('timestamp'), 'Y-m'));

		$yms = array();
		for ($i = $s_y; $i <= $c_y; $i ++) {
			for ($k = 1; $k <= 12; $k ++) {
				/** 小于起始月份 */
				if ($i == $s_y && $k < $s_n) {
					continue;
				}

				/** 大于当前时间月份 */
				if ($i == $c_y && $k > $c_n) {
					break;
				}

				$yms[$i.'-'.$k] = $i.'年'.$k.'月';
			}
		}

		$this->view->set('year_months', $yms);
		$this->view->set('selected_year_month', $month);

		return true;
	}
}
