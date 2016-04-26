<?php

/**
 * User: keller
 * Date: 16/3/16
 * Time: 下午11:20
 */

namespace Dailyreport\Service;

use Common\Common\WxqyMsg;

class DailyreportService extends AbstractService {

    protected $dr_model;

    // 构造方法
    public function __construct() {
        parent::__construct();
        $this->dr_model = D('Dailyreport');
    }

    public function get($dr_id) {
        if ($dr = $this->dr_model->get($dr_id)) {
            return $dr;
        }
        return false;
    }

    public function save($dr) {
        if ($dr = $this->dr_model->insert($dr)) {
            return $dr;
        }
        return false;
    }

    public function getMember($m_uid) {
        if ($member = $this->dr_model->getMember($m_uid)) {
            return $member;
        }
        return false;
    }

    public function getMembers($m_uids) {
        if ($members = $this->dr_model->getMember($m_uids)) {
            return $members;
        }
        return false;
    }

    public function getPost($dr_id) {
        $drp_m = D('DailyreportPost');
        if ($post = $drp_m->get_post($dr_id)) {
            return $post;
        }
        return false;
    }

    public function savePost($drp) {
        $drp_m = D('DailyreportPost');
        if ($drp_id = $drp_m->insert($drp)) {
            return $drp_id;
        }
        return false;
    }

    public function saveMem($drm) {
        $drm_m = D('DailyreportMem');
        if ($mem = $drm_m->insert($drm)) {
            return $mem;
        }
        return false;
    }

    public function saveRead($drr) {
        $drr_m = D('DailyreportRead');
        if ($read = $drr_m->insert($drr)) {
            return $read;
        }
        return false;
    }

    /**
     * 获取日报列表
     */
    public function get_list($param) {
        if ($drs = $this->dr_model->get_list($param)) {
            return $drs;
        }
        return false;
    }

    /**
     * 删除日报
     * @author keller<likai@vchangyi.com>
     * @route
     * @method
     * @param $drt_id
     */
    public function del_dr($dr_id) {
        if ($this->dr_model->del_dr($dr_id)) {
            return true;
        }
        return false;
    }

    public function get_admin_report($dr_id) {
        if ($dr = $this->dr_model->get_admin_report($dr_id)) {
            return $dr;
        }
        return false;
    }

    public function get_admin_report_comments($dr_id, $page) {
        if ($comments = $this->dr_model->get_admin_report_comments($dr_id, $page)) {
            return $comments;
        }
        return false;
    }

    public function del_admin_report_comment($drp_id) {
        $drp_m = D('DailyreportPost');
        if ($drp_m->delete($drp_id)) {
            return true;
        }
        return false;
    }

    /**
     * 导出csv文件
     * @author keller<likai@vchangyi.com>
     * @route
     * @method
     * @param $param
     */
    public function get_export($param) {
        if ($exports = $this->dr_model->export_dr($param)) {
            return $exports;
        }
        return false;
    }

    /**
     * 导出csv文件统计数
     * @author keller<likai@vchangyi.com>
     * @route
     * @method
     * @param $param
     * @return bool
     */
    public function get_export_count($param) {
        if ($count = $this->dr_model->export_count_dr($param)) {
            return $count;
        }
        return false;
    }

    /**
     * 获取评论列表
     * @author keller<likai@vchangyi.com>
     * @route
     * @method
     * @param $param
     * @return bool
     */
    public function get_comment_list($param) {
        $drp_m = D('DailyreportPost');
        if ($comments = $drp_m->get_comment_list($param)) {
            return $comments;
        }
        return false;
    }

    /**
     * 添加评论
     * @author keller<likai@vchangyi.com>
     * @route
     * @method
     * @param $param
     * @return bool
     */
    public function add_comment($param, $m_uid) {
        $drp_m = D('DailyreportPost');
        $dr_m = D('Dailyreport');
        if ($result = $drp_m->add_comment($param)) {
            $message = $dr_m->get($param['dr_id']);
            //推送内容
            $cache = &\Common\Common\Cache::instance();
        $setting = $cache->get('Common.setting');
        $domain = C('PROTOCAL') . $setting['domain'];
            $url = $domain . '/h5/index.html?#/app/page/dailyreport/dailyreport-detail?id=' . $param['dr_id'] . '&name=' . $message['dr_subject'];
            if ($message['dr_forword_uid'] > 0) {
                $send_body = $param['m_username'] . ":" . $param['drp_message'] . "\n来自" . $message['dr_forword_uname'] . '转发的报告:' . $message['dr_subject'];
            } else {
                $send_body = $param['m_username'] . ":" . $param['drp_message'] . "\n" . $message['dr_subject'];
            }
            //如果是评论则推送给报告发送人
            if (@$param['drp_comment_user_name']) {
                WxqyMsg::instance()
                        ->send_news('您收到一条回复消息', $send_body, $url, $param['drp_comment_user_id']);
                return true;
            }
            //如果判断当前的报告是否是自己的
            if ($m_uid == $message['m_uid'] && $message['dr_forword_uid'] == 0) {
                return false;
            }
            if ($m_uid != $message['dr_forword_uid']) {
                $target_id = $message['dr_forword_uid'] == 0 ? $message['m_uid'] : $message['dr_forword_uid'];
                WxqyMsg::instance()
                        ->send_news('您收到一条评论消息', $send_body, $url, $target_id);
            }
            
        }
        return false;
    }

    /**
      保存草稿
     * */
    public function save_api_dailyreport($post, $m_uid, $m_username) {
        if ($dr_id = $this->dr_model->save_api_dailyreport($post, $m_uid, $m_username)) {
            return $dr_id;
        }
        return false;
    }

    /**
     * 
     * @param type $dr_id
     * @param type $m_uid
     * @return boolean
     */
    public function get_dailyreport_info_api($dr_id, $m_uid) {
        if ($dailyreport = $this->dr_model->get_dailyreport_info_api($dr_id, $m_uid)) {
            return $dailyreport;
        }
        return false;
    }

    public function get_my_send_dailyreport_list($m_uid, $page, $q, $k, $drt_id) {
        if ($list = $this->dr_model->get_my_send_dailyreport_list($m_uid, $page, $q, $k, $drt_id)) {
            return $list;
        }
        return false;
    }

    public function get_track($dr_id) {
        $data = $this->dr_model->get_track($dr_id);
        return $data ? $data : array();
    }

    /**
     * 
     * @param type $m_uid
     * @param type $page
     * @param type $q
     * @param type $k
     * @param type $drt_id
     * @return boolean
     */
    public function get_my_responsibles($m_uid, $page, $q, $k, $drt_id) {
        if ($list = $this->dr_model->get_my_responsibles($m_uid, $page, $q, $k, $drt_id)) {
            return $list;
        }
        return false;
    }

    public function get_for_me($m_uid, $page, $q, $k, $drt_id) {
        if ($list = $this->dr_model->get_for_me($m_uid, $page, $q, $k, $drt_id)) {
            return $list;
        }
        return false;
    }

    public function get_past_api($m_uid, $page, $q, $k, $drt_id, $target_id) {
        if ($list = $this->dr_model->get_past_api($m_uid, $page, $q, $k, $drt_id, $target_id)) {
            return $list;
        }
        return false;
    }
    
    public function saveAtta($dr_id,$fdr_id,$drp_id,$forword_uid,$forword_uname){
        return $this->dr_model->saveAtta($dr_id,$fdr_id,$drp_id,$forword_uid,$forword_uname);
    }

}
