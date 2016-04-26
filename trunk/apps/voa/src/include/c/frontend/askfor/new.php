<?php

/**
 * 新的审批申请
 * $Author$
 * $Id$
 */
class voa_c_frontend_askfor_new extends voa_c_frontend_askfor_base {

    protected $_subject;
    protected $_message;
    protected $msg_title;
    protected $afat_status_array = array(
	'审批申请中',
	'审批通过',
	'转审批',
	'审批不通过',
	'草稿',
	'已催办',
	'已撤销',
	'已删除'
    );

    public function execute() {
	/** 审批流程ID */
	$aft_id = rintval($this->request->get('aft_id'));

	// 日期列表
	$today = rstrtotime('today');
	for ($i = 0; $i < 15; $i ++) {
	    $d = rgmdate($today + 86400 * $i, 'Y-m-d');
	    $dates[$d] = $d;
	}
	$this->view->set('dates', $dates);

	// 时间列表(1小时为一段)
	for ($i = 0; $i < 48; $i ++) {
	    $k = rgmdate($today + 3600 * $i, 'H') * 60 + rgmdate($today + 3600 * $i, 'i');
	    $t = rgmdate($today + 3600 * $i, 'H:i');
	    $times[$t] = rgmdate($today + 3600 * $i, 'H:i');
	}

	$datetime = startup_env::get('timestamp') + 86400;
	$this->view->set('times', $times);
	$this->view->set('datetime', $datetime);

	/** 取得审批流程 */
	$template = array();
	if ($aft_id > 0) {
	    $uda = &uda::factory('voa_uda_frontend_askfor_template_get');

	    $uda->template_get($template);
	    $username = startup_env::get('wbs_username');

	    if (!empty($template['approvers'])) {
		$approvers = unserialize($template['approvers']);
		$this->view->set('approvers', implode(' > ', array_column($approvers, 'm_username')));
		$template['tag'] = 1;
		$template['first_approver'] = array_shift($approvers);
	    } else {
		$positions = unserialize($template['positions']);
		$this->view->set('approvers', implode(' > ', array_column($positions, 'mp_name')));
		$template['position_arr'] = $positions;
		$template['tag'] = 2;
	    }
	    // 自定义subject
	    $subject = $username . ' ' . $template['name'] . '申请';
	    $template['subject'] = rsubstr($subject, 40);
	} else {
	    /** 如果是自由流程，允许上传图片 */
	    $template['upload_image'] = 1;
	    $template['aft_id'] = 0;
	}

	if ($this->_is_post()) {
	    /** 标题 */
	    $this->_subject = trim($this->request->get('af_subject'));

	    if (empty($this->_subject)) {
		$this->_error_message('subject_short', get_referer());
	    }

	    /** 审批内容 */
	    $this->_message = trim($this->request->get('af_message'));
	    if (empty($this->_message)) {
		$this->_error_message('message_short', get_referer());
	    }

	    /** 如果是自由流程，则审批人从页面取得 */
	    if ($aft_id == 0) {
		$m_uid = $this->request->get('uids');
		$servm = &service::factory('voa_s_oa_member', array('pluginid' => 0));
		$user = $servm->fetch_by_uid($m_uid);
		$template['first_approver'] = array(
		    'm_uid' => $m_uid,
		    'm_username' => $user['m_username']
		);
		$template['tag'] = 1;
		$template['name'] = "自由流程";
	    }
	    /** 抄送人 */
	    $template['copy_uids'] = $this->request->get('copy_uids');

	    /** 调用处理函数 */
	    $this->_add($template);
		 
	    return false;
	}
  
	/* 根据审批流程模版获取流程id */
	$cols_array = array();
	if (isset($template['cols']) && !empty($template['cols'])) {
	    foreach ($template['cols'] as $key => $val) {
		if ($val['type'] > 2) {
		    $cols_array[$key] = 'cols' . $val['afcc_id'];
		}
	    }
	}

	/* navtitle设置 */
	$this->view->set('navtitle', '自由审批');
	if (isset($template['name']) && !empty($template['name'])) {
	    $this->view->set('navtitle', $template['name']);
	}

	$this->view->set('cols_arr', $cols_array);
	$this->view->set('action', $this->action_name);
	$this->view->set('askfor', array());
	$this->view->set('aft_id', $aft_id);
	$this->view->set('template', $template);

	$this->view->set('p_sets', $this->_p_sets);

	// 赋值jsapi接口需要的ticket
	$this->_get_jsapi("['chooseImage', 'previewImage', 'uploadImage']");

	$this->_output('mobile/askfor/new');
    }

