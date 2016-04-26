<?php
/**
 * voa_c_admincp_office_dailyreport_view
 * 企业后台/微办公管理/日报/详情查看
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_dailyreport_view extends voa_c_admincp_office_dailyreport_base {

	public function execute() {
	   
		$dr_id = rintval($this->request->get('dr_id'), false);
		/** 主题信息 */
		$dailyreport = array();
		if ($dr_id <= 0 || !($dailyreport = parent::_get_dailyreport($dr_id, $this->_module_plugin_id))) {
			$this->message('error', '指定日报不存在或已被删除');
		}

		/** 抄送人 */
		$cc_users = array();
		/** 目标人 */
		$to_users = array();
		/** 所有涉及的人员 */
		$m_uids = array();

		/** 参与人员列表 */
		$mems = $this->_service_single('dailyreport_mem', $this->_module_plugin_id, 'fetch_by_dr_id', $dr_id);

		foreach ($mems as $v) {
		    if($v['m_uid'] == $dailyreport['m_uid']){
		        continue;
		    }
			if ($v['drm_status'] == $this->uda_dailyreport->mem_status['carbon_copy']) {
				//抄送人
				$cc_users[] = $v;
			} elseif ($v['m_uid'] != $dailyreport['m_uid']) {
				//目标人
				$to_users[] = $v;
			}
			$m_uids[$v['m_uid']] = $v['m_uid'];
		}
		unset($v);
		$users = voa_h_user::get_multi($m_uids);

		$uda_dailyreport = &uda::factory('voa_uda_frontend_dailyreport_format');

		/** 详情及回复 */
		$posts = $this->_service_single('dailyreport_post', $this->_module_plugin_id, 'fetch_by_dr_id', $dr_id);
		foreach ($posts as $k => &$v) {
			if (voa_d_oa_dailyreport_post::FIRST_YES == $v['drp_first']) {
				//如果是报告主题，则与主题信息合并
				$dailyreport = array_merge($v, $dailyreport);
				unset($posts[$k]);
				continue;
			}

			$v['_created'] = rgmdate($v['drp_created'], 'Y-m-d H:i');
		}

		// 读取日报所有相关文件 by Deepseath@20141222#310
		$attachs = array();
		$serv_drat = &service::factory('voa_s_oa_dailyreport_attachment', array('pluginid' => $this->_module_plugin_id));
		$attach_list = $serv_drat->fetch_all_by_dr_id($dr_id);
		if ($attach_list) {
			// 日报文件所关联的公共附件ID
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
				$attachs[$v['drp_id']][] = array(
					'at_id' => $v['at_id'],// 公共文件附件ID
					'id' => $v['drat_id'], // 日报文件ID
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

		$p_sets = voa_h_cache::get_instance()->get('plugin.dailyreport.setting', 'oa');//读日报配置缓存
		
		/** 格式化日报主表信息 */
		$uda_dailyreport->format($dailyreport, isset($users[$dailyreport['m_uid']]) ? $users[$dailyreport['m_uid']] : array());
		$this->view->set('dailyreport', $dailyreport);
		$this->view->set('cc_users', $cc_users);//转发人
		$this->view->set('to_users', $to_users);//接收人
		$this->view->set('posts', $posts);
		$this->view->set('posts_total', count($posts));
		$this->view->set('weeknames', config::get('voa.misc.weeknames'));
		// 日报图片
		$this->view->set('attach_list', array_key_exists(0, $attachs) ? $attachs[0] : array());
		$this->view->set('dailyType',$p_sets['daily_type']);//日报类型数组
		$this->output('office/dailyreport/dailyreport_view');

	}

}
