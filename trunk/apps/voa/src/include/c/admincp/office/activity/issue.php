<?php
/**
 * voa_c_admincp_office_news_issue
 * 企业后台/微办公管理/新闻公告/用户H5发布权限
 * @date: 2015年5月8日
 * @author: kk
 * @version:
 */

class voa_c_admincp_office_activity_issue extends voa_c_admincp_office_activity_base {
	public function execute() {
		//是否有提交数据
		if($this->_is_post()) {
			try {
				$uda = &uda::factory('voa_uda_frontend_activity_issue');
				$uda->add($_POST);
			} catch (help_exception $h) {
				$this->_admincp_error_message($h);
			} catch (Exception $e) {
				logger::error($e);
				$this->_admincp_system_message($e);
			}
			$this->message('success', '前台签到权限成功', $this->cpurl($this->_module, $this->_operation, 'list', $this->_module_plugin_id), false);
		}

		$p_setting = voa_h_cache::get_instance()->get('plugin.activity.setting', 'oa');

		// 默认数据
		$default_departemt = array();
		$default_member = array();
		$all = isset($p_setting['all']) ? (int)$p_setting['all'] : 0; //all签到权限   0=>非全部 1=>全部

		if((isset($p_setting['cd_ids']) || isset($p_setting['m_uids'])) && $all === 0) {

			// 判断是否存在部门列表 如果存在 清除空数组
			if (!empty($p_setting['cd_ids'])) {
				$p_setting['cd_ids'] = array_filter($p_setting['cd_ids']);
			}

			// 判断是否存在成员列表 如果存在 清除空数组
			if (!empty($p_setting['m_uids'])) {
				$p_setting['m_uids'] = array_filter($p_setting['m_uids']);
			}

			if ($p_setting['cd_ids'] ) {
				$cd_ids = $p_setting['cd_ids'];
				$serv_d = &service::factory('voa_s_oa_common_department', array('pluginid' => 0));
				$depms = $serv_d->fetch_all_by_key($cd_ids);
				foreach ($p_setting['cd_ids'] as $right) {
					$default_departemt[] = array(
						'id' => $right,
						'name' => isset($depms[$right]['cd_name']) ? $depms[$right]['cd_name'] : '',
						'isChecked' => (bool)true,
					);
				}
			}
			if ($p_setting['m_uids']) {
				$m_uids = $p_setting['m_uids'];
				$serv_m = &service::factory('voa_s_oa_member', array('pluginid' => 0));
				$users = $serv_m->fetch_all_by_ids($m_uids);
				foreach ($p_setting['m_uids'] as $right) {
					$default_member[] = array(
						'm_uid' => $right,
						'm_username' => isset($users[$right]['m_username']) ? $users[$right]['m_username'] : '',
						'selected' => (bool)true,
					);
				}
			}
		}

		$this->view->set('url', $this->cpurl($this->_module, $this->_operation, 'list', $this->_module_plugin_id));
		$this->view->set('default_departemt', rjson_encode(array_values($default_departemt)));
		$this->view->set('default_member', rjson_encode(array_values($default_member)));
		$this->view->set('default_all', $all);
		$this->output('office/activity/issue');
	}
}
