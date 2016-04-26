<?php
/**
 * voa_c_wxwall_frontend_getnewlist
 * 微信墙前端/前台:获取最新消息
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_wxwall_frontend_getnewlist extends voa_c_wxwall_frontend_base {

	public function execute() {

		$perpage = 3;
		$updated = $this->request->get('updated');
		$updated = rintval($updated, false);
		$data = array();

		foreach ($this->_message_list($this->_current_ww_id, $updated, $perpage) AS $_wwp_id => $_wwp) {
			$data[$_wwp_id]['face'] = voa_h_user::avatar($_wwp['m_uid'], $_wwp['_user']);
			$data[$_wwp_id]['username'] = $_wwp['_user']['m_username'];
			$data[$_wwp_id]['message'] = $_wwp['_message'];
			$data[$_wwp_id]['updated'] = $_wwp['wwp_updated'];
			$data[$_wwp_id]['wwp_created'] = rgmdate($_wwp['wwp_created'],'Y-m-d H:i');
			$data[$_wwp_id]['_created'] = rgmdate($_wwp['wwp_created'],'Y-m-d H:i');
		}
		echo $this->_ajax_return($data);
		exit;

	}

	/**
	 * 获取指定微信墙大于某个消息更新时间的若干消息
	 * @param int $ww_id
	 * @param int $updated
	 * @param int $perpage
	 * @return array
	 */
	protected function _message_list($ww_id, $updated, $perpage = 3){
		$serv_wwp = &service::factory('voa_s_oa_wxwall_post', array('pluginid' => startup_env::get('pluginid')));
		$list = $serv_wwp->fetch_by_ww_id_updated($ww_id, $updated, 0, $perpage);
		if (empty($list)) {
			return array();
		}
		$m_uids = array();
		$users = array();
		foreach ($list AS $_wwp_id => $_wwp) {
			if (!isset($m_uids[$_wwp['m_uid']])) {
				$m_uids[$_wwp['m_uid']] = $_wwp['m_uid'];
			}
		}
		unset($_wwp_id,$_wwp);
		$serv_m = &service::factory('voa_s_oa_member', array('pluginid' => 0));
		$users = $serv_m->fetch_all_by_ids($m_uids);
		voa_h_user::push($users);
		foreach ($list AS $_wwp_id => $_wwp) {
			$_wwp['_message'] = rsubstr($_wwp['wwp_message'], 280, ' ...');
			if (isset($users[$_wwp['m_uid']])) {
				$_wwp['_user'] = array(
						'm_face' => voa_h_user::avatar($_wwp['m_uid']),
						'm_gender' => $users[$_wwp['m_uid']]['m_gender'],
						'm_username' => rhtmlspecialchars($users[$_wwp['m_uid']]['m_username']),
				);
			} else {
				$_wwp['_user'] = array(
						'm_face' => voa_h_user::avatar(0, array('m_gender' => 0)),
						'm_gender' => 0,
						'm_username' => '*访客*',
				);
			}
			$_wwp['_message'] = voa_h_wxwall::message_format($_wwp['_message']);
			$list[$_wwp_id] = $_wwp;
		}
		return $list;
	}

}
