<?php
/**
 * 新的报告
 * $Author$
 * $Id$
 */
class voa_c_frontend_dailyreport_new extends voa_c_frontend_dailyreport_base {

	public function execute() {

		// 提交新建
		if ($this->_is_post()) {
			$this->_add();
			return false;
		}

		// 获取星期文字定义
		$weeknames = config::get('voa.misc.weeknames');

		// 日报时间可选范围:前30天
		$btime = startup_env::get('timestamp');
		// 获取当前系统，年，月，日，第x周
		list($s_y, $s_m, $s_d, $s_wn) = explode('-', rgmdate($btime, 'Y-n-j-W'));
		// 可选的日报日期列表
		$days = array();
		for ($i = 0; $i < date('t'); ++ $i) {
			// 当前进程的日期时间戳
			$ts = $btime - $i * 86400;
			$ymdw = voa_h_func::date_fmt('Y m d w', $ts);
			$cur_ts = rstrtotime($ymdw['Y'] . '-' . $ymdw['m'] . '-' . $ymdw['d'] . ' 00:00:00');
			// 定义可选日期列表
			$days[$cur_ts . '|' . $ymdw['Y'] . '年' . $ymdw['m'] . '月' . $ymdw['d'] . '日'] = $ymdw;
		}
		unset($i);

		// 月报时间可选范围:前12个月
		$months = array();
		for ($i = 0; $i < 13; $i ++) {
			// 进程中的月份和年份
			$_c_m = $s_m - $i;
			$_c_y = $s_y;
			// 如果月份为负数，则处理为真实有效的年份（跨年）和月份
			if ($_c_m <= 0) {
				$_c_y = $s_y - abs(ceil($_c_m / 12) - 1);
				$_c_m = 12 - abs($_c_m) % 12;
			}
			// 格式化当前进程的月份前导加零
			$__c_m = sprintf("%02s", $_c_m);
			$cur_ts = rstrtotime("{$_c_y}-{$__c_m}-1 00:00:00");
			// 定义当前可选月份列表
			$months[$cur_ts . "|" . $_c_y . '年' . $__c_m . '月'] = $_c_y . '年' . $__c_m . '月';
			unset($_c_m, $_c_y, $__c_m);
		}
		unset($i);

		// 年报时间可选范围:前后3年
		$years = array();
		for ($i = 0; $i < 3; $i ++) {
			$_y = $s_y - $i;
			// 定义可选年份列表
			$years[rstrtotime($_y . '-01-01 00:00:00') . '|' . $_y . '年'] = $_y . '年';
		}
		unset($_y);

		// 周报时间可选范围:前7周
		$weeks = array();
		$current_week = rgmdate($btime, 'W'); // 计算当前周
		// 定义当前周的开始时间戳
		$current_week_timestamp = rstrtotime($s_y . 'W' . $current_week);
		for ($i = 0; $i < 7; $i ++) {
			// 当前周的开始时间戳
			$cus_ts = $current_week_timestamp - $i * (86400 * 7);
			// 当前为第x周
			$wk = rgmdate($cus_ts, 'W');
			// 当前周的起始日期
			$_w_s = rgmdate($cus_ts, 'm-d');
			// 当前周的结束日期
			$_w_e = rgmdate($cus_ts + 86400 * 6, 'm-d');
			// 当前周的所在年份
			$_w_y = rgmdate($cus_ts + 86400 * 6, 'Y');
			// 定义可选周的列表
			$weeks[$cus_ts . '|' . $_w_y . '年第' . $wk . '周(' . $_w_s . ' - ' . $_w_e . ')']
			= '第' . $wk . '周(' . $_w_s . ' - ' . $_w_e . ')';
		}

		// 季报时间可选范围:前4季
		$seasons = array();
		// 当前第x季
		$current_season = ceil((rgmdate($btime, 'n')) / 3);
		for ($i = 0; $i < 4; $i ++) {
			// 当前可能的第x季
			$s = $current_season - $i;
			// 大于0是合法的
			if ($s > 0) {
				$c_y = $s_y;
			} else {
				// 小于零则涉及跨年问题，重新计算年份
				$c_y = $s_y - abs(ceil($s / 4) - 1);
				$s = 4 - abs($s) % 4;
			}
			// 根据有效的第x季，计算开始的月份
			$c_m = $s * 3 - 3 + 1;
			// 当前季的开始时间戳
			$cus_ts = rstrtotime("{$c_y}-{$c_m}-1 00:00:00");
			// 定义可选的季度列表
			$seasons[$cus_ts . '|' . $c_y . '年第' . $s . '季度'] = $c_y . '年 第' . $s . '季度';
		}

		// 读应用配置缓存
		$p_sets = voa_h_cache::get_instance()->get('plugin.dailyreport.setting', 'oa');

		// 取草稿信息
		$data = array();
		$this->_get_draft($data);
		$data['message'] = isset($data['message']) ? $data['message'] : '';
		$data['accepter'] = empty($data['accepter']) ? array() : $data['accepter'];
		$data['ccusers'] = isset($data['ccusers']) ? $data['ccusers'] : array();

		$this->view->set('dailyType', $p_sets['daily_type']); // 日报类型数组
		$this->view->set('p_sets', $p_sets);// 工作报告相关配置信息
		$this->view->set('ccusers', $data['ccusers']);
		$this->view->set('message', $data['message']);
		$this->view->set('accepter', $data['accepter']);
		$this->view->set('action', $this->action_name);
		$this->view->set('dailyreport', array());
		$this->view->set('days', $days); // 日报时间
		$this->view->set('months', $months); // 月报时间
		$this->view->set('years', $years); // 年报时间
		$this->view->set('weeks', $weeks); // 周报时间
		$this->view->set('seasons', $seasons); // 季报时间
		$this->view->set('weeknames', $weeknames);
		$this->view->set('navtitle', '新建报告');
		$this->_output('mobile/dailyreport/post');
	}

	/**
	 * 新增报告
	 * @return boolean
	 */
	public function _add() {
		$uda = &uda::factory('voa_uda_frontend_dailyreport_insert');
		// 日报信息
		$dailyreport = array();
		// 日报详情信息
		$post = array();
		// 接收人信息
		$mem = array();
		// 抄送人信息
		$cculist = array();
		if (! $uda->dailyreport_new($dailyreport, $post, $mem, $cculist)) {
			$this->_error_message($uda->error);
			return false;
		}
		
		// 更新草稿信息
		$this->_update_draft(array_column($mem, "m_uid"), array_keys($cculist));

		// 格式化
		$fmt = &uda::factory('voa_uda_frontend_dailyreport_format');
		if (! $fmt->format($dailyreport)) {
			$this->_error_message($fmt->error);
			return false;
		}

		// 发送消息通知
		$uda->send_wxqymsg_news($this->session, $dailyreport, 'new', startup_env::get('wbs_uid'), array());

		$this->_success_message('发布报告成功', "/dailyreport/view/{$dailyreport['dr_id']}");
	}

}
