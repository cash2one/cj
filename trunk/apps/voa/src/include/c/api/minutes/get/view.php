<?php
/**
 * voa_c_api_minutes_get_list
 * 查看会议纪要
 * $Author$
 * $Id$
 */

class voa_c_api_minutes_get_view extends voa_c_api_minutes_base {
	/** 正常 */
	const STATUS_NORMAL = 1;
	/** 已更新 */
	const STATUS_UPDATE = 2;
	/** 抄送人 */
	const STATUS_CARBON_COPY = 3;
	/** 已删除 */
	const STATUS_REMOVE = 4;

	public function execute() {

		// 请求参数
		$fields = array(
			// 日报ID
			'mi_id' => array('type' => 'int', 'required' => true),
		);
		if (!$this->_check_params($fields)) {
			return false;
		}
		/** 会议纪要ID */
		$mi_id =  $this->_params['mi_id'];

		$fmt = &uda::factory('voa_uda_frontend_minutes_format');
		
		/** 读取会议纪要信息 */
		$serv = &service::factory('voa_s_oa_minutes', array('pluginid' => startup_env::get('pluginid')));
		$minutes = $serv->fetch_by_id($mi_id);
		if (empty($mi_id) || empty($minutes)) {
			return $this->_set_errcode('minutes_is_not_exists');
		}

		if (!$fmt->minutes($minutes)) {
			//$this->_error_message($fmt->error);
			$this->_errcode = $uda->errcode;
			$this->_errmsg = $uda->errmsg;
			return false;
		}

		/** 读取参会人和抄送人 */
		$serv_m = &service::factory('voa_s_oa_minutes_mem', array('pluginid' => startup_env::get('pluginid')));
		$mems = $serv_m->fetch_by_mi_id($mi_id);
		/** 取出 uid */
		$uids = array();
		/** 判断用户权限 */
		$is_permit = false;
		/** 抄送人信息 */
		$ccusers = array();
		/** 参会人 */
		$tousers = array();
		/** 作者 */
		$mi_author = array();
		foreach ($mems as $v) {
			if (startup_env::get('wbs_uid') == $v['m_uid'] || 0 == $v['m_uid']) {
				$is_permit = true;
			}

			if ($v['m_uid'] == $minutes['m_uid']) {
				$mi_author = $v;
			} elseif (self::STATUS_CARBON_COPY == $v['mim_status']) {/** 抄送人 */
				$ccusers[] = $v;
			} else {/** 参会人 */
				$tousers[] = $v;
			}

			$uids[$v['m_uid']] = $v['m_uid'];
		}

		/** 判断当前用户是否有权限查看 */
		if (!$is_permit && startup_env::get('wbs_uid') != $minutes['m_uid']) {
			//$this->_error_message('no_privilege');
			return $this->_set_errcode(voa_errcode_api_minutes::NO_PRIVILEGE);
		}

		/** 读取会议纪要详情以及回复 */
		$serv_p = &service::factory('voa_s_oa_minutes_post', array('pluginid' => startup_env::get('pluginid')));
		$posts = $serv_p->fetch_by_mi_id($mi_id);
	
		/** 整理输出回复数组 */
		$posts_json = array();
		foreach ($posts as $k => &$v) {
			if (!$fmt->minutes_post($v)) {
				//$this->_error_message($fmt->error);
				$this->_errcode = $uda->errcode;
				$this->_errmsg = $uda->errmsg;
				return false;
			}

			/** 如果是会议纪要内容, 则 */
			if (voa_d_oa_minutes_post::FIRST_YES == $v['mip_first']) {
				$minutes = array_merge($v, $minutes);
				unset($posts[$k]);
				continue;
			}
			$posts_json[$k]['uid'] = $v['m_uid'];
			$posts_json[$k]['username'] = $v['m_username'];
			$posts_json[$k]['message'] = $v['_message'];
			$posts_json[$k]['created'] = $v['mip_created'];
		}
		unset($v);

		/** 读取用户信息 */
		$servm = &service::factory('voa_s_oa_member', array('pluginid' => 0));
		$users = $servm->fetch_all_by_ids($uids);
		foreach ($users as $u) {
			voa_h_user::push($u);
		}
		/** 处理接收人数组 */
		$tousers_josn = array();
		foreach ($tousers as $key => $value) {
			$tousers_josn[$value['m_uid']]['uid'] = $value['m_uid'];
			$tousers_josn[$value['m_uid']]['username'] = $value['m_username'];
			$tousers_josn[$value['m_uid']]['avatar'] = voa_h_user::avatar($value['m_uid'], isset($users[$value['m_uid']]) ? $users[$value['m_uid']] : array());
		}

		/** 处理抄送人数组 */
		$ccusers_josn = array();
		foreach ($ccusers as $key => $value) {
			$ccusers_josn[$value['m_uid']]['uid'] = $value['m_uid'];
			$ccusers_josn[$value['m_uid']]['username'] = $value['m_username'];
			$ccusers_josn[$value['m_uid']]['avatar'] = voa_h_user::avatar($value['m_uid'], isset($users[$value['m_uid']]) ? $users[$value['m_uid']] : array());
		}
		/** 重组返回json数组 */
		$this->_result = array(
			'id' => $mi_id,
			'minutes' => array(
				'uid' => $minutes['m_uid'],// 创建者uid
				'username' => $minutes['m_username'],// 创建者名字
				'avatar' => voa_h_user::avatar($minutes['m_uid']),// 创建者名字
				'message' => $minutes['mip_message'],// 会议记录内容
				'subject' => $minutes['mip_subject'],// 会议记录主题 
				'created' => $minutes['mi_created'],// 会议记录创建时间
				'updated' => $minutes['mi_updated'],// 会议记录更新时间
			),
			'posts' => array_values($posts_json),
			'ccusers' => array_values($ccusers_josn),
			'tousers' => array_values($tousers_josn),
		);
		return true;

	}

}
