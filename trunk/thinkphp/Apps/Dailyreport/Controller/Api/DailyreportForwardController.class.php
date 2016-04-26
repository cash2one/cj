<?php

/**
 * User: keller
 * Date: 16/3/23
 * Time: 下午8:15
 */

namespace Dailyreport\Controller\Api;

class DailyreportForwardController extends AbstractController {

    protected $_require_login = true;

    /**
     * 转发报告
     * @author keller<likai@vchangyi.com>
     * @route
     * @method
     */
    public function Forwad_post() {
        $post = I('post.');
        $dr_id = $post['dr_id'];
        $forword_uid = $post['forword_uid'];
        $forword_uname = $post['forword_uname'];
        $remark = $post['remark'];
        $m_uids = $post['m_uids'];
        $len_remark = mb_strlen($remark,'utf8');

        if(!$dr_id){
            E('_ERR_DAILYREPORT_FORWORD_ID');
        }

        if(!$m_uids){
            E('_ERR_DAILYREPORT_FORWORD_MUIDS');
        }


        if ($len_remark > 100) {
            E('_ERR_DAILYREPORT_FORWORD_REMARK');
            return false;
        }
        $serv_dr = D('Dailyreport', 'Service');
        $dr = $serv_dr->get($dr_id);
        if (!$dr) {
            E('_ERR_DAILYREPORT_FORWORD_ERR');
            return false;
        }

        $dr['dr_forword_uid'] = $forword_uid;
        $dr['dr_forword_uname'] = $forword_uname;
        $dr['dr_from_dr_id'] = $dr_id;
        $dr['dr_remark'] = $remark;

        unset($dr['dr_id'],$dr['dr_created'],$dr['dr_updated']); //取消dr_id设置//取消dr_created设置//取消dr_updated设置

        $this->start_trans();

        $fdr_id = $serv_dr->save($dr);
        $arr_m_uid = explode(",", $m_uids);
        foreach ($arr_m_uid as $m_uid) {
            $member = $serv_dr->getMember($m_uid);

            $drm = array(
                'dr_id' => intval($fdr_id),
                'm_uid' => $m_uid,
                'm_username' => $member['m_username'],
                'drm_status' => 1,
                'get_level' => 1
            );

            //接收人表信息记录
            $mem = $serv_dr->saveMem($drm);
            if (!$mem) {
                $this->rollback();
                E('_ERR_DAILYREPORT_FORWORD_ERR');
                return false;
            }

            $drr = array(
                'is_read' => 1,
                'dr_id' => $fdr_id,
                'm_uid' => $m_uid,
                'status' => 1
            );

            //阅读表信息记录
            $read = $serv_dr->saveRead($drr);
            if (!$read) {
                $this->rollback();
                E('_ERR_DAILYREPORT_FORWORD_ERR');
                return false;
            }
        }
        //post表信息记录
        $o_drp = $serv_dr->getPost($dr_id);
        $drp = array(
            'drp_forword_uid' => $forword_uid,
            'drp_forword_uname' => $forword_uname,
            'dr_id' => $fdr_id,
            'drp_subject' => $o_drp['drp_subject'],
            'drp_message' => $o_drp['drp_message'],
            'drp_first' => $o_drp['drp_first'],
            'drp_status' => $o_drp['drp_status'],
            'drp_new_message' => $o_drp['drp_new_message']
        );
        if (!$drp_id = $serv_dr->savePost($drp)) {
            $this->rollback();
            E('_ERR_DAILYREPORT_FORWORD_ERR');
            return false;
        }
        //附件表记录
        if($serv_dr->saveAtta($dr_id,$fdr_id,$drp_id,$forword_uid,$forword_uname)){
            $this->rollback();
            E('_ERR_DAILYREPORT_FORWORD_ERR');
            return false;
        }
        $this->commit();
        //推送消息到微信
        $send_msg = "您收到一条份来自{$forword_uname}转发的报告";
        $send_body = "备注:".$remark."\n".$dr['dr_subject'];
        $cache = &\Common\Common\Cache::instance();
        $setting = $cache->get('Common.setting');
        $domain = C('PROTOCAL') . $setting['domain'];
        $send_url = $domain . '/h5/index.html?#/app/page/dailyreport/dailyreport-detail?id=' .$fdr_id . '&name='.$dr['dr_subject'];
        $send_uids = array_unique(explode(',',$m_uids));
        \Common\Common\WxqyMsg::instance()
                ->send_news($send_msg, $send_body, $send_url, $send_uids);
        $this->_result = array(
            'dr_id' => $fdr_id
        );
        return true;
    }

}
