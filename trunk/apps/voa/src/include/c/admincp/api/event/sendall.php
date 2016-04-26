<?php
/**
 * Created by PhpStorm.
 * User: Muzhitao
 * Date: 2015/12/28 0028
 * Time: 17:59
 * Email：muzhitao@vchangyi.com
 */
class voa_c_admincp_api_event_sendall extends voa_c_admincp_api_event_base {

	public function execute() {

		$acid = $this->request->post('acid');

		$serv = &service::factory('voa_s_oa_event');
		$detail = $serv->get($acid);
		$uids = $this->_unread_data($acid, $detail);
		$settings = voa_h_cache::get_instance()->get('setting', 'oa');

		// 获取agentid
		$plugins = voa_h_cache::get_instance()->get('plugin', 'oa');
		startup_env::set('pluginid', $this->_p_sets['pluginid']);
		if(isset($plugins[$this->_p_sets['pluginid']]['cp_agentid'])){
			startup_env::set('agentid', $plugins[$this->_p_sets['pluginid']]['cp_agentid']);
		}
		// 发送微信消息e
		$msg_title = "[活动]".$detail['title'];
		$scheme = config::get('voa.oa_http_scheme');
		$msg_desc = "活动时间:".rgmdate($detail['start_time'], 'Y-m-d')."\n";
		$msg_desc .= "活动地点：" .$detail['province'].$detail['city'].$detail['area'].$detail['street'];
		$msg_url = $scheme . $settings['domain'] . '/previewh5/micro-community/index.html#/app/page/activity/activity-detail?id=' . $acid;
		$msg_picurl = voa_h_attach::attachment_url($detail['at_ids']);
		// 发消息
		voa_h_qymsg::push_news_send_queue($this->session, $msg_title, $msg_desc, $msg_url, $uids, '', $msg_picurl);

		// 返回结果
		$result = array(

		);

		return $this->_output_result($result);
	}

	/**
	 * 获取未报名人员
	 * @param $acid
	 * @return array
	 */
	private function _unread_data($acid, $activity) {

		$serv = &service::factory('voa_s_oa_event_partake');
		$conds = array('acid' => $acid, 'check' => 0, 'openid' => '');

		// 已经参加的人员
		$join_list = $serv->list_by_conds($conds);

		// 全体人员
		if($activity['is_all'] == 1){
			$serv_m = &service::factory('voa_s_oa_member', array('pluginid' => 0));
			$member = $serv_m->fetch_all();
			$list = array_column($member, 'm_uid');
			if(empty($join_list)){
				$list = '@all';
			}

		} else {
			$serv_invite = &service::factory('voa_s_oa_event_invite');
			//全部人员
			$conds = array('acid' => $acid);
			$all_list = $serv_invite->list_by_conds($conds);

			$m_uids = $cd_ids = array();
			foreach ($all_list as $right) {
				//获取部门
				if ($right['type'] == 1) {
					$cd_ids[] = $right['primary_id'];
				}
				//获取人员
				if ($right['type'] == 2) {
					$m_uids[] = $right['primary_id'];
				}
			}

			//获取部门下的人员
			if ($cd_ids) {
				$p_m_uids = $this->__in_department($cd_ids);
				$m_uids = array_merge($m_uids,$p_m_uids);
			}

			//删除重复人员
			$list = array_flip($m_uids);

			if (!empty($join_list)) {
				foreach($join_list as $k => $_v) {
					if (isset($list[$_v['m_uid']])) {
						unset($list[$_v['m_uid']]);
					}
				}
			}
			$list = array_values(array_flip($list));
		}

		return $list;
	}


	/**
	 * 查询所在部门下的人员
	 * @param $cd_ids
	 * @param $m_uids
	 * @param $page
	 * @param $limit
	 * @return array
	 */
	private function __in_department($cd_ids, $m_uids=array()) {

		$serv_md = &service::factory('voa_s_oa_member');

		$condi['cd_id'] = array($cd_ids, 'IN');
		if ($m_uids) {
			$condi['m_uid'] = array($m_uids, 'IN');
		}
		$list = $serv_md->fetch_all_by_conditions($condi);

		if ($list) {
			return array_column($list, 'm_uid');
		}

		return array();
	}
}
