<?php
/**
 * voa_uda_frontend_nvote_close
 * 关闭投票
 * User: luckwang
 * Date: 15/3/9
 * Time: 下午4:07
 */

class voa_uda_frontend_nvote_close extends voa_uda_frontend_nvote_abstract {

    public function __construct() {
        parent::__construct();
    }

    /**
     * @param int $close_uid
     * @param int $nvote_id
     * @param bool $only_self 只能关闭自己添加的投票
     * @return bool
     */
    public function close($close_uid, $nvote_id, $only_self = true, $session = null) {

        $close_uid = rintval($close_uid);
        $nvote_id = rintval($nvote_id);

        if ($close_uid < 1) {
            $this->errmsg('201', '获取用户信息失败');
            return false;
        }

        if ($nvote_id < 1) {
            $this->errmsg('202', '请选择需要关闭的投票');
            return false;
        }

        $vote = $this->_serv->get($nvote_id);
        if (empty($vote)) {
            $this->errmsg('203', '投票不存在');
            return false;
        }

        if ($vote['close_status'] == voa_d_oa_nvote::CLOSE_STATUS_YES) {
            $this->errmsg('204', '不能重复关闭投票');
            return false;
        }
        if ($only_self) {
            if ($vote['submit_id'] != $close_uid) {
                $this->errmsg('205', '非自己添加的投票不能关闭');
            }
        }

        $nvote['close_status'] = voa_d_oa_nvote::CLOSE_STATUS_YES;
        if ($only_self === false) {
            $nvote['close_uid'] = $close_uid;
        }
        $nvote['end_time'] = startup_env::get('timestamp');

        $result = $this->_serv->update($nvote_id, $nvote);

        //关闭成功并且是后台关闭推送消息通知发起人
        if ($result) {
            if ($only_self === false &&
                !empty($vote['submit_id'])) {
                $this->push_close_msg($vote, $session);
            }
        }

        return $result;
    }

    /**
     * 后台关闭投票-推送消息通知发起人
     * @param array $nvote
     * @return void
     */
    public function push_close_msg($nvote, $session) {

        //demo体验号不发送提醒
        if (strpos(strtolower(controller_request::get_instance()->server('SERVER_NAME')), 'demo') !== false) {
            return ;
        }

        $viewurl = voa_wxqy_service::instance()->oauth_url(
                                            config::get(startup_env::get('app_name').'.oa_http_scheme') .
                                            $this->_setting['domain'] .
                                            '/frontend/nvote/view?nv_id=' . $nvote['id'] .
                                            '&pluginid=' . $this->_sets['pluginid']);
        /*
        $content = "您的投票【 ".$nvote['subject']." 】已被后台管理员关闭\n"
            . " <a href='".$viewurl."'>点我查看</a>";

        $data = array(
            'mq_touser' => $nvote['submit_id'],
            'mq_toparty' => '',
            'mq_msgtype' => voa_h_qymsg::MSGTYPE_TEXT,
            'mq_agentid' => (int)$this->_plugins[$this->_sets['pluginid']]['cp_agentid'],
            'mq_message' => $content
        );

        voa_h_qymsg::push_send_queue($data);
        */

        //获取用户openid
        $serv_m = &service::factory('voa_s_oa_member', array('pluginid' => 0));
        $users = $serv_m->fetch_by_uid($nvote['submit_id']);
        $touser = $users['m_openid'];

        $toparty = '';
        $msg_title = '您的投票已被后台管理员关闭';
        $msg_desc = '主题：'.$nvote['subject'];
        $msg_url = $viewurl;
        $msg_picurl = '';
        // 发送消息
        voa_h_qymsg::push_news_send_queue($session, $msg_title, $msg_desc, $msg_url, $touser, $toparty, $msg_picurl, (int)$this->_plugins[$this->_sets['pluginid']]['cp_agentid']);
    }
}
