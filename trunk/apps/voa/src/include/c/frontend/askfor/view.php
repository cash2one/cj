<?php

/**
 * 查看审批申请
 * $Author$
 * $Id$
 */
class voa_c_frontend_askfor_view extends voa_c_frontend_askfor_base {

    public function execute() {
        /** 审批ID */
        $af_id = rintval($this->request->get('af_id'));

        /** 获取当前审批信息 */
        $servaf = &service::factory('voa_s_oa_askfor', array('pluginid' => startup_env::get('pluginid')));
        $askfor = $servaf->fetch_by_id($af_id);

        $askfor['af_subject'] = rsubstr(rhtmlspecialchars($askfor['af_subject']), 28);
        $askfor['af_message'] = rhtmlspecialchars($askfor['af_message']);
        $askfor['af_message'] = bbcode::instance()->bbcode2html($askfor['af_message']);
        $askfor['created'] = rgmdate($askfor['af_created'], 'Y-m-d H:i');
        if (empty($af_id) || empty($askfor)) {
            $this->_error_message('askfor_not_exist', get_referer());
        }

        /** 获取审批流程自定义字段 */
        $servafcd = &service::factory('voa_s_oa_askfor_customdata', array('pluginid' => startup_env::get('pluginid')));
        $colsdata = $servafcd->fetch_by_af_id($af_id);

        /** 获取审批进度信息列表 */
        $servp = &service::factory('voa_s_oa_askfor_proc', array('pluginid' => startup_env::get('pluginid')));
        $procs = $servp->fetch_by_af_id($af_id);
        
        $servm = &service::factory('voa_s_oa_member', array('pluginid' => startup_env::get('pluginid')));
        $serv_d = &service::factory('voa_s_oa_member_department', array('pluginid' => startup_env::get('pluginid')));
        $wbs_uid =  $wbs_uid = $serv_d->fetch_by_conditions(array('m_uid'=>startup_env::get('wbs_uid')));
        //$wbs_uid = $servm->fetch(startup_env::get('wbs_uid'));
        //print_r($procs);
        /** 如果不是自己发起的申请, 则判断是否有权限查看 */
        if ($askfor['m_uid'] != startup_env::get('wbs_uid')) {
            $view = false;
            /** 如果有权限, 即是审批人或抄送人 */
            foreach ($procs as $v) {
                if (!empty($v['mp_id'])) {
                    if ($v['mp_id'] == $wbs_uid['mp_id']) {
                        $view = true;
                        break;
                    }
                } else {
                    if ($v['m_uid'] == startup_env::get('wbs_uid')) {
                        $view = true;
                        break;
                    }
                }
            }
            
            
            /** 如果不让查看, 则 */
            if (!$view) {
                $this->_error_message('没有权限！请联系管理员', get_referer());
            }
        }

        /** 取出审批人的用户信息, 取出抄送信息 */
        $uids = array();
        $carbon_un = array();
        /** 抄送人记录 */
        $carbon_copies = array();
        /** 当前进度记录 */
        $cur_proc = array();
        foreach ($procs as $k => &$v) {
            $v['afp_note'] = rhtmlspecialchars($v['afp_note']);
            $uids[$v['m_uid']] = $v['m_uid'];

            $v['_updated'] = rgmdate($v['afp_updated'], 'Y-m-d H:i');
            $v['_status'] = $this->_askfor_status_descriptions[$v['afp_status']];
            $v['_class'] = 'ui-badge-muted';
            if ($v['afp_status'] == voa_d_oa_askfor::STATUS_REMINDER) {
                //如果是自己查看，则显示催办信息，否则不显示
                if ($v['m_uid'] == startup_env::get('wbs_uid')) {
                    $v['_class'] = 'ui-badge';
                } else {
                    unset($procs[$k]);
                }
            }
            //删除草稿进度
            if ($v['afp_status'] == voa_d_oa_askfor::STATUS_DRAFT) {
                unset($procs[$k]);
            }
            //取出抄送人
            if ($v['afp_status'] == voa_d_oa_askfor_proc::STATUS_CARBON_COPY) {
                $carbon_copies[] = $v;
            }

            /** 获取当前进度记录 */
            if ($v['afp_id'] == $askfor['afp_id']) {
                $cur_proc = $v;
                $v['_class'] = 'ui-badge';
            }
        }

        unset($v);

        /** 读取 */
        $servm = &service::factory('voa_s_oa_member', array('pluginid' => 0));
        $users = $servm->fetch_all_by_ids($uids);
        foreach ($users as $u) {
            voa_h_user::push($u);
        }

        // 读取审批所有相关文件
        $attachs = array();
        $serv_afat = &service::factory('voa_s_oa_askfor_attachment', array('pluginid' => startup_env::get('pluginid')));
        $attach_list = $serv_afat->fetch_all_by_af_id($af_id);
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
                    'at_id' => $v['at_id'], // 公共文件附件ID
                    //'id' => $v['afat_id'], // 审批文件ID
                    //'filename' => $at['at_filename'],// 附件名称
                    //'filesize' => $at['at_filesize'],// 附件容量
                    //'mediatype' => $at['at_mediatype'],// 媒体文件类型
                    //'description' => $at['at_description'],// 附件描述
                    //'isimage' => $at['at_isimage'] ? 1 : 0,// 是否是图片
                    'url' => voa_h_attach::attachment_url($v['at_id'], 0), // 附件文件url
                        //'thumb' => $at['at_isimage'] ? voa_h_attach::attachment_url($v['at_id'], 45) : '',// 缩略图URL
                );
            }
        }
        //print_r($cur_proc);
        if (!empty($cur_proc['mp_id'])) {
            //$cur_proc['mp_uid'] = $servm->fetch_by_cj_id($cur_proc['mp_id']);
            $cur_proc['mp_uid'] = $serv_d->fetch_all_by_conditions(array('mp_id'=>$cur_proc['mp_id']));
        }
        $this->view->set('af_id', $af_id);
        $this->view->set('askfor', $askfor);
        $this->view->set('colsdata', $colsdata);
        $this->view->set('procs', $procs);
        $this->view->set('cur_proc', $cur_proc);
        $this->view->set('carbon_copies', empty($carbon_copies) ? '' : implode(',', array_column($carbon_copies, 'm_username')));
        $this->view->set('navtitle', '审批详情');
        // 审批图片
        $this->view->set('attachs', array_key_exists(0, $attachs) ? $attachs[0] : array());

        $this->_output('mobile/askfor/view');
    }

}
