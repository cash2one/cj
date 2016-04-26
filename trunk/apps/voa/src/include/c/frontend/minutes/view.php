<?php
/**
 * 查看会议纪要
 * $Author$
 * $Id$
 */

class voa_c_frontend_minutes_view extends voa_c_frontend_minutes_base {
	/** 正常 */
	const STATUS_NORMAL = 1;
	/** 已更新 */
	const STATUS_UPDATE = 2;
	/** 抄送人 */
	const STATUS_CARBON_COPY = 3;
	/** 已删除 */
	const STATUS_REMOVE = 4;

	public function execute() {
		$fmt = &uda::factory('voa_uda_frontend_minutes_format');
		/** 会议纪要ID */
		$mi_id = rintval($this->request->get('mi_id'));

		/** 读取会议纪要信息 */
		$serv = &service::factory('voa_s_oa_minutes', array('pluginid' => startup_env::get('pluginid')));
		$minutes = $serv->fetch_by_id($mi_id);
		if (empty($mi_id) || empty($minutes)) {
			$this->_error_message('minutes_is_not_exists');
			return false;
		}

		if (!$fmt->minutes($minutes)) {
			$this->_error_message($fmt->error);
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
			$this->_error_message('no_privilege');
		}

		/** 读取会议纪要详情以及回复 */
		$serv_p = &service::factory('voa_s_oa_minutes_post', array('pluginid' => startup_env::get('pluginid')));
		$posts = $serv_p->fetch_by_mi_id($mi_id);
		foreach ($posts as $k => &$v) {
			if (!$fmt->minutes_post($v)) {
				$this->_error_message($fmt->error);
				return false;
			}

			/** 如果是会议纪要内容, 则 */
			if (voa_d_oa_minutes_post::FIRST_YES == $v['mip_first']) {
				$minutes = array_merge($v, $minutes);
				unset($posts[$k]);
				continue;
			}
		}

		unset($v);

		/** 读取用户信息 */
		$servm = &service::factory('voa_s_oa_member', array('pluginid' => 0));
		$users = $servm->fetch_all_by_ids($uids);
		foreach ($users as $u) {
			voa_h_user::push($u);
		}

		// 读取会议记录所有相关文件 by Deepseath@20141230#391
		$attachs = array();
		$serv_miat = &service::factory('voa_s_oa_minutes_attachment', array('pluginid' => startup_env::get('pluginid')));
		$attach_list = $serv_miat->fetch_all_by_mi_id($mi_id);
		if ($attach_list) {
			// 会议记录文件所关联的公共附件ID
			$at_ids = array();
			foreach ($attach_list as $v) {
				$at_ids[] = $v['at_id'];
			}

			$serv_at = &service::factory('voa_s_oa_common_attachment', array('pluginid' => 0));
			$common_attach_list = $serv_at->fetch_by_ids($at_ids);

			foreach ($attach_list as $v) {
				if (!isset($common_attach_list[$v['at_id']])) {
					continue;
				}
				$at = $common_attach_list[$v['at_id']];
				$attachs[$v['mip_id']][] = array(
					'at_id' => $v['at_id'],// 公共文件附件ID
					'id' => $v['miat_id'], // 会议记录文件会议记录
					'filename' => $at['at_filename'],// 附件名称
					'filesize' => $at['at_filesize'],// 附件容量
					'mediatype' => $at['at_mediatype'],// 媒体文件类型
					'description' => $at['at_description'],// 附件描述
					'isimage' => $at['at_isimage'] ? 1 : 0,// 是否是图片
					'url' => voa_h_attach::attachment_url($v['at_id'], 0),// 附件文件url
					'thumb' => $at['at_isimage'] ? voa_h_attach::attachment_url($v['at_id'], 45) : '',// 缩略图URL
				);
			}
		}

		$this->view->set('action', $this->action_name);
		$this->view->set('minutes', $minutes);
		$this->view->set('ccusers', $ccusers);
		$this->view->set('tousers', $tousers);
		$this->view->set('posts', $posts);
		$this->view->set('attachs', array_key_exists(0, $attachs) ? $attachs[0] : array());

		$this->_output('minutes/view');
	}

}
