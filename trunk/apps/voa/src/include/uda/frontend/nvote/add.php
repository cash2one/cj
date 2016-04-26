<?php
/**
 * voa_uda_frontend_nvote_add
 * 投票调研-uda添加投票
 * User: luckwang
 * Date: 15/3/9
 * Time: 上午11:17
 */

class voa_uda_frontend_nvote_add extends voa_uda_frontend_nvote_abstract {

    public function __construct() {
        parent::__construct();

    }

    /**
     * 添加投票
     * @param $vote  投票信息 array(
     *                          'subject' => '主题',
     *                          'endtime' => 结束时间2015-03-15 14:23,
     *                          'is_single' => 1,
     *                          'is_show_name' => 2,
     *                          'is_show_result' => 3,
     *                          'at_id' => 附件id,
     *                          'submit_id' => 添加用户id,
     *                          'submit_ca_id' => 管理员id)
     * @param $m_uids 投票用户 array(用户id1, 用户id2, 用户id3)
     * @param $options 投票选项 array(
     *                              array('option' => '选项1','at_id' => 附件id),
     *                              array('option' => '选项2', 'at_id' => 附件id))
     * @param $session  为发送微信消息提供帮助
     * @return bool
     */
    public function add(&$vote, $m_uids, $cd_ids, $options, $session) {

        $val_vote = array(
            'subject' => 'val_subject',
            'end_time' => 'val_endtime',
            'is_single' => array('val_is_single', 'int'),
            'is_show_name' => array('val_is_show_name', 'int'),
            'is_show_result' => array('val_is_show_result', 'int'),
            'is_repeat' => array('val_is_repeat', 'int')
        );
        $this->_params = $vote;
        // 检查投票设置项
        if (!$this->_submit2table($val_vote, $vote)) {
            return false;
        }

        //检查投票用户
        $mems = array();
        $cds = array();
        if (!$this->val_cdid_muid($m_uids, $cd_ids, $mems, $cds)) {
            return false;
        }

        //检查投票选项
        $n_options = array();
        if (!$this->val_options($options, $n_options)) {
            return false;
        }
        //service实例
        $serv_mem = &service::factory('voa_s_oa_nvote_mem');
        $serv_cd = &service::factory('voa_s_oa_nvote_department');
        $serv_option = &service::factory('voa_s_oa_nvote_option');
        $serv_attach = &service::factory('voa_s_oa_nvote_attachment');

        try {
            $this->_serv->begin();
            $vat_id = $vote['at_id'];
            unset($vote['at_id']);
            //添加投票
            $vote = $this->_serv->insert($vote);
            //添加附件
            $serv_attach->add($vat_id, $vote['submit_id'], $vote['id']);

            //添加投票用户
            if (!empty($mems)) {
                foreach ($mems as &$mem) {
                    $mem['nvote_id'] = $vote['id'];
                }
                $serv_mem->insert_multi($mems);
            }

            //添加投票部门
            if (!empty($cds)) {
                foreach ($cds as &$cd) {
                    $cd['nvote_id'] = $vote['id'];
                }
                $serv_cd->insert_multi($cds);
            }
            //添加投票选项
            foreach ($n_options as $option) {
                $option['nvote_id'] = $vote['id'];

                $voat_id = $option['at_id'];
                unset($option['at_id']);
                //插入选项
                $option = $serv_option->insert($option);
                //添加选项附件
                $serv_attach->add($voat_id, $vote['submit_id'], $vote['id'], $option['id']);
            }

            //提交事务
            $this->_serv->commit();
            //发送微信消息通知
            $this->push_create_msg($vote, $m_uids, $cd_ids, $session);
            return true;

        } catch (Exception $e) {

            logger::error(print_r($e, true));
            $this->_serv->rollback();
            /** 入库操作失败 */
            $this->errmsg(100, '操作失败');
            return false;
        }

    }

