<?php
/**
 * voa_c_admincp_office_askfor_view
 * 企业后台 - 审批流 - 详情查看
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_askfor_view extends voa_c_admincp_office_askfor_base {

	/** 抄送状态值 */
	protected $_askfor_carbon_copy_status = voa_d_oa_askfor_proc::STATUS_CARBON_COPY;

	/** 审核进程状态文字描述 */
	protected $_askfor_proc_status_descriptions = array(
			voa_d_oa_askfor_proc::STATUS_NORMAL => '审批中',
			voa_d_oa_askfor_proc::STATUS_APPROVE => '已批准',
			voa_d_oa_askfor_proc::STATUS_APPROVE_APPLY => '通过并转审批',
			voa_d_oa_askfor_proc::STATUS_REFUSE => '审核未通过',
			voa_d_oa_askfor_proc::STATUS_CARBON_COPY => '抄送',
			voa_d_oa_askfor_proc::STATUS_CANCEL => '已撤销',
			voa_d_oa_askfor_proc::STATUS_REMINDER => '已催办',
			voa_d_oa_askfor_proc::STATUS_REMOVE => '已删除',
	);

	/** 审核进程状态样式定义 */
	protected $_askfor_proc_status_class_tag = array(
			voa_d_oa_askfor_proc::STATUS_NORMAL => 'primary',//审批中
			voa_d_oa_askfor_proc::STATUS_APPROVE => 'success',//已批准
			voa_d_oa_askfor_proc::STATUS_APPROVE_APPLY => 'info',//通过并转审批
			voa_d_oa_askfor_proc::STATUS_REFUSE => 'danger',//审核未通过
			voa_d_oa_askfor_proc::STATUS_CARBON_COPY => 'default',//抄送
			voa_d_oa_askfor_proc::STATUS_CANCEL => 'danger', //已撤销
			voa_d_oa_askfor_proc::STATUS_REMINDER => 'danger', //已催办
			voa_d_oa_askfor_proc::STATUS_REMOVE => 'warning',//已删除
	);


	public function execute() {

		/** 当前浏览的审批ID */
		$af_id = $this->request->get('af_id');
		if ( !$af_id || !($askfor = $this->_askfor_get($this->_module_plugin_id, $af_id)) || empty($askfor['af_id']) ) {
			$this->message('error', '指定审批信息不存在 或 已被删除');
		}
		$askfor = $this->_askfor_format($askfor);

		/** 当前审批涉及到的所有用户id */
		$uids = array();

		/** 申请人 */
		if ( $askfor['m_uid'] ) {
			$uids[$askfor['m_uid']] = $askfor['m_uid'];
		}

		/** 该审批所有进程，原始数据 */
		$procList = $this->_service_single('askfor_proc', $this->_module_plugin_id, 'fetch_by_af_id', $af_id, 0, 0);
		/** 提取所有进程内的用户uid */
		foreach ( $procList AS $_afp_id => $_afp ) {
			if ( !isset($uids[$_afp['m_uid']]) ) {
				$uids[$_afp['m_uid']] = $_afp['m_uid'];
			}
		}
		unset($_afp, $_afp_id);

		/** 该审批所有评论，原始数据 */
		$commentListTmp = $this->_service_single('askfor_comment', $this->_module_plugin_id, 'fetch_by_af_id', $af_id, 0, 0);
		/** 所有评论id */
		$afc_ids = array_keys($commentListTmp);
		/** 提取所有评论人id */
		foreach ( $commentListTmp AS $_afc_id => $_afc ) {
			if ( !isset($uids[$_afc['m_uid']]) ) {
				$uids[$_afc['m_uid']] = $_afc['m_uid'];
			}
		}
		unset($_afc_id, $_afc);

		/** 所有评论的回复，原始数据 */
		if ( !empty($afc_ids) ) {
			$replyList = $this->_service_single('askfor_reply', $this->_module_plugin_id, 'fetch_all_by_afc_ids', $afc_ids);
		} else {
			$replyList = array();
		}
		/** 评论回复中涉及的用户id */
		foreach ( $replyList AS $_tmp ) {
			foreach ( $_tmp AS $_afr_id => $_afr ) {
				if ( !isset($uids[$_afr['m_uid']]) ) {
					$uids[$_afr['m_uid']] = $_afr['m_uid'];
				}
			}
		}
		unset($_tmp, $_afr_id, $_afr);

		/** 所有涉及到本审核的用户列表 */
		$memberList = $this->_service_single('member', $this->_module_plugin_id, 'fetch_all_by_ids', $uids);
		unset($uids);

		/** 审核进程数 */
		$countProc = 0;
		/** 评论数 */
		$countComment = 0;
		/** 评论回复数 */
		$countReply = 0;

		/** 抄送人 */
		$ccMemberList = array();
		/** 审核人 */
		$procMemberList = array();

		foreach ( $procList AS $_afp_id => $_afp ) {

			/** 抄送人 */
			if ( $_afp['afp_status'] == $this->_askfor_carbon_copy_status ) {
				$ccMemberList[$_afp['m_uid']] = isset($memberList[$_afp['m_uid']]) ? $memberList[$_afp['m_uid']]['m_username'] : $_afp['m_username'];
				unset($procList[$_afp_id]);
				continue;
			}

			/** 审批信息 */
			$procList[$_afp_id]['_status'] = isset($this->_askfor_proc_status_descriptions[$_afp['afp_status']]) ? $this->_askfor_proc_status_descriptions[$_afp['afp_status']] : '';
			$procList[$_afp_id]['_status_class_tag'] = isset($this->_askfor_proc_status_class_tag[$_afp['afp_status']]) ? $this->_askfor_proc_status_class_tag[$_afp['afp_status']] : '';
			$procList[$_afp_id]['_created'] = rgmdate($_afp['afp_created'],'Y-m-d H:i');
			$procList[$_afp_id]['_username'] = isset($memberList[$_afp['m_uid']]) ? $memberList[$_afp['m_uid']]['m_username'] : $_afp['m_username'];

			/** 审批人 */
			if ($_afp['afp_status'] != voa_d_oa_askfor_proc::STATUS_REMINDER && $_afp['afp_status'] != voa_d_oa_askfor_proc::STATUS_CANCEL) {
				$procMemberList[$_afp['m_uid']] = $procList[$_afp_id]['_username'];
			}

			/** 审核进程+1 */
			$countProc++;

		}

		/** 整理后的评论列表 */
		$commentList = array();
		foreach ( $commentListTmp AS $_afc_id=>$_afc ) {
			$_afc['_username'] = isset($memberList[$_afc['m_uid']]) ? $memberList[$_afc['m_uid']]['m_username'] : '';
			$_afc['_created'] = rgmdate($_afc['afc_created'],'Y-m-d H:i');
			$_afc['_message'] = $this->_bbcode2html($_afc['afc_message']);

			/** 评论数+1 */
			$countComment++;

			/** 评论数据 */
			$commentList[$_afc_id]['_thread'] = $_afc;
			/** 回复数据 */
			$commentList[$_afc_id]['_reply'] = array();

			/** 整理回复数据 */
			if ( !empty($replyList[$_afc_id]) ) {
				foreach ( $replyList[$_afc_id] AS $_afr_id=>$_afr ) {
					$_afr['_username'] = isset($memberList[$_afr['m_uid']]) ? $memberList[$_afr['m_uid']]['m_username'] : '';
					$_afr['_created'] = rgmdate($_afr['afr_created'],'Y-m-d H:i');
					$_afr['_message'] = $this->_bbcode2html($_afr['afr_message']);
					$commentList[$_afc_id]['_reply'][$_afr_id] = $_afr;
					/** 回复数+1 */
					$countReply++;
				}
				unset($_afr_id, $_afr);
			}
		}
		unset($commentListTmp, $_afc_id, $_afc);

		// 读取审批所有相关文件 by Deepseath@20141226#332
		$attachs = array();
		$serv_aoat = &service::factory('voa_s_oa_askfor_attachment', array('pluginid' => $this->_module_plugin_id));
		$attach_list = $serv_aoat->fetch_all_by_af_id($af_id);
		if ($attach_list) {
			// 审批文件所关联的公共附件ID
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
				$attachs[$v['afc_id']][] = array(
					'at_id' => $v['at_id'],// 公共文件附件ID
					'id' => $v['af_id'], // 审批文件ID
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

		$this->view->set('af_id', $af_id);
		$this->view->set('askfor', $askfor);
		$this->view->set('ccMemberList', $ccMemberList);
		$this->view->set('procList', $procList);
		$this->view->set('commentList', $commentList);
		$this->view->set('procMemberList', $procMemberList);
		$this->view->set('countProc', $countProc);
		$this->view->set('countComment', $countComment);
		$this->view->set('countReply', $countReply);
		// 审批图片
		$this->view->set('attach_list', array_key_exists(0, $attachs) ? $attachs[0] : array());

		$this->output('office/askfor/view');

	}

}
