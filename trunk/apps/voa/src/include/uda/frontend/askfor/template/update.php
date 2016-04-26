<?php

/**
 * 审批流程编辑操作
 * $Author$
 * $Id$
 */
class voa_uda_frontend_askfor_template_update extends voa_uda_frontend_askfor_template_base {

    public function __construct() {
	parent::__construct();
    }

    /**
     * 编辑审批流程
     * @param array $askfor 审批主题信息
     * @param array $post 审批详情信息
     * @param array $mem 审批人信息
     * @param array $cculist 抄送人信息
     * @return boolean
     */
    public function template_update() {
	/** 模板ID  */
	$aft_id = (int) $this->_request->get('aft_id');
	if (!$this->val_aft_id($aft_id)) {
	    return false;
	}

	/** 模板主题 */
	$name = (string) $this->_request->get('name');
	if (!$this->val_name($name)) {
	    return false;
	}

	$approvers = array();
	$servm = &service::factory('voa_s_oa_member', array('pluginid' => startup_env::get('pluginid')));
	$servp = &service::factory('voa_s_oa_member_position', array('pluginid' => startup_env::get('pluginid')));
	$status = (int) $this->_request->get('contact_container');

	if ($status == 1) {
	    /** 当前审批人 */
	    $m_uids = (array) $this->_request->get('m_uid');
	    if (!empty($m_uids)) {
		foreach ($m_uids as $m_uid) {
		    if (!$this->val_uid($m_uid)) {
			return false;
		    }
		}
	    }

	    /** 审批审批人 */
	    $users = $servm->fetch_all_by_ids($m_uids);
	    if (!empty($m_uids)) {
		foreach ($m_uids as $m_uid) {
		    $approvers[] = array('m_uid' => $m_uid, 'm_username' => $users[$m_uid]['m_username']);
		}
	    }
	} else {
	    /** 职务选择判断 附加cj_id */
	    $positions = (array) $this->_request->get('position');
	    //if (!$this->__judegSortArray($positions)) {
		//return false;
	    //}
	    foreach ($positions as $value) {
		$list = $servp->get($value);
		$approvers[] = array('mp_id' => $value, 'mp_name' => $list['mp_name']);
	    }
	}

	$aft_id = (int) $this->_request->get('aft_id');
	$orderid = (int) $this->_request->get('orderid');
	$upload_image = (int) $this->_request->get('upload_image');
	$cols = (array) $this->_request->get('cols');

	/** 数据入库 */
	$servt = &service::factory('voa_s_oa_askfor_template', array('pluginid' => startup_env::get('pluginid')));
	$servc = &service::factory('voa_s_oa_askfor_customcols', array('pluginid' => startup_env::get('pluginid')));
	try {
	    $servm->begin();
	    /** 模板入库 */
	    $template = array(
		'name' => $name,
		'approvers' => $status == 1 ? serialize($approvers) : '',
		'positions' => $status == 2 ? serialize($approvers) : '',
		'orderid' => $orderid,
		'upload_image' => $upload_image,
		'aft_status' => voa_d_oa_askfor_template::STATUS_UPDATE
	    );

	    $servt->update($template, array('aft_id' => $aft_id));

	    /** 自定义字段入库 */
	    $servc->delete_by_aft_id($aft_id);
	    if (!empty($cols)) {
		foreach ($cols as $k => $col) {
		    $data[] = array(
			'aft_id' => $aft_id,
			'field' => $aft_id . '_field_' . $k,
			'name' => $col['name'],
			'type' => $col['type'],
			'required' => isset($col['required']) ? 1 : 0
		    );
		}

		$servc->insert_multi($data);
	    }


	    $servm->commit();
	} catch (Exception $e) {
	    $servm->rollback();
	    /** 如果 $id 值为空, 则说明入库操作失败 */
	    $this->errmsg(152, '新增流程失败');
	    return false;
	}

	return true;
    }

    /**
     * 判断数组是否有序 
     * @param array $arr
     * @return boolean
     */
    private function __judegSortArray($array) {
	if (is_array($array)) {
	    $count = count($array);
	    if ($count > 1) {
		if ($array [0] > $array [1]) {
		    $flag = 1;
		} else {
		    //$flag = 0;
		    return false;
		}
		$temp = $flag;
		for ($i = 1; $i < $count - 1; $i ++) {
		    if ($flag == 0) {
			if ($array [$i] < $array [$i + 1]) {
			    continue;
			} else {
			    $flag = 1;
			    break;
			}
		    }
		    if ($flag == 1) {
			if ($array[$i] > $array[$i + 1]) {
			    continue;
			} else {
			    $flag = 0;
			    break;
			}
		    }
		}
		if ($flag != $temp) {
		    return false;
		} else {
		    return true;
		}
	    }
	    return true;
	}

	return false;
    }

}
