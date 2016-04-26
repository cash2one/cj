<?php
/**
 * Created by PhpStorm.
 * User: lixue
 * Date: 15/11/6
 * Time: 上午10:53
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

		//当前浏览的审批ID
		$af_id = $this->request->get('af_id');
		/*if ( !$af_id || !($askfor = $this->_askfor_get($this->_module_plugin_id, $af_id)) || empty($askfor['af_id']) ) {
			$this->message('error', '指定审批信息不存在 或 已被删除');
		}
		$askfor = $this->_askfor_format($askfor);

		//当前审批涉及到的所有用户id
		$uids = array();

		// 申请人
		if ( $askfor['m_uid'] ) {
			$uids[$askfor['m_uid']] = $askfor['m_uid'];
		}

		//该审批所有进程，原始数据
		$serv_proc = &service::factory('voa_s_oa_askfor_ormproc');
		$conds_proc['af_id'] = $af_id;
		$conds_proc['afp_condition IN (?)'] = array(1, 2, 3, 4, 6, 7);
		$proclist = $serv_proc->list_by_conds($conds_proc);
		$proclist = $this->_proc_format($proclist);


		$form_proclist = $this->_proc_format($proclist);

		//如果是固定流程
		if($askfor['aft_id'] != 0){
			//计算出当前最大审批级数
			$leav_list = array();
			if(!empty($proclist)){
				foreach($proclist as $val){

					$leav_list[$val['afp_level']] = $val['afp_level'];
				}
				$max_leav = max($leav_list);
			}
			//格式每个用户状态
			foreach($proclist as &$va){
				$va['condition'] = $this->_askfor_proc_status_descriptions[$va['afp_condition']];
			}
			$current_le = 1;
			//计算当前到达级数
			foreach($proclist as $current){
				if($current['is_active'] == 1){
					$current_le = $current['afp_level'];
				}
			}
			//进行状态显示过滤
			foreach($proclist as &$va_cond){
				if($va_cond['afp_level'] > $current_le){
					unset($va_cond['afp_condition']);
					unset($va_cond['condition']);
				}
			}
		}


		//获取抄送人
		$conds_cs['afp_condition'] = 5;
		$conds_cs['af_id'] = $af_id;
		$cs_list = $serv_proc->list_by_conds($conds_cs);

		$cs_uids = array();
		if(!empty($cs_list)){
			foreach($cs_list as $v_cs){
				$cs_uids[$v_cs['m_uid']]['m_uid'] = $v_cs['m_uid'];
				$cs_uids[$v_cs['m_uid']]['m_username'] = $v_cs['m_username'];
			}
		}

		//提取所有进程内的用户uid
//		foreach ( $procList AS $_afp_id => $_afp ) {
//			if ( !isset($uids[$_afp['m_uid']]) ) {
//				$uids[$_afp['m_uid']] = $_afp['m_uid'];
//			}
//		}
//		unset($_afp, $_afp_id);


		// 读取审批所有相关文件 by Deepseath@20141226#332
		$serv_att = &service::factory('voa_s_oa_askfor_ormattachment');
		$conds_att['af_id'] = $af_id;
		$att_list = $serv_att->list_by_conds($conds_att);
		if(!empty($att_list)){
			foreach($att_list as &$_att){
				$_att['imgurl'] = voa_h_attach::attachment_url($_att['at_id']);
			}
		}
		//进程数量
		$proc_count = count($form_proclist);*/
/*		$attachs = array();
		$serv_aoat = &service::factory('voa_s_oa_askfor_attachment', array('pluginid' => $this->_module_plugin_id));
		var_dump($serv_aoat);die;
		$attach_list = $serv_aoat->fetch_all_by_af_id($af_id);
		var_dump($attach_list);die;
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
		}*/

//		$this->view->set('af_id', $af_id);
//		$this->view->set('askfor', $askfor);
//		$this->view->set('ccMemberList', $ccMemberList);
//		$this->view->set('procList', $procList);
//		$this->view->set('commentList', $commentList);
//		$this->view->set('procMemberList', $procMemberList);
//		$this->view->set('countProc', $countProc);
//		$this->view->set('countComment', $countComment);
//		$this->view->set('countReply', $countReply);
/*
		$this->view->set('proc_count', $proc_count);
		$this->view->set('att_list', $att_list);
		$this->view->set('leav_list', $leav_list);
		$this->view->set('proclist', $proclist);
		$this->view->set('form_proclist', $form_proclist);
		$this->view->set('cs_uids', $cs_uids);*/
		$this->view->set('af_id', $af_id);
		$this->output('office/askfor/new_view');

	}

}
