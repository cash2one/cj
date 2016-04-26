<?php
namespace UcRpc\Service;
use Common\Common\WxqyMsg;
use Common\Common\Cache;
use Org\Util\String;
use Exam\Common\Lcs;

class ExamCrontabService extends AbstractService {

    const TJ_STATUS_COMPLETE = 2; // 已提交试卷

	const PAPER_FLAG_NOT_NOTIFIED = 0; // 未通知

	const PAPER_FLAG_NOTIFIED_START = 1; // 已通知[即将开始考试]

	const PAPER_FLAG_NOTIFIED_STOP = 2; // 已通知[即将结束考试]

    const PAPER_FLAG_NOTIFIED_END = 3; // 已通知[考试结束]

	// 构造方法
	public function __construct() {

		parent::__construct();
        //缓存
        $cache = &Cache::instance();
        $cache_setting = $cache->get('Common.setting');
        // 读取插件信息
        $model_plugin = D('Common/CommonPlugin');
        $plugin = $model_plugin->get_by_identifier('exam');
        // 如果 agentid 为空
        if (empty($plugin['cp_agentid'])) {
            return true;
        }
        // 更新 pluginid, agentid 配置
        cfg('PLUGIN_ID', $plugin['cp_pluginid']);
        cfg('AGENT_ID', $plugin['cp_agentid']);

        cfg('EXAM', load_config(APP_PATH.'Exam/Conf/config.php'));
        cfg('face_base_url', cfg('PROTOCAL') . $cache_setting['domain']);

	}

	// 执行计划任务
	public function notify_start($params) {

		$wxqyMsg = WxqyMsg::instance();

        // 获取即将开始的试卷
        $serv_paper = D('Exam/ExamPaper', 'Service');
        $paper = $serv_paper->get($params['papaer_id']);

        if(!$paper['id']){
            return true;
        }
        /*
        if(NOW_TIME<$paper['begin_time']-$paper['notify_begin']*60){
            return true;
        }
        */
        $m_uids=$cd_ids='';
        $this->_send_range($paper, $m_uids, $cd_ids);
        // 发消息给用户
        $title = '【考试提醒】您有一门考试即将开始';
        $desc = "试卷名称：{$paper['name']}\n考试说明：".String::msubstr($paper['intro'],0,60);
        $url = U('/Exam/Frontend/Index/PaperDetail', "paper_id={$paper['id']}", false, true);
        $picurl = cfg('face_base_url') . '/attachment/read/' . $paper['cover_id'];
        $wxqyMsg->send_news($title, $desc, $url, $m_uids, $cd_ids, $picurl, cfg('AGENT_ID'), cfg('PLUGIN_ID'));

		return true;
	}

	public function notify_stop($params) {

		$wxqyMsg = WxqyMsg::instance();

        // 获取即将结束的考卷
        $serv_paper = D('Exam/ExamPaper', 'Service');
        $serv_tj = D('Exam/ExamTj', 'Service');
        $paper = $serv_paper->get($params['papaer_id']);
        if(!$paper['id']){
            return true;
        }
        /*
        if(NOW_TIME<$paper['end_time']-$paper['notify_end']*60){
            return true;
        }
        */
        // 获取未参与的用户
        $m_uids = $serv_tj->get_uids_by_notstart($paper['id']);
        // 发消息给用户
        $title = '【考试提醒】您有一门考试即将结束';
        $desc = "试卷名称：{$paper['name']}\n考试说明：".String::msubstr($paper['intro'],0,60);
        $url = U('/Exam/Frontend/Index/PaperDetail', "paper_id={$paper['id']}", false, true);
        $picurl = cfg('face_base_url') . '/attachment/read/' . $paper['cover_id'];
        $wxqyMsg->send_news($title, $desc, $url, $m_uids, '', $picurl, cfg('AGENT_ID'), cfg('PLUGIN_ID'));

		return true;
	}

    public function notify_over($params) {

        $wxqyMsg = WxqyMsg::instance();
        // 考试时间已到
        $serv_paper = D('Exam/ExamPaper', 'Service');
        $serv_tj = D('Exam/ExamTj', 'Service');
        $paper = $serv_paper->get($params['papaer_id']);
        if(!$paper['id']){
            return true;
        }
        $tjs = $serv_tj->list_by_notsubmit($paper['id']);
        $m_uids=array();
        $tj_ids=array();
        foreach ($tjs as $tj) {
            $m_uids[] = $tj['m_uid'];
            $tj_ids[] = $tj['id'];
        }
        if($m_uids){
            $title = '【考试提醒】考试时间已到，系统自动交卷';
            $desc =  "试卷名称：{$paper['name']}\n考试说明：".String::msubstr($paper['intro'],0,60);
            $url = U('/Exam/Frontend/Index/PaperFinished', "paper_id={$paper['id']}", false, true);
            $picurl = cfg('face_base_url') . '/attachment/read/' . $paper['cover_id'];
            $wxqyMsg->send_news($title, $desc, $url, $m_uids, '', $picurl, cfg('AGENT_ID'), cfg('PLUGIN_ID'));
        }
        $this->_auto_submit($tj_ids, $paper);

        return true;
    }

    /**
     * 获取推送范围
     */
    protected function _send_range($paper, &$m_uids, &$cd_ids) {
        $cd_ids = '';
        $m_uids = '';
        if($paper['is_all']){
            $m_uids="@all";
        }else{
            if(!empty($paper['m_uids'])){
                $m_uids=explode(",", $paper['m_uids']);
            }
            if(!empty($paper['cd_ids'])){
                $cd_ids=explode(",", $paper['cd_ids']);
            }
        }
    }
    /**
     * 自动交卷
     */
    protected function _auto_submit($tj_ids, $paper) {
        $serv_tj = D('Exam/ExamTj', 'Service');
        $serv_ti_tj = D('Exam/ExamTiTj', 'Service');
        $lcs=new Lcs();

        $list=$serv_ti_tj->list_by_tj_ids( implode(',', $tj_ids) );
        // 按统计id入数组
        $tj_tis=array();
        foreach ($list as $k => $v) {
           $tj_tis[$v['tj_id']][]=$v;
        }
        // 检查答案
        foreach ($tj_tis as $tj_id => $tis) {
            $error_num=$score=0;
            $pass_ids=array();
            foreach ($tis as $v) {
                if(empty($v['my_answer'])){
                    $error_num++;
                }else{
                    if(intval($v['type'])==1){
                        // 当类型为问答题，进行文本比对
                        $my_answer=str_replace(array("\r\n", "\r", "\n"), " ", $v['my_answer']);
                        $my_answer=preg_replace("/\s(?=\s)/","\\1", $my_answer);
                        $answer=str_replace(array("\r\n", "\r", "\n"), " ", $v['answer']);
                        $answer=preg_replace("/\s(?=\s)/","\\1", $answer);
                        $pecent=$lcs->getSimilar( $my_answer, $answer);
                        if($pecent>0.65){
                            $score += $v['score'];
                            $pass_ids[]=$v['id'];
                        }else{
                            $error_num++;
                        }
                    }else{
                        if( $v['my_answer'] != $v['answer'] ){
                            $error_num++;
                        }else{
                            $score += $v['score'];
                            $pass_ids[]=$v['id'];
                        }
                    }
                }
            }
            // 更新数据库
            $serv_tj->update_tj($tj_id, $paper, $error_num, $score, $paper['paper_time'], self::TJ_STATUS_COMPLETE);
            $serv_ti_tj->update_by_conds( array('id'=>$pass_ids), array('is_pass'=>1));
        }

    }
}
