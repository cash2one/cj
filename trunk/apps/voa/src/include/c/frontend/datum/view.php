<?php
/**
 * 查看报告
 * $Author$
 * $Id$
 */

class voa_c_frontend_datum_view extends voa_c_frontend_datum_base {
	/** 正常 */
	const STATUS_NORMAL = 1;
	/** 已更新 */
	const STATUS_UPDATE = 2;
	/** 抄送人 */
	const STATUS_CARBON_COPY = 3;
	/** 已删除 */
	const STATUS_REMOVE = 4;

	public function execute() {
		/** 报告ID */
		$dr_id = rintval($this->request->get('dr_id'));

		/** 读取报告信息 */
		$serv = &service::factory('voa_s_oa_datum', array('pluginid' => startup_env::get('pluginid')));
		$datum = $serv->fetch_by_id($dr_id);
		if (empty($dr_id) || empty($datum)) {
			$this->_error_message('datum_is_not_exists');
		}

		$datum['dr_subject'] = rhtmlspecialchars($datum['dr_subject']);

		/** 读取报名目标人和抄送人 */
		$serv_m = &service::factory('voa_s_oa_datum_mem', array('pluginid' => startup_env::get('pluginid')));
		$mems = $serv_m->fetch_by_dr_id($dr_id);
		/** 取出 uid */
		$uids = array();
		/** 判断用户权限 */
		$is_permit = false;
		/** 抄送人信息 */
		$ccusers = array();
		/** 报告目标人 */
		$tousers = array();
		foreach ($mems as $v) {
			if (startup_env::get('wbs_uid') == $v['m_uid'] || 0 == $v['m_uid']) {
				$is_permit = true;
			}

			if (self::STATUS_CARBON_COPY == $v['drm_status']) {/** 抄送人 */
				$ccusers[] = $v;
			} else if ($v['m_uid'] != startup_env::get('wbs_uid')) {/** 目标人 */
				$tousers[] = $v;
			}

			$uids[$v['m_uid']] = $v['m_uid'];
		}

		/** 判断当前用户是否有权限查看 */
		if (!$is_permit && startup_env::get('wbs_uid') != $datum['m_uid']) {
			$this->_error_message('no_privilege');
		}

		/** 读取报告详情以及回复 */
		$serv_p = &service::factory('voa_s_oa_datum_post', array('pluginid' => startup_env::get('pluginid')));
		$posts = $serv_p->fetch_by_dr_id($dr_id);
		foreach ($posts as $k => &$v) {
			$v['drp_subject'] = rhtmlspecialchars($v['drp_subject']);
			$v['drp_message'] = bbcode::instance()->bbcode2html($v['drp_message']);
			/** 如果是报告内容, 则 */
			/**if (voa_d_oa_datum_post::FIRST_YES == $v['drp_first']) {
				$datum = array_merge($v, $datum);
				unset($posts[$k]);
				continue;
			}*/

			$v['_created'] = rgmdate($v['drp_created'], 'u');
		}

		unset($v);

		$this->view->set('action', $this->action_name);
		$this->view->set('datum', $datum);
		$this->view->set('ccusers', $ccusers);
		$this->view->set('tousers', $tousers);
		$this->view->set('posts', $posts);
		$this->view->set('navtitle', '查看报告');

		$this->_output('datum/view');
	}

}