    protected function _add($template) {

	// 上传的附件id
	$upload_attach_ids = (string) $this->request->post('at_ids');
	$upload_attach_ids = trim($upload_attach_ids);
	// 检查附件id
	$attach_ids = array();
	// 判断是否上传了附件 且 系统是否允许上传图片
	if (!empty($upload_attach_ids) && $template['upload_image'] == 1) {

	    // 整理附件id
	    foreach (explode(',', $upload_attach_ids) as $_id) {
		if (!is_numeric($_id)) {
		    continue;
		}
		$_id = (int) $_id;
		if ($_id > 0 && !isset($attach_ids[$_id])) {
		    $attach_ids[$_id] = $_id;
		}
	    }
	}

	// 获取附件信息
	$attachs = array();
	if (!empty($attach_ids)) {
	    $serv_at = &service::factory('voa_s_oa_common_attachment', array('pluginid' => 0));
	    $attachs = array();
	    $attachs = $serv_at->fetch_by_conditions(array(
		'at_id' => array($attach_ids, '='),
		'm_uid' => startup_env::get('wbs_uid')
	    ));
	}

	$serv_afat = &service::factory('voa_s_oa_askfor_attachment', array('pluginid' => startup_env::get('pluginid')));
	$serv_m = &service::factory('voa_s_oa_member', array('pluginid' => startup_env::get('pluginid')));
	$serv_d = &service::factory('voa_s_oa_member_department', array('pluginid' => startup_env::get('pluginid')));
	/** 数据入库 */
	try {
	    $serv_afat->begin();
	    /** 申请信息入库 */
	    $servaf = &service::factory('voa_s_oa_askfor', array('pluginid' => startup_env::get('pluginid')));
	    $askfor = array(
		'm_uid' => startup_env::get('wbs_uid'),
		'm_username' => startup_env::get('wbs_username'),
		'af_subject' => $this->_subject,
		'af_message' => $this->_message,
		'aft_id' => $template['aft_id'],
		'af_status' => voa_d_oa_askfor::STATUS_NORMAL
	    );
	    $af_id = $servaf->insert($askfor, true);
	    $askfor['af_id'] = $af_id;

	    if (empty($af_id)) {
		throw new Exception('新建审批错误');
	    }
	    $servp = &service::factory('voa_s_oa_askfor_proc', array('pluginid' => startup_env::get('pluginid')));
	    if (!empty($template['approvers']) || $template['aft_id'] == 0) {
		/** 审批人信息入库 */
		$first_approver = $template['first_approver'];
		if (empty($first_approver)) {
		    throw new Exception('流程错误，没有审批人');
		}
		$afp_id = $servp->insert(array(
		    'af_id' => $af_id,
		    'm_uid' => $first_approver['m_uid'],
		    'm_username' => $first_approver['m_username'],
		    'afp_status' => voa_d_oa_askfor_proc::STATUS_NORMAL
			), true);
	    }

	    /* 审批职务进度 */
	    if (!empty($template['position_arr'])) {
		$lists = $template['position_arr'];
		$first_positions = array_shift($lists);
		$afp_id = $servp->insert(array(
		    'af_id' => $af_id,
		    'mp_id' => $first_positions['mp_id'],
		    'mp_name' => $first_positions['mp_name'],
		    'afp_status' => voa_d_oa_askfor_proc::STATUS_NORMAL
			), true);
	    }
	    if (empty($afp_id)) {
		throw new Exception('新建审批错误');
	    }
	    /** 把进度 id 更新到审批表 */
	    $servaf->update(array(
		'afp_id' => $afp_id
		    ), array('af_id' => $af_id));

	    /** 抄送人人信息入库 */
	    if (!empty($template['copy_uids'])) {
		$copy_uids = explode(',', $template['copy_uids']);
		if (!empty($copy_uids)) {

		    $copy_users = $serv_m->fetch_all_by_ids($copy_uids);
		    foreach ($copy_users as $copy_user) {
			$copy_data[] = array(
			    'af_id' => $af_id,
			    'm_uid' => $copy_user['m_uid'],
			    'm_username' => $copy_user['m_username'],
			    'afp_status' => voa_d_oa_askfor_proc::STATUS_CARBON_COPY
			);
		    }
		    $servp->insert_multi($copy_data);
		}
	    }

	    /** 自定义字段入库 */
	    $cols = (array) $this->request->get('cols');
	    if (!empty($cols)) {
		$servcd = &service::factory('voa_s_oa_askfor_customdata', array('pluginid' => startup_env::get('pluginid')));
		$cols_data = array();
		foreach ($cols as $k => $col) {
		    foreach ($template['cols'] as $v) {
			if ($k == $v['afcc_id']) {
			    $cols_data[] = array(
				'af_id' => $af_id,
				'field' => $v['field'],
				'name' => $v['name'],
				'value' => $col,
			    );
			}
		    }
		}
		$servcd->insert_multi($cols_data);
	    }

	    // 附件入库
	    foreach ($attachs as $v) {
		$serv_afat->insert(array(
		    'af_id' => $af_id,
		    'afc_id' => 0, // 标记为任务的图片
		    'at_id' => $v['at_id'],
		    'm_uid' => startup_env::get('wbs_uid'),
		    'm_username' => startup_env::get('wbs_username'),
		    'afat_status' => voa_d_oa_askfor_attachment::STATUS_NORMAL
		));
	    }

	    $serv_afat->commit();
	} catch (Exception $e) {
	    $serv_afat->rollback();
	    /** 如果 $id 值为空, 则说明入库操作失败 */
	    $this->_error_message('新建审批错误', get_referer());
	}
	if ($template['tag'] == 1) {
	    $this->_to_queue($askfor, $first_approver['m_uid'], $template['name']);
	} else {
	    $temp_list = $template['position_arr'];
	    $first_positions = array_shift($temp_list);
	    $temp_arr = array('mp_id'=>$first_positions['mp_id']);
	    $mem_list = $serv_d->fetch_all_by_conditions($temp_arr);
	    //$mem_list = $serv_m->fetch_by_cj_id($first_positions['mp_id']);
	    foreach ($mem_list as $value) {
		$this->_to_queue($askfor, $value['m_uid'], $template['name']);
	    }
	}
	/** 发送企业消息 */
// 注释原因：只在审批人同意后才发送给抄送人信息
//		if (!empty($copy_users)) {
//			$this->_to_copy_user($askfor, $copy_users);
//		}

	echo rjson_encode(
		array(
		    'errcode' => 0,
		    'errmsg' => 'success',
		    'timestamp' => startup_env::get('timestamp'),
		    'result' => array(
			'url' => "/askfor/view/{$af_id}",
			'message' => '发布审批成功'
		    )
		)
	);
	exit();
    }

