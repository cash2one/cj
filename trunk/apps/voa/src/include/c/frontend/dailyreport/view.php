<?php
/**
 * 查看报告
 * $Author$
 * $Id$
 */
class voa_c_frontend_dailyreport_view extends voa_c_frontend_dailyreport_base {

	/**
	 * 正常
	 */
	const STATUS_NORMAL = 1;

	/**
	 * 已更新
	 */
	const STATUS_UPDATE = 2;

	/**
	 * 抄送人
	 */
	const STATUS_CARBON_COPY = 3;

	/**
	 * 已删除
	 */
	const STATUS_REMOVE = 4;

	public function execute() {
		// 报告ID
		$dr_id = rintval($this->request->get('dr_id'));

		$uda = &uda::factory('voa_uda_frontend_dailyreport_format');

		// 读取报告信息
		$serv = &service::factory('voa_s_oa_dailyreport', array(
			'pluginid' => startup_env::get('pluginid')
		));
		$dailyreport = $serv->fetch_by_id($dr_id);
		if (empty($dr_id) || empty($dailyreport)) {
			$this->_error_message('dailyreport_is_not_exists');
		}

		if (! $uda->format($dailyreport)) {
			$this->_error_message($uda->error);
			return false;
		}

		// 读取报名目标人和抄送人
		$serv_m = &service::factory('voa_s_oa_dailyreport_mem', array(
			'pluginid' => startup_env::get('pluginid')
		));
		$mems = $serv_m->fetch_by_dr_id($dr_id);
		// 对比 uid 查看的日报uid
		$is_recv = false;
		if (startup_env::get('wbs_uid') != $dailyreport['m_uid']) {
			$is_recv = true;
		}
		// 取出 uid
		$uids = array();
		// 判断用户权限
		$is_permit = false;
		// 抄送人信息
		$ccusers = array();
		// 报告目标人
		$tousers = array();
		// 取用户头像
		foreach ($mems as $v) {
			$uids[$v['m_uid']] = $v['m_uid'];
		}

		$servm = &service::factory('voa_s_oa_member', array(
			'pluginid' => 0
		));
		$users = $servm->fetch_all_by_ids($uids);
		foreach ($mems as $v) {
			if (startup_env::get('wbs_uid') == $v['m_uid'] || 0 == $v['m_uid']) {
				$is_permit = true;
			}

			$v['m_face'] = $users[$v['m_uid']]['m_face'];
			if (self::STATUS_CARBON_COPY == $v['drm_status']) {
				// 抄送人
				if ($v['m_uid'] == $dailyreport['m_uid']) {
					continue;
				}
				$ccusers[] = $v;
			} else {
				// 目标人
				$tousers[] = $v;
			}
		}

		// 判断当前用户是否有权限查看
		if (!$is_permit && startup_env::get('wbs_uid') != $dailyreport['m_uid']) {
			$this->_error_message('no_privilege');
		}

		// 读取报告详情以及回复
		$posts_uid = array();
		$serv_p = &service::factory('voa_s_oa_dailyreport_post', array(
			'pluginid' => startup_env::get('pluginid')
		));
		$posts = $serv_p->fetch_by_dr_id($dr_id);
		foreach ($posts as $k => &$v) {
			$uda->dailyreport_post($v);
			// 如果是报告内容, 则
			if (voa_d_oa_dailyreport_post::FIRST_YES == $v['drp_first']) {
				$msg = preg_replace('/(<br\s*\/?>)+/i', '<br />', $v['_message']);
				$v['_message_li'] = explode('<br />', $msg);
				$v['_message_li'] = array_map('nl2br', $v['_message_li']);
				$dailyreport = array_merge($v, $dailyreport);
				unset($posts[$k]);
				continue;
			} else {
				$v['_message'] = nl2br($v['_message']);
			}
			$posts_uid[$v['m_uid']] = $v['m_uid'];
		}
		unset($v);

		// 读取评论用户信息
		$users = $servm->fetch_all_by_ids($posts_uid);
		foreach ($users as $u) {
			voa_h_user::push($u);
		}

		// 读取日报所有相关文件 by Deepseath@20141222#310
		$attachs = array();
		$serv_drat = &service::factory('voa_s_oa_dailyreport_attachment', array(
			'pluginid' => startup_env::get('pluginid')
		));
		$attach_list = $serv_drat->fetch_all_by_dr_id($dr_id);
		if ($attach_list) {
			// 日报文件所关联的公共附件ID
			$at_ids = array();
			foreach ($attach_list as $v) {
				$at_ids[] = $v['at_id'];
			}

			$serv_at = &service::factory('voa_s_oa_common_attachment', array(
				'pluginid' => 0
			));
			$common_attach_list = $serv_at->fetch_by_ids($at_ids);

			foreach ($attach_list as $v) {
				if (! isset($common_attach_list[$v['at_id']])) {
					continue;
				}
				$at = $common_attach_list[$v['at_id']];
				$attachs[$v['drp_id']][] = array(
					'at_id' => $v['at_id'], // 公共文件附件ID
					'id' => $v['drat_id'], // 日报文件ID
					'filename' => $at['at_filename'], // 附件名称
					'filesize' => $at['at_filesize'], // 附件容量
					'mediatype' => $at['at_mediatype'], // 媒体文件类型
					'description' => $at['at_description'], // 附件描述
					'isimage' => $at['at_isimage'] ? 1 : 0, // 是否是图片
					'url' => voa_h_attach::attachment_url($v['at_id'], 0), // 附件文件url
					'thumb' => $at['at_isimage'] ? voa_h_attach::attachment_url($v['at_id'], 45) : ''
				);
			}
		}
		$p_sets = voa_h_cache::get_instance()->get('plugin.dailyreport.setting', 'oa'); // 读日报配置缓存

		$r_y = (int)$dailyreport['_reporttime_fmt']['Y'];
		$r_m = (int)$dailyreport['_reporttime_fmt']['m'];
		$r_d = (int)$dailyreport['_reporttime_fmt']['d'];
		$weeknames = config::get('voa.misc.weeknames');
		$r_w = $weeknames[$dailyreport['_reporttime_fmt']['w']];
		$r_wn = (int)rgmdate($dailyreport['dr_reporttime'], 'W');
		$r_q = $this->__get_quarter($r_m);

		// 报告类型个性化显示数据
		switch ($dailyreport['dr_type']) {
			case 2 :
				$dshow[1] = $r_y . '年';
				$dshow[2] = $r_wn;
				$dshow[3] = '周';
				$title = ' ' . $r_y . '年第' . $r_wn . '周 ';
				$report_time = $r_y . '年第' . $r_wn . '周 ('.rgmdate($dailyreport['dr_reporttime'], 'm-d').' - '.rgmdate($dailyreport['dr_reporttime'] + 86400 * 6, 'm-d').')';
				break;
			case 3 :
				$dshow[1] = $r_y . '年';
				$dshow[2] = $r_m;
				$dshow[3] = '月份';
				$title = ' ' . $r_y . '年' . $r_m . '月份 ';
				$report_time = $r_y . '年' . $r_m . '月份';
				break;
			case 4 :
				$dshow[1] = $r_y . '年';
				$dshow[2] = $r_q;
				$dshow[3] = '季度';
				$title = ' ' . $r_y . '年第' . $r_q . '季 ';
				$report_time = $r_y . '年第' . $r_q . '季度';
				break;
			case 5 :
				$dshow[1] = '&nbsp;';
				$dshow[2] = $r_y;
				$dshow[3] = '年报';
				$title = ' ' . $r_y . '年度 ';
				$report_time = $r_y . '年度';
				break;
			default :
				$dshow[1] = $r_y . '年' . $r_m . '月';
				$dshow[2] = $r_d;
				$dshow[3] = $r_w;
				$title = ' ' . $r_y . '-' . $r_m . '-' . $r_d . ' ';
				$report_time = $r_y . '-' . $r_m . '-' . $r_d;
		}

		$this->view->set('dailyType', $p_sets['daily_type']); // 日报类型数组
		$this->view->set('action', $this->action_name);
		$this->view->set('dailyreport', $dailyreport);
		$tousers = array_merge($tousers, $ccusers); // 转发人合并到接收人数组里
		// $this->view->set('ccusers', $ccusers);
		$this->view->set('tousers', $tousers);
		$this->view->set('posts', $posts);
		$this->view->set('postsize', count($posts)); // 评论个数
		$this->view->set('weeknames', config::get('voa.misc.weeknames'));
		$this->view->set('dr_id', $dr_id);
		$this->view->set('is_recv', $is_recv);
		// 日报图片
		$this->view->set('attachs', array_key_exists(0, $attachs) ? $attachs[0] : array());
		$this->view->set('navtitle', $dailyreport['m_username'].$title.$p_sets['daily_type'][$dailyreport['dr_type']][0]);
		$this->view->set('dshow', $dshow);
		$this->view->set('report_time', $report_time);
		$this->view->set('report_type', $p_sets['daily_type'][$dailyreport['dr_type']][0]);
		$this->_output('mobile/dailyreport/view');
	}

	/**
	 * 根据月份计算季度
	 * @param number $month
	 * @return string
	 */
	private function __get_quarter($month) {
		if ($month >= 1 && $month <= 3) {
			return '一';
		} elseif ($month >= 4 && $month <= 6) {
			return '二';
		} elseif ($month >= 7 && $month <= 9) {
			return '三';
		} else {
			return '四';
		}
	}

}