    /**
     * 后台关闭投票-推送消息通知发起人
     * @param array $nvote
     * @return void
     */
    public function push_create_msg($nvote, $m_uids, $cd_ids, $session) {

        //demo体验号不发送提醒
        if (strpos(strtolower(controller_request::get_instance()->server('SERVER_NAME')), 'demo') !== false) {
            return ;
        }

        $viewurl = voa_wxqy_service::instance()->oauth_url(
            config::get(startup_env::get('app_name').'.oa_http_scheme') .
            $this->_setting['domain'] .
            '/frontend/nvote/view?nv_id=' . $nvote['id'] .
            '&pluginid=' . $this->_sets['pluginid']);

        /*$content = "您的投票【 ".$nvote['subject']." 】已被后台管理员关闭\n"
            . " <a href='".$viewurl."'>点我查看</a>";

        $data = array(
            'mq_touser' => $m_uids,
            'mq_toparty' => '',
            'mq_msgtype' => voa_h_qymsg::MSGTYPE_TEXT,
            'mq_agentid' => (int)$this->_plugins[$this->_sets['pluginid']]['cp_agentid'],
            'mq_message' => $content
        );

        voa_h_qymsg::push_news_send_queue($data);*/

        //获取用户openid
        $serv_m = &service::factory('voa_s_oa_member', array('pluginid' => 0));
        $users = $serv_m->fetch_all_by_ids($m_uids);

        $touser = implode('|', array_column($users, 'm_openid'));

        $serv_d = &service::factory('voa_s_oa_common_department', array('pluginid' => 0));
        $depms = $serv_d->fetch_all_by_key($cd_ids);
        $toparty = implode('|', array_column($depms, 'cd_qywxid'));

        $msg_title = $nvote['subject'];
        //获取发起人名称
        $user_name = '后台管理员';
        if ($nvote['submit_id']) {
            $user = $serv_m->fetch($nvote['submit_id']);
            $user_name = $user['m_username'];
        }
        $msg_desc = '来自' . $user_name . '的投票邀请';
        $msg_url = $viewurl;
        $msg_picurl = '';

        // 发送消息
        voa_h_qymsg::push_news_send_queue($session, $msg_title, $msg_desc, $msg_url, $touser, $toparty, $msg_picurl, (int)$this->_plugins[$this->_sets['pluginid']]['cp_agentid'], $this->_sets['pluginid'], -1);
    }


    /**
     * 检查投票主题
     * @param $subject
     * @param $vote
     * @return bool
     */
    public function val_subject(&$subject, &$vote) {
        $subject = (string)$subject;
        $subject = trim($subject);
        if (!validator::is_string_count_in_range($subject, 1, 30)) {
            $this->errmsg('101', '投票主题长度介于 1到30 个字符之间');
            return false;
        }

        $vote['subject'] = $subject;
        return true;
    }

    /**
     * 检查投票结束时间
     * @param $endtime
     * @param $vote
     * @return bool
     */
    public function val_endtime(&$endtime, &$vote) {
        $endtime = rstrtotime($endtime);
        if ($endtime < startup_env::get('timestamp')) {
            $this->errmsg('104', '投票结束时间必须大于当前时间');
            return false;
        }

        $vote['end_time'] = $endtime;
        return true;
    }


    /**
     * 检查投票选项方式
     * @param $is_single
     * @param $vote
     * @return bool
     */
    public function val_is_single(&$is_single, &$vote) {
        $is_single = rintval($is_single);
        if ($is_single !== voa_d_oa_nvote::SINGLE_YES &&
                $is_single !== voa_d_oa_nvote::SINGLE_NO) {
            $this->errmsg('102', '投票选项应设置单选或多选');
            return false;
        }

        $vote['is_single'] = $is_single;
        return true;
    }

    /**
     * 检查投票方式
     * @param $is_show_name
     * @param $vote
     * @return bool
     */
    public function val_is_show_name(&$is_show_name, &$vote) {
        $is_show_name = rintval($is_show_name);
        if ($is_show_name !== voa_d_oa_nvote::SHOW_NAME_NO &&
            $is_show_name !== voa_d_oa_nvote::SHOW_NAME_YES) {
            $this->errmsg('103', '投票方式应设置匿名或非实名');
            return false;
        }

        $vote['is_show_name'] = $is_show_name;
        return true;
    }

