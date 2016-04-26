<?php
/**
 * 通过审批
 * $Author$
 * $Id$
 */

class voa_c_frontend_askfor_verify_approve extends voa_c_frontend_askfor_verify {

    protected $msg_title;
    protected $template;

    public function execute() {
        $transmit = false;
        $status = 1;    // 1表示发给单个审批人;2表示发给个某个职务下所有的人员 

        /** 权限检查 */
        $this->_chk_permit();

        $serv = &service::factory('voa_s_oa_askfor', array('pluginid' => startup_env::get('pluginid')));
        $serp = &service::factory('voa_s_oa_askfor_proc', array('pluginid' => startup_env::get('pluginid')));
        $sert = &service::factory('voa_s_oa_askfor_template', array('pluginid' => startup_env::get('pluginid')));
        $serv_m = &service::factory('voa_s_oa_member');
        try {
            $serv->begin();
            //取得审批人列表
            $template = $sert->fetch_by_id($this->_askfor['aft_id']);
            
            /* 如果选择的是审批人 */
            if (!empty($template['approvers'])) {
                $approvers = unserialize($template['approvers']);
                $keys = array_column($approvers, 'm_uid');
                $key = array_keys($keys, $this->_proc['m_uid']);

                /* 如果还有下一个审批人，则同意并转审批 */
                if (isset($keys[$key[0] + 1])) {
                    $approver = $approvers[$key[0] + 1];
                    $afp_id = $serp->insert(array(
                        'af_id' => $this->_askfor['af_id'],
                        'm_uid' => $approver['m_uid'],
                        'm_username' => $approver['m_username'],
                        'afp_status' => voa_d_oa_askfor_proc::STATUS_NORMAL
                            ), true);
                    /** 更新审批以及审批进度状态 */
                    $this->_update_status($this->_askfor['af_id'], array(
                        'afp_id' => $afp_id,
                        'af_status' => voa_d_oa_askfor::STATUS_APPROVE_APPLY
                            ), $this->_proc['afp_id'], array(
                        'afp_status' => voa_d_oa_askfor_proc::STATUS_APPROVE_APPLY,
                        'afp_note' => $this->request->get('message')
                    ));
                    $status = 1;
                    $transmit = true;
                } else {
                    //如果没有下一个审批人，则同意审批
                    $this->_update_status($this->_askfor['af_id'], array(
                        'af_status' => voa_d_oa_askfor::STATUS_APPROVE
                            ), $this->_proc['afp_id'], array(
                        'afp_status' => voa_d_oa_askfor_proc::STATUS_APPROVE,
                        'afp_note' => $this->request->get('message')
                    ));
                }
            }
            /* 如果选择的是职务 */
            if (!empty($template['positions'])) {
                $positions = unserialize($template['positions']);
                $keys = array_column($positions, 'mp_id');
                $key = array_keys($keys, $this->_proc['mp_id']);
                if (isset($keys[$key[0] + 1])) {
                    $position = $positions[$key[0] + 1];
                    $afp_id = $serp->insert(array(
                        'af_id' => $this->_askfor['af_id'],
                        'mp_id' => $position['mp_id'],
                        'mp_name' => $position['mp_name'],
                        'afp_status' => voa_d_oa_askfor_proc::STATUS_NORMAL
                            ), true);

                    /** 更新审批以及审批进度状态 */
                    $this->_update_status($this->_askfor['af_id'], array(
                        'afp_id' => $afp_id,
                        'af_status' => voa_d_oa_askfor::STATUS_APPROVE_APPLY
                            ), $this->_proc['afp_id'], array(
                        'afp_status' => voa_d_oa_askfor_proc::STATUS_APPROVE_APPLY,
                        'afp_note' => $this->request->get('message')
                    ));
                    $status = 2;
                    $transmit = true;
                } else {
                    /* 如果没有下一个审批人，则同意审批 */
                    $this->_update_status($this->_askfor['af_id'], array(
                        'af_status' => voa_d_oa_askfor::STATUS_APPROVE
                            ), $this->_proc['afp_id'], array(
                        'afp_status' => voa_d_oa_askfor_proc::STATUS_APPROVE,
                        'afp_note' => $this->request->get('message')
                    ));
                }
            }
            if (empty($template)) {
                $this->_update_status($this->_askfor['af_id'], array(
                    'af_status' => voa_d_oa_askfor::STATUS_APPROVE
                            ), $this->_proc['afp_id'], array(
                    'afp_status' => voa_d_oa_askfor_proc::STATUS_APPROVE,
                    'afp_note' => $this->request->get('message')
                ));
            }
            $serv->commit();
        } catch (Exception $e) {
            $serv->rollback();
            /** 入库操作失败 */
            $this->_error_message('操作失败', get_referer());
        }

        /** 发送消息提醒 */
        $viewurl = '';
        $this->get_view_url($viewurl, $this->_askfor['af_id']);
        $mq_ids = array();
        if ($transmit) {//转审批
            /* 发给审批人 */
            if ($status == 1) {
                $mem = $serv_m->fetch_by_uid($this->_askfor['m_uid']);
                $this->msg_title = $template['name'] . "审批被转审";
                $msg_desc = '审批主题：' . $this->_askfor['af_subject'];
                $msg_desc .= "\n接收人：" . $approver['m_username'];
                $msg_url = $viewurl;
                $touser = $mem['m_openid'];
                voa_h_qymsg::push_news_send_queue($this->session, $this->msg_title, $msg_desc, $msg_url, $touser);
                //发给下一个审批人
                $mem1 = $serv_m->fetch_by_uid($approver['m_uid']);

                //待处理审批
                $this->msg_title = $this->_askfor['m_username'] . $template['name'] . "待处理审批";

                $msg_desc1 = '审批主题：' . $this->_askfor['af_subject'];
                $msg_desc1 .= "\n申请人：" . $this->_askfor['m_username'];
                $msg_url1 = $viewurl;
                $touser1 = $mem1['m_openid'];
                voa_h_qymsg::push_news_send_queue($this->session, $this->msg_title, $msg_desc1, $msg_url1, $touser1);
            }

            if ($status == 2) {
                $servm = &service::factory('voa_s_oa_member', array('pluginid' => 0));
                $serv_d = &service::factory('voa_s_oa_member_department', array('pluginid' => 0));
                $temp_arr = array('mp_id'=>$position['mp_id']);
                $mem_list = $serv_d->fetch_all_by_conditions($temp_arr);
                foreach ($mem_list as $v) {
                    $this->msg_title = $this->_askfor['m_username'] . $template['name'] . "待处理审批";
                    $msg_desc1 = '审批主题：' . $this->_askfor['af_subject'];
                    $msg_desc1 .= "\n申请人：" . $this->_askfor['m_username'];
                    $msg_url1 = $viewurl;
                    $mem1 = $servm->fetch_by_uid($v['m_uid']);
                    $touser1 = $mem1['m_openid'];
                    voa_h_qymsg::push_news_send_queue($this->session, $this->msg_title, $msg_desc1, $msg_url1, $touser1);
                }
            }
    
            /** 给抄送人发送消息 */
            if ($this->_copy_users) {
                //待处理审批
                $this->msg_title = "抄送" . $this->_askfor['m_username'] . $template['name'] . "待处理审批";
                $msg_desc = '审批主题：' . $this->_askfor['af_subject'];
                $msg_desc .= "\n申请人：" . $this->_askfor['m_username'];
                $msg_url = $viewurl;
                $touser = implode('|', array_column($this->_copy_users, 'm_openid'));
                voa_h_qymsg::push_news_send_queue($this->session, $this->msg_title, $msg_desc, $msg_url, $touser);
            }
        } else {//审批
            /** 给申请人发送消息 */
            $serv_m = &service::factory('voa_s_oa_member');
            $mem = $serv_m->fetch_by_uid($this->_askfor['m_uid']);
            //审批已通过
            $this->msg_title = $this->_askfor['af_subject'] . "审批已通过";
            $msg_desc = '审批主题：' . $this->_askfor['af_subject'];
            $msg_desc .= "\n审批人：" . $this->_proc['m_username'];
            $msg_url = $viewurl;
            $touser = $mem['m_openid'];
            voa_h_qymsg::push_news_send_queue($this->session, $this->msg_title, $msg_desc, $msg_url, $touser);
            /** 给抄送人发送消息 */
            if ($this->_copy_users) {
                //审批已通过
                $this->msg_title = "抄送" . $this->_askfor['m_username'] . $this->_askfor['af_subject'] . "审批已通过";
                $msg_desc = '审批主题：' . $this->_askfor['af_subject'];
                $msg_desc .= "\n申请人：" . $this->_askfor['m_username'];
                $msg_url = $viewurl;
                $touser = implode('|', array_column($this->_copy_users, 'm_openid'));
                voa_h_qymsg::push_news_send_queue($this->session, $this->msg_title, $msg_desc, $msg_url, $touser);
            }
        }

        echo rjson_encode(
                array(
                    'errcode' => 0,
                    'errmsg' => 'success',
                    'timestamp' => startup_env::get('timestamp'),
                    'result' => array(
                        'url' => "/askfor/view/" . $this->_askfor['af_id'],
                        'message' => '审批操作成功'
                    )
                )
        );
        exit();
    }

}
