<?php
/**
 * voa_c_api_dailyreport_get_view
 * 查看日报
 * $Author$
 * $Id$
 */
class voa_c_api_dailyreport_get_view extends voa_c_api_dailyreport_base {
	//接收人
	const STATUS_NORMAL = 1;
	//已更新
	const STATUS_UPDATE = 2;
	//抄送人
	const STATUS_CARBON_COPY = 3;
	//已删除
	const STATUS_REMOVE = 4;

	public function execute() {
		//请求参数
		$fields = array(
			//日报ID
			'dr_id' => array('type' => 'int', 'required' => true),
		);
		if (!$this->_check_params($fields)) {
			return false;
		}

		$dr_id = $this->_params['dr_id'];
		//读取报告信息
		$uda = &uda::factory('voa_uda_frontend_dailyreport_format');
		$serv = &service::factory('voa_s_oa_dailyreport', array('pluginid' => startup_env::get('pluginid')));
		$dailyreport = $serv->fetch_by_id($dr_id);

        //读取日报的附件数据
        $dr_attachment = &service::factory('voa_d_oa_dailyreport_attachment',array('pluginid' => startup_env::get('pluginid')));
        $dr_attr = $dr_attachment->fetch_by_conditions(array(
            'dr_id' => $dr_id
        ));

        $at_ids = array();
        foreach($dr_attr as $k=>$v){
            $at_ids[] = $v['at_id'];
        }

        // 更新日报阅读状态为已读
        $read = &service::factory('voa_s_oa_dailyreport_read');
        $read->update_by_conds(array(
            'dr_id' => $dr_id
        ), array(
            'is_read' => 2
        ));

        $common_attatachment = &service::factory('voa_s_oa_common_attachment');
        $attr_list = $common_attatachment->fetch_by_ids($at_ids);
        $attr_data = array();
        foreach($attr_list as $key=>$row){
            $item['is_image'] =$row['at_isimage'];
            $item['at_url'] = voa_h_attach::attachment_url($row['at_id'], 0);
	        $item['is_attach'] = $row['at_isattach'];
            $item['thumb'] = intval($row['at_isimage']) ?  voa_h_attach::attachment_url($row['at_id'], 45) : '';
            $item['filename'] = $row['at_filename'];
            $item['filesize']  = $row['at_filesize'];
            $attr_data[] = $item;
        }

		//为空判断
		if (empty($dailyreport)) {
			return $this->_set_errcode(voa_errcode_api_dailreport::VIEW_NOT_EXISTS, $dr_id);
		}

		//读取报名目标人和抄送人
		$serv_m = &service::factory('voa_s_oa_dailyreport_mem', array('pluginid' => startup_env::get('pluginid')));
		$mems = $serv_m->fetch_by_dr_id($dr_id);

		//对比 uid 查看的日报uid
		if (startup_env::get('wbs_uid') != $dailyreport['m_uid'] ) {
				$is_recv = true;
		}
		//取出 uid
		$uids = array();

		//判断用户权限
		$is_permit = false;

		//抄送人信息
		$ccusers = array();

		//报告目标人
		$tousers = array();
		foreach ($mems as $v) {
			$uids[$v['m_uid']] = $v['m_uid'];
			if (startup_env::get('wbs_uid') == $v['m_uid'] || 0 == $v['m_uid']) {
				$is_permit = true;
			}

			if (self::STATUS_CARBON_COPY == $v['drm_status']) {
			    //抄送人
				if ($v['m_uid'] == $dailyreport['m_uid']) {
					continue;
				}
				$ccusers[] = $v;
			} else {
				//目标人
				$tousers[] = $v;
			}
		}

		//判断当前用户是否有权限查看
		if (!$is_permit && startup_env::get('wbs_uid') != $dailyreport['m_uid']) {
			return $this->_set_errcode(voa_errcode_api_dailreport::VIEW_NO);
		}

		//读取报告详情
		$serv_p = &service::factory('voa_s_oa_dailyreport_post', array('pluginid' => startup_env::get('pluginid')));
		$posts = $serv_p->fetch_by_conditions(array(
				'dr_id' => $dr_id,
				'drp_first' => voa_d_oa_dailyreport_post::FIRST_YES
		));

		foreach ($posts as $k => &$v){
			$dailyreport['message'] = $v['drp_message'];
		}
		unset($v);

		$servm = &service::factory('voa_s_oa_member', array('pluginid' => 0));
		//用户头像信息
		$users = $servm->fetch_all_by_ids(array_keys($mems));
		voa_h_user::push($users);

		//处理接收人数组
		$tousers_josn = array();
		foreach ($tousers as $key => $value) {
			$touser['uid'] = $value['m_uid'];
            $touser['username'] = $value['m_username'];
            $touser['avatar'] = voa_h_user::avatar($value['m_uid'],isset($users[$value['m_uid']]) ? $users[$value['m_uid']] : array());
            $tousers_josn[] = $touser;
		}

		//处理抄送人数组
		$ccusers_josn = array();
		foreach ($ccusers as $key => $value) {
			$ccusers_josn[$value['m_uid']]['uid'] = $value['m_uid'];
			$ccusers_josn[$value['m_uid']]['username'] = $value['m_username'];
			$ccusers_josn[$value['m_uid']]['avatar'] = voa_h_user::avatar($value['m_uid'],
					isset($users[$value['m_uid']]) ? $users[$value['m_uid']] : array());
		}

		//重组返回json数组
		$this->_result  = array(
			'dr_id' => $dr_id,
            'dr_subject' => $dailyreport['dr_subject'],
			'uid' => $dailyreport['m_uid'],//创建者uid
			'username' => rhtmlspecialchars($dailyreport['m_username']),//创建者名字
			'avatar' => voa_h_user::avatar($dailyreport['m_uid'],
					 isset($users[$dailyreport['m_uid']]) ? $users[$dailyreport['m_uid']] : array()),
			'reporttime' => $dailyreport['dr_reporttime'],//日报时间
			'createdtime' => $dailyreport['dr_created'],//创建时间
			'message' => $dailyreport['message'],//日报内容
			'ccusers' => $ccusers_josn ? array_values($ccusers_josn) : array(),
			'touser' => $tousers_josn ? $tousers_josn : array(),
            'attachment' => $attr_data
		);
		return true;
	}

}
