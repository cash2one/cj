<?php

/**
 * 审批操作相关
 * $Author$
 * $Id$
 */
class voa_c_frontend_askfor_verify extends voa_c_frontend_askfor_base {

    /** 审批信息 */
    protected $_askfor = array();

    /** 当前进度 */
    protected $_proc = array();

    /** 抄送人 */
    protected $_copy_users = array();

    protected function _chk_permit() {
	/** 判断当前审批是否存在 */
	$af_id = rintval($this->request->get('af_id'));
	$serv_af = &service::factory('voa_s_oa_askfor', array('pluginid' => startup_env::get('pluginid')));
	$this->_askfor = $serv_af->fetch_by_id($af_id);
	if (empty($this->_askfor)) {
	    $this->_error_message('askfor_not_exist', get_referer());
	}

	/** 如果申请人撤销，则不能同意和驳回 */
	if ($this->_askfor['af_status'] == voa_d_oa_askfor::STATUS_CANCEL) {
	    $this->_send_error('审申请人已撤销审批，不能进行相应操作', 2);
	}

	/** 读取当前进度信息 */
	$serv_p = &service::factory('voa_s_oa_askfor_proc', array('pluginid' => startup_env::get('pluginid')));
	$this->_proc = $serv_p->fetch_by_id($this->_askfor['afp_id']);

	if (empty($this->_proc)) {
	    $this->_error_message('askfor_proc_error', get_referer());
	}
	/** 读取抄送人信息 */
	$this->__list_copy_users($af_id);

	/** 判断当前用户是否有审核权限 */
	if (!empty($this->_proc['m_uid'])) {
	    if ($this->_proc['m_uid'] != startup_env::get('wbs_uid') || voa_d_oa_askfor_proc::STATUS_NORMAL != $this->_proc['afp_status']) {
		$this->_error_message('askfor_forbidden', get_referer());
	    }
	} else {
	    $serv_d = &service::factory('voa_s_oa_member_department', array('pluginid' => startup_env::get('pluginid')));
	    $wbs_uid = $serv_d->fetch_by_conditions(array('m_uid'=>startup_env::get('wbs_uid')));
	    if ($this->_proc['mp_id'] != $wbs_uid['mp_id'] || voa_d_oa_askfor_proc::STATUS_NORMAL != $this->_proc['afp_status']) {
		$this->_error_message('askfor_forbidden', get_referer());
	    }
	}

	return true;
    }

    /**
     * 用于催办、撤销检查权限
     * @return boolean
     */
    protected function _chk_permit_for_myself($is_cancel) {

	/** 判断当前审批是否存在 */
	$af_id = rintval($this->request->get('af_id'));
	$serv_af = &service::factory('voa_s_oa_askfor', array('pluginid' => startup_env::get('pluginid')));
	$this->_askfor = $serv_af->fetch_by_id($af_id);

	if (empty($this->_askfor)) {
	    $this->_error_message('askfor_not_exist', get_referer());
	}
	/** 如果已审批，则不能撤销和催办 */
	if ($this->_askfor['af_status'] == voa_d_oa_askfor::STATUS_APPROVE || ($this->_askfor['af_status'] == voa_d_oa_askfor::STATUS_APPROVE_APPLY && $is_cancel) || $this->_askfor['af_status'] == voa_d_oa_askfor::STATUS_REFUSE) {
	    $this->_send_error('审批人已审批，不能进行相应操作');
	}
	/** 读取当前进度信息 */
	$serv_p = &service::factory('voa_s_oa_askfor_proc', array('pluginid' => startup_env::get('pluginid')));
	$this->_proc = $serv_p->fetch_by_id($this->_askfor['afp_id']);
	if (empty($this->_proc)) {
	    $this->_error_message('askfor_proc_error', get_referer());
	}
	/** 读取抄送人信息 */
	$this->__list_copy_users($af_id);
	/** 判断当前用户是否有审核权限 */
	if ($this->_askfor['m_uid'] != startup_env::get('wbs_uid')) {
	    $this->_error_message('askfor_forbidden', get_referer());
	}

	return true;
    }

    /**
     * 更新审核状态
     * @param int $af_id 当前申请id
     * @param int $data 待更新的审核申请信息
     * @param int $proc_id 进度id
     * @param int $proc_status 待更新的审核进度信息
     */
    protected function _update_status($af_id, $data, $proc_id, $proc_data) {
	/** 更新审批状态 */
	$serv_af = &service::factory('voa_s_oa_askfor', array('pluginid' => startup_env::get('pluginid')));
	$serv_af->update($data, array('af_id' => $af_id));

	/** 更新审批进度状态 */
	$serv_p = &service::factory('voa_s_oa_askfor_proc', array('pluginid' => startup_env::get('pluginid')));
	$serv_p->update($proc_data, array('afp_id' => $proc_id));
    }

    /**
     * 取得抄送人列表
     * @param unknown $af_id
     */
    private function __list_copy_users($af_id) {
	$serv_p = &service::factory('voa_s_oa_askfor_proc', array('pluginid' => startup_env::get('pluginid')));
	$copy_users = $serv_p->fetch_by_conditions(array('af_id' => $af_id, 'afp_status' => voa_d_oa_askfor_proc::STATUS_CARBON_COPY));
	$users = null;
	if (!empty($copy_users)) {
	    $copy_uids = array_column($copy_users, 'm_uid');
	    $serv_m = &service::factory('voa_s_oa_member', array('pluginid' => startup_env::get('pluginid')));
	    $users = $serv_m->fetch_all_by_ids($copy_uids);
	}

	$this->_copy_users = $users;
    }

    /**
     * 发送JSON数据
     * @param string $message
     */
    protected function _send_json($message) {
	echo rjson_encode(
		array(
		    'errcode' => 0,
		    'errmsg' => 'success',
		    'timestamp' => startup_env::get('timestamp'),
		    'result' => array(
			'url' => "/askfor/view/" . $this->_askfor['af_id'],
			'message' => $message
		    )
		)
	);
	exit();
    }

    /**
     * 发送出错数据
     * @param string $message
     */
    protected function _send_error($errmsg, $errcode = 1) {
	echo rjson_encode(
		array(
		    'errcode' => $errcode,
		    'errmsg' => $errmsg,
		    'timestamp' => startup_env::get('timestamp'),
		    'result' => array()
		)
	);
	exit();
    }

}