    /**
     * 发送消息给审批人
     * @param array $askfor
     * @param array $mem
     * @param array $cculist
     * @return boolean
     */
    protected function _to_queue($askfor, $uid, $template_type) {

	$servm = &service::factory('voa_s_oa_member', array('pluginid' => 0));
	$mem = $servm->fetch_by_uid($uid);
	/** 组织查看链接 */
	$viewurl = '';
	$this->get_view_url($viewurl, $askfor['af_id']);

	$this->msg_title = "(" . $askfor['m_username'] . ")" . $template_type;
	/** 记录状态, 1=审批申请中，2=审批通过, 3=转审批, 4=审批不通过, 5=草稿，6=已催办，7=已撤销，8=已删除 */
	$af_status = $askfor['af_status'];
	//判断消息状态
	$this->_af_status($af_status);
	$msg_desc = '审批主题：' . $askfor['af_subject'];
	$msg_desc .= "\n申请人：" . startup_env::get('wbs_username');
	$msg_url = $viewurl;
	$touser = $mem['m_openid'];

	// 发送消息
	voa_h_qymsg::push_news_send_queue($this->session, $this->msg_title, $msg_desc, $msg_url, $touser);

	return true;
    }

    /**
     * 发送消息给抄送人
     * @param array $askfor
     * @param array $mem
     * @param array $cculist
     * @return boolean
     */
    protected function _to_copy_user($askfor, $copy_users, $template_type) {
	/** 组织查看链接 */
	$viewurl = '';
	$this->get_view_url($viewurl, $askfor['af_id']);


	$this->msg_title = "抄送" . $askfor['m_username'] . "的" . $template_type;
	/** 记录状态, 1=审批申请中，2=审批通过, 3=转审批, 4=审批不通过, 5=草稿，6=已催办，7=已撤销，8=已删除 */
	$af_status = $askfor['af_status'];
	//判断消息状态
	$this->_af_status($af_status);
	$msg_desc = '审批主题：' . $askfor['af_subject'];
	$msg_desc .= "\n申请人：" . startup_env::get('wbs_username');
	$msg_url = $viewurl;
	$touser = implode('|', array_column($copy_users, 'm_openid'));
	// 发送消息
	voa_h_qymsg::push_news_send_queue($this->session, $this->msg_title, $msg_desc, $msg_url, $touser);

	return true;
    }

    /** 判断消息状态 */
    protected function _af_status($af_status) {
	switch ($af_status) {
	    case 1:
		$this->msg_title .= $this->afat_status_array[0];
		break;
	    case 2:
		$this->msg_title .= $this->afat_status_array[1];
		break;
	    case 3:
		$this->msg_title .= $this->afat_status_array[2];
		break;
	    case 4:
		$this->msg_title .= $this->afat_status_array[3];
		break;
	    case 5:
		$this->msg_title .= $this->afat_status_array[4];
		break;
	    case 6:
		$this->msg_title .= $this->afat_status_array[5];
		break;
	    case 7:
		$this->msg_title .= $this->afat_status_array[6];
		break;
	    case 8:
		$this->msg_title .= $this->afat_status_array[7];
		break;
	    default:
		break;
	}
    }

}

//end
