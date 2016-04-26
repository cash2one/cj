<?php
/**
 * voa_c_wxwall_admincp_verify_list
 * 微信墙前端/管理:墙内容列表
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_wxwall_admincp_verify_list extends voa_c_wxwall_admincp_verify_base {

	public function execute() {

		$postverify = $this->request->get('postverify');
		$updateStatusUrlBase = $this->wxwall_admincp_url($this->_module, $this->_action, array('viewstatus' => ''));

		$post_status_description = array();
		foreach (voa_h_wxwall::$post_status_description AS $_status => $_name) {
			if ($_status != voa_h_wxwall::$post_status_remove) {
				$post_status_description[$_status] = $_name;
			}
		}

		if (isset($_GET['viewstatus'])) {
			$viewStatus = $this->request->get('viewstatus');
		} else {
			$viewStatus = 1;
		}
		$viewStatus = rintval($viewStatus, false);
		if (!isset($post_status_description[$viewStatus])) {
			$viewStatus = 1;
		}

		list($total, $msgList, $multi) = $this->wwp_list($this->_current_ww_id, $viewStatus);
		$this->view->set('msgList', $msgList);
		$this->view->set('total', $total);
		$this->view->set('multi', $multi);
		$this->view->set('updateStatusUrlBase', $updateStatusUrlBase);
		$this->view->set('viewStatus', $viewStatus);
		$this->view->set('post_status_descriptions', $post_status_description);
		$this->view->set('navTitle', $this->_admincp_actions[$this->_module]['name'].' - '.$this->_current_wxwall['ww_subject']);

		$this->view->set('deleteUrlBase', $this->wxwall_admincp_url($this->_module, 'update', array('setstatus' => '-4', 'wwp_id' => '')));

		$setStatus = array(
				-1 => '上墙',
				-2 => '下墙',
				-3 => '上墙',
				-4 => '删除',
		);

		$setStatusUrlBase = array();
		foreach ($post_status_description AS $_code => $_name) {
			$_set_code = 0 - $_code;
			$setStatusUrlBase[$_code] = array(
					'url' => $this->wxwall_admincp_url($this->_module, 'update', array('setstatus' => $_set_code, 'wwp_id' => '')),
					'name' => $setStatus[$_set_code],
					'classname' => $_set_code == -2 ? 'warning' : 'success'
			);
		}
		$this->view->set('setStatusUrlBase', $setStatusUrlBase);

		$this->output('wxwall/admincp/verify/list');

	}

	/**
	 * 读取指定状态的微信墙消息
	 * @param int $ww_id
	 * @param int $status
	 * @param string $mpurl
	 */
	public function wwp_list($ww_id, $status = 2){

		$perpage = 15;

		$serv_wwp = &service::factory('voa_s_oa_wxwall_post', array('pluginid' => startup_env::get('pluginid')));
		$total = $serv_wwp->count_by_ww_id($ww_id, $status);
		$multi = '';
		$list = array();
		if ($total > 0) {
			$perpage = 15;
			$pagerOptions = array(
					'total_items' => $total,
					'per_page' => $perpage,
					'current_page' => $this->request->get('page'),
					'show_total_items' => true,
			);
			$multi = pager::make_links($pagerOptions);
			pager::resolve_options($pagerOptions);
			$tmp = $serv_wwp->fetch_by_ww_id($ww_id, $status, $pagerOptions['start'], $pagerOptions['per_page']);
			$wwpStatus = voa_h_wxwall::$post_status_description;
			$m_uids = array();
			foreach ($tmp AS $_wwp_id => $_wwp) {
				if (!isset($m_uids[$_wwp['m_uid']])) {
					$m_uids[$_wwp['m_uid']] = $_wwp['m_uid'];
				}
			}
			$serv_m = &service::factory('voa_s_oa_member', array('pluginid' => 0));
			$users = $serv_m->fetch_all_by_ids($m_uids);
			foreach ($tmp AS $_wwp_id => $_wwp) {
				$_wwp['_created'] = rgmdate($_wwp['wwp_created'],'Y-m-d H:i');
				$_wwp['_status'] = isset($wwpStatus[$_wwp['wwp_status']]) ? $wwpStatus[$_wwp['wwp_status']] : $wwpStatus['wwp_status'];
				$_wwp['_user'] = isset($users[$_wwp['m_uid']]) ? array('m_face'=>$users[$_wwp['m_uid']]['m_face'],'m_gender'=>$users[$_wwp['m_uid']]['m_gender']) : array('m_face'=>'','m_gender'=>0);
				$_wwp['_face'] = voa_h_user::avatar($_wwp['m_uid'], isset($users[$_wwp['m_uid']]) ? $users[$_wwp['m_uid']] : array());
				$_wwp['_message'] = voa_h_wxwall::message_format($_wwp['wwp_message']);
				$list[$_wwp_id]	=	$_wwp;
			}
			unset($tmp);
		}
		return array($total,$list,$multi);
	}



}