    /**
     * 检查投票显示结果
     * @param $is_show_result
     * @param $vote
     * @return bool
     */
    public function val_is_show_result(&$is_show_result, &$vote) {
        $is_show_result = rintval($is_show_result);
        if ($is_show_result !== voa_d_oa_nvote::SHOW_RESULT_NO &&
            $is_show_result !== voa_d_oa_nvote::SHOW_RESULT_YES) {
            $this->errmsg('105', '投票结果应设置显示或不显示');
            return false;
        }

        $vote['is_show_result'] = $is_show_result;
        return true;
    }

    /**
     * 检查是否重复投票
     * @param $is_repeat
     * @param $vote
     * @return bool
     */
    public function val_is_repeat(&$is_repeat, &$vote) {
        $is_repeat = rintval($is_repeat);
        if ($is_repeat !== voa_d_oa_nvote::REPEAT_NO &&
            $is_repeat !== voa_d_oa_nvote::REPEAT_YES) {
            $this->errmsg('111', '投票应设置重复或不重复');
            return false;
        }

        $vote['is_repeat'] = $is_repeat;
        return true;
    }

    /**
     * 检查投票用户
     * @param $m_uids
     * @param $uids
     * @return bool
     */
    public function val_cdid_muid($m_uids, $cd_ids, &$mems, &$cds) {
        if (empty($cd_ids) &&
                empty($m_uids)) {
            $this->errmsg('106', '投票用户和部门不能都为空');
            return false;
        }

        $is_all = false;
        $serv_cd = &service::factory('voa_s_oa_common_department');

        //检查投票部门
        foreach ($cd_ids as $cd_id) {

            $cd_id = rintval($cd_id);
            //判断是否为全公司
            if ($cd_id == -1) {

                $is_all = true;
            } else {
                //判断所选的部门是否为全公司
                $department = $serv_cd->fetch($cd_id);
                if (!empty($department) &&
                    $department['cd_upid'] == 0) {
                    $is_all = true;
                } else {
                    $cds[]['cd_id'] = $cd_id;
                }
            }
            //全公司
            if ($is_all === true) {
                $departments = $serv_cd->fetch_all();
                $cds = array();
                foreach ($departments as $dep) {
                    $cds[]['cd_id'] = $dep['cd_id'];
                }
                $mems = array();
                return true;
            }

        }
        //检查投票用户
        foreach ($m_uids as $m_uid) {
            $m_uid = rintval($m_uid);
            if ($m_uid < 1) {
                continue;
            }
            $mems[]['m_uid'] = $m_uid;
        }

        if (empty($mems) &&
            empty($cds)) {
            $this->errmsg('107', '投票用户和部门不能都为空');
            return false;
        }
        return true;
    }

    /**
     * 验证投票选项
     * @param $options
     * @param $n_options
     * return bool
     */
    public  function val_options($options, &$n_options) {
        if (!is_array($options) ||
                empty($options)) {
            $this->errmsg('108', '投票选项不能为空');
            return false;
        }

        $o_count = count($options);
        foreach ($options as $index => $op) {
            $option = (string)$op['option'];
            if (!validator::is_string_count_in_range($option, 1, 30)) {
                $this->errmsg('109', '投票选项长度介于 1到30 个字符之间:' . $option);
                return false;
            }

            $n_option['option'] = $option;
            $n_option['at_id'] = !empty($op['at_id']) ? rintval($op['at_id']) : 0;
            $n_option['priority'] = $index;

            for ($i = $index + 1; $i < $o_count; $i++) {
                if ($option == $options[$i]['option']) {
                    $this->errmsg('110', '不能有重复的选项:' . $option);
                    return false;
                }
            }
            $n_options[] = $n_option;
        }

        return true;
    }
}
