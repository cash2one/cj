<?php

namespace Exam\Controller\Api;
use Common\Common\Cache;
use Common\Common\Plugin;

abstract class AbstractController extends \Common\Controller\Api\AbstractController {

    const AUTH_CODE = "uqi7#FvFQwL%ldfOD@^PJobkJe&&xvxi";

    const TJ_STATUS_START = 1; // 已参与考试

    const TJ_STATUS_COMPLETE = 2; // 已提交试卷

    const PAPER_FLAG_NOT_NOTIFIED = 0; // 未通知

    const PAPER_FLAG_NOTIFIED_START = 1; // 已通知[即将开始考试]

    const PAPER_FLAG_NOTIFIED_STOP = 2; // 已通知[即将结束考试]

    const PAPER_FLAG_NOTIFIED_END = 3; // 已通知[考试结束]

    public function before_action($action = '') {
        if(parent::before_action($action)) {
            $this->check_paper();
            return true;
        }

        return false;
    }

    /*
     * 获取插件配置
     */
    protected function _get_plugin() {
        // 获取插件信息
        $this->_plugin = &Plugin::instance('Exam');

        // 更新 pluginid, agentid 配置
        cfg('PLUGIN_ID', $this->_plugin->get_pluginid());
        cfg('AGENT_ID', $this->_plugin->get_agentid());
        cfg('PLUGIN_IDENTIFIER', $this->_plugin->get_name());
        return true;
    }


    public function after_action($action = '') {

        return parent::after_action($action);
    }

    /**
     * 根据应用名称 获取应用相关信息
     * @param $cp_identifier
     * @return string
     */
    public function get_plugin_id($cp_identifier) {

        // 字符串小写
        $cp_identifier = strtolower($cp_identifier);

        // 获取插件列表
        $cache = &Cache::instance();
        $plugins = $cache->get('Common.plugin');

        foreach($plugins as $_k => $_v) {
            if ($_v['cp_identifier'] == $cp_identifier) {
                return $plugins[$_k];
            }
        }

        return array();
    }

    protected function base_encode($str) {
        $src  = array("/","+","=");
        $dist = array("_a","_b","_c");
        $new  = str_replace($src,$dist,$str);
        return $new;
    }

    protected function base_decode($str) {
        $src = array("_a","_b","_c");
        $dist  = array("/","+","=");
        $new  = str_replace($src,$dist,$str);
        return $new;
    }

    protected function check_paper() {
        // 我的部门ID
        $serv_md = D('Common/MemberDepartment', 'Service');
        $departments = $serv_md->list_by_uid($this->_login->user['m_uid']);
        $my_cd_ids = array();
        foreach ($departments as $department) {
            $my_cd_ids[] = $department['cd_id'];
        }

        // 我已参加的考试
        $serv_tj = D('Exam/ExamTj', 'Service');
        $my_tjs = $serv_tj->list_by_uid($this->_login->user['m_uid']);
        $paperids = array();
        foreach ($my_tjs as $tj) {
            $paperids[] = $tj['paper_id'];
        }

        $dempartment_model = D('Common/CommonDepartment');
        $serv_paper = D('Exam/ExamPaper', 'Service');
        $papers = $serv_paper->list_uncompletes();
        $tjs = array();
        foreach($papers as $paper) {
            if(!in_array($paper['id'], $paperids)) {
                $cd_ids = empty($paper['cd_ids']) ? array() : explode(',', $paper['cd_ids']);
                $m_uids = empty($paper['m_uids']) ? array() : explode(',', $paper['m_uids']);
                $arr_intersect = array_intersect($my_cd_ids, $cd_ids);// xavi mod
                if(!empty($arr_intersect) || in_array($this->_login->user['m_uid'], $m_uids)) {
                    $conditions = array('cd_id' => explode(',', $paper['cd_ids']));
                    $departments =  $dempartment_model->list_by_conds($conditions);
                    foreach ($departments as $value) {
                        $paper['departments'][] = $value['cd_name'];
                    }

                    $tjs[] = array(
                        'm_uid' => $this->_login->user['m_uid'],
                        'paper_id' => $paper['id'],
                        'paper_name' => $paper['name'],
                        'total_score' => $paper['total_score'],
                        'pass_score' => $paper['pass_score'],
                        'ti_num' => $paper['ti_num'],
                        'begin_time' => $paper['begin_time'],
                        'end_time' => $paper['end_time'],
                        'paper_time' => $paper['paper_time'],
                        'departments' => implode(',', $paper['departments']),
                        'intro' => $paper['intro'],
                        'status' => 0 // 0:未参加 1:已开始考试 2:考试完成
                    );
                }
            }
        }

        if(!empty($tjs)) {
            $serv_tj->insert_all($tjs);
        }
    }
}
