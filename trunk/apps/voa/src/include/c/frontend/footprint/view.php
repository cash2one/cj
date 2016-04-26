<?php
/**
 * 查看销售轨迹
 * $Author$
 * $Id$
 */

class voa_c_frontend_footprint_view extends voa_c_frontend_footprint_base {
	/** 正常 */
	const STATUS_NORMAL = 1;
	/** 已更新 */
	const STATUS_UPDATE = 2;
	/** 抄送人 */
	const STATUS_CARBON_COPY = 3;
	/** 已删除 */
	const STATUS_REMOVE = 4;

	public function execute() {
		/** 销售轨迹ID */
		$fp_id = rintval($this->request->get('fp_id'));

		$uda = &uda::factory('voa_uda_frontend_footprint_format');

		/** 读取销售轨迹信息 */
		$serv = &service::factory('voa_s_oa_footprint', array('pluginid' => startup_env::get('pluginid')));
		$footprint = $serv->fetch_by_id($fp_id);
		if (empty($fp_id) || empty($footprint)) {
			$this->_error_message('footprint_is_not_exists');
		}

		if (!$uda->format($footprint)) {
			$this->_error_message($uda->error);
			return false;
		}

		/** 读取报名目标人和抄送人 */
		$serv_m = &service::factory('voa_s_oa_footprint_mem', array('pluginid' => startup_env::get('pluginid')));
		$mems = $serv_m->fetch_by_fp_id($fp_id);
		/** 取出 uid */
		$uids = array();
		/** 判断用户权限 */
		$is_permit = false;
		/** 抄送人信息 */
		$ccusers = array();
		foreach ($mems as $v) {
			$uids[$v['m_uid']] = $v['m_uid'];
			if (startup_env::get('wbs_uid') == $v['m_uid'] || 0 == $v['m_uid']) {
				$is_permit = true;
			}

			if (self::STATUS_CARBON_COPY == $v['fpm_status']) {/** 抄送人 */
				if ($v['m_uid'] == $footprint['m_uid']) {
					continue;
				}

				$ccusers[] = $v;
			}
		}

		/** 判断当前用户是否有权限查看 */
		if (!$is_permit && startup_env::get('wbs_uid') != $footprint['m_uid']) {
			$this->_error_message('no_privilege');
		}

		$this->view->set('action', $this->action_name);
		$this->view->set('footprint', $footprint);
		$this->view->set('ccusers', $ccusers);
		$this->view->set('weeknames', config::get('voa.misc.weeknames'));
		$this->view->set('fp_id', $fp_id);
		$this->view->set('navtitle', '查看销售轨迹');

		$this->_output('footprint/view');
	}

}
