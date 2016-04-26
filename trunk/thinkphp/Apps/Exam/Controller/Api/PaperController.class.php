<?php

namespace Exam\Controller\Api;
use Think\Log;
use Exam\Common\Lcs;

class PaperController extends AbstractController {

    /**
     * 获取试卷列表
     */
    public function list_get() {
        $status = I('get.is_finished', 0, 'intval'); //已完成、未完成状态
        $serv_tj = D('Exam/ExamTj', 'Service');

        $papers = $serv_tj->list_by_status($status, $this->_login->user['m_uid']);
        // 返回操作
        $this->_result = array(
            'papers' => $papers,
            'now_time' => NOW_TIME,
        );
        return true;
    }
    /**
     * 获取试卷详情
     */
	public function detail_get() {
        $id = I('get.id');
        $paper_id = I('get.paper_id');
        if(empty($id)&&empty($paper_id)){
        	$this->_set_error('_ERROR_PAPER_ID_IS_EMPTY');
            return false;
        }

        // 获取我的试卷
        $serv_tj = D('Exam/ExamTj', 'Service');
        if($id>0){
            $paper = $serv_tj->get_with_paper_by_id($id);
        }else{
            $paper = $serv_tj->get_with_paper_by_id(0,$paper_id,$this->_login->user['m_uid']);
        }

        $authcode = '';
        if(NOW_TIME < $paper['end_time']) {
            $authcode = $this->base_encode(authcode($paper['type'] . "\t" . $paper['id'], self::AUTH_CODE, 'ENCODE'));
        }
        $time_status=0;// 0 未开始 1 已开始 2 已结束 3 已终止
        if($paper['begin_time']<NOW_TIME){
            $time_status=1;
        }
        if($paper['end_time']<NOW_TIME){
            $time_status=2;
        }
        if($paper['paper_status']==2){
            $time_status=3;
        }

		// 返回操作
		$this->_result = array(
			'paper' => array(
				'id' => $paper['id'],
				'name' => $paper['paper_name'],
                'total_score' => $paper['total_score'],
				'pass_score' => $paper['pass_score'],
				'ti_num' => $paper['ti_num'],
				'begin_time' => $paper['begin_time'],
                'time_status' => $time_status,
				'end_time' => $paper['end_time'],
                'paper_time' => $paper['paper_time'],
				'departments' => implode(",", $paper['departments']),
                'members' => implode(",", $paper['members']),
				'intro' => $paper['intro'],
				'type' => $paper['type'],
                'status' => $paper['status'],
                'paper_status' => $paper['paper_status'],
				'auth' => $authcode,
                'cover' => $serv_tj->get_attachment($paper['cover_id'])
			)
		);

		return true;
	}

    /**
     * 获取试题，考试开始
     */
	public function question_get() {
        $auth = I('get.auth');
        if(empty($auth)) {
        	$this->_set_error('_ERROR_ARG');
            return false;
        }
        $decodestr = authcode($this->base_decode($auth), self::AUTH_CODE);
        if(!$decodestr) {
        	$this->_set_error('_ERROR_ARG');
            return false;
        }
        list($type, $id) = explode("\t", $decodestr);

        // 获取我的试卷
        $serv_tj = D('Exam/ExamTj', 'Service');
        $paper = $serv_tj->get_with_paper_by_id($id);

        // 按类型获取题库
        $serv_ti = D('Exam/ExamTi', 'Service');
        if($type != 2) {
        	$serv_detail = D('Exam/ExamPaperDetail', 'Service');
        	$tis = $serv_detail->list_with_ti_by_paperid($paper['paper_id']);
            if(empty($tis)) {
                $this->_set_error('_ERROR_ARG');
                return false;
            }
        	$ti_ids = array();
        	foreach ($tis as $k=>$v) {
                $tis[$k]['options'] = empty($v['options']) ? array() : explode("\r\n", $v['options']);
        		$ti_ids[] = $v['id'];
        	}
    		//$tis_norder = $serv_ti->list_outer_tis_by_ids($ti_ids);

        } else {
            if( !empty($paper['random_tids'])){
                // 读取随机试题
                $ti_ids=explode(',', $paper['random_tids']);
                $tis = $serv_ti->list_outer_tis_by_ids($ti_ids);
            }else{
                // 第一次随机试题
                $serv_paper = D('Exam/ExamPaper', 'Service');
                $tis = $serv_ti->list_random(explode(',', $paper['tiku']), $paper['rules']);
                $ti_ids = array_keys($tis);
                $serv_tj->update_random_tids($id, implode(',', $ti_ids));// 保存随机试题
                $tis=array_values($tis);
            }
        }
        // 标记为已开始考试 第一次开始进入
        $serv_ti_tj = D('Exam/ExamTiTj', 'Service');
        $my_answers=array();
        if($paper['status']==0){
            // 记录开始时间和 更新状态
            $serv_tj->update_to_begin($id, self::TJ_STATUS_START);
            $paper['my_begin_time']=NOW_TIME;
            // 生成答题卡
            $serv_ti_tj->insert_tj($this->_login->user['m_uid'], $paper['paper_id'], $paper['id'], $tis);
        }else{
            // 按考卷读取答题卡
            $my_answers = $serv_ti_tj->list_by_tj_id($paper['id']);
        }
        // 将考生答案写入试题数组
        foreach ($tis as $k=>$v) {
            $tis[$k]['your_answer']=!empty($my_answers[$v['id']]['my_answer'])?$my_answers[$v['id']]['my_answer']:'';
        }

        $tauth = authcode(implode(',', $ti_ids) . "\t" . NOW_TIME, self::AUTH_CODE, 'ENCODE');

		// 返回操作
		$this->_result = array(
            'paper'=>array(
                'name' => $paper['paper_name'],
                'begin_time' => $paper['begin_time'],
                'end_time' => $paper['end_time'],
                'ti_num' => $paper['ti_num'],
                'paper_time' => $paper['paper_time'],
                'status' => $paper['status'],
                'paper_status' => $paper['paper_status'],
                'my_begin_time' => $paper['my_begin_time'],
            ),
			'question_list' => $tis,
			'tauth' => $tauth,
            'auth' => $auth,
            'ls_id' => $paper['id'].'_'.$this->_login->user['m_uid'],// 本地存储答案id
            'startime' => NOW_TIME,
		);

		return true;
	}
    /**
     * 提交考试答案
     */
	public function paper_post() {
		// 验证auth
        $auth = I('post.auth');
        if(empty($auth)) {
        	$this->_set_error('_ERROR_ARG');
            return false;
        }

        $decodestr = authcode($this->base_decode($auth), self::AUTH_CODE);
        if(!$decodestr) {
        	$this->_set_error('_ERROR_ARG');
            return false;
        }
        list($type, $id) = explode("\t", $decodestr);

        // 验证tauth
        $tauth = I('post.tauth');
        if(empty($tauth)) {
        	$this->_set_error('_ERROR_ARG');
            return false;
        }

        $tdecodestr = authcode($tauth, self::AUTH_CODE);
        if(!$tdecodestr) {
        	$this->_set_error('_ERROR_ARG');
            return false;
        }
        list($tidstr, $start_time) = explode("\t", $tdecodestr);
        $tids = explode(',', $tidstr);

        // 获取我的试卷
        $serv_tj = D('Exam/ExamTj', 'Service');
        $paper = $serv_tj->get_by_id($id);

        if($paper['status']>=2||$paper['paper_status']==0){ // 如果是已完成的直接返回
            $this->_result = array(
                'id' => $id
            );
            return true;
        }

        $user_answers = I('post.answers', '', 'trim');
        if($type != 2) {
        	$serv_detail = D('Exam/ExamPaperDetail', 'Service');
        	$answers = $serv_detail->list_answer_by_paperid($paper['paper_id']);
        } else {
    		$serv_ti = D('Exam/ExamTi', 'Service');
    		$answers = $serv_ti->list_answer_by_tids($tids, $paper['rules']);
        }

        list($score, $error_num, $tj) = $this->check_answer($user_answers, $answers);

        // 得分及错题数
        //Log::write('exam api: '.var_export($tj, true));
        //return true;

        // 考试用时
        $use_time = ceil((NOW_TIME - $paper['my_begin_time']) / 60);
        if($use_time>$paper['paper_time']){
            $use_time=$paper['paper_time']; // 超过时长的处理
        }
        // 考试统计
        $serv_tj = D('Exam/ExamTj', 'Service');
        $serv_tj->update_tj($id, $paper, $error_num, $score, $use_time, self::TJ_STATUS_COMPLETE);
        // 试题统计
        $serv_ti_tj = D('Exam/ExamTiTj', 'Service');
        $serv_ti_tj->update_all($paper['id'], $tj);

		// 返回操作
		$this->_result = array(
            'id' => $id
		);

		return true;
	}
    /**
     * 检查考试答案
     */
	protected function check_answer($user_answers, $answers) {
		$score = $error_num = 0;
		$tj = array();

        $lcs=new Lcs();// 文本对比类
        $pecent=0;


		foreach($answers as $tid => $answer) {
			$tj[$tid] = array(
				'answer' => $answer['answer']
			);
            if(!isset($user_answers[$tid])||empty($user_answers[$tid]['answer'])||$user_answers[$tid]['answer']==''){
                // 未答题的处理
                $error_num++;
                $tj[$tid]['my_answer'] = '';
                $tj[$tid]['is_pass'] = 0;
            }else{
                $tj[$tid]['my_answer'] = $user_answers[$tid]['answer'];
                if(intval($answer['type'])==1){
                    // 当类型为问答题，进行文本比对
                    $user_answer=str_replace(array("\r\n", "\r", "\n"), " ", $user_answers[$tid]['answer']);
                    $user_answer=preg_replace("/\s(?=\s)/","\\1", $user_answer);

                    $sys_answer=str_replace(array("\r\n", "\r", "\n"), " ", $answer['answer']);
                    $sys_answer=preg_replace("/\s(?=\s)/","\\1", $sys_answer);

                    $pecent=$lcs->getSimilar( $user_answer, $sys_answer);
                    if($pecent>0.65){
                        $score += $answer['score'];
                        $tj[$tid]['is_pass'] = 1;
                    }else{
                        $error_num++;
                        $tj[$tid]['is_pass'] = 0;
                    }
                }else{
                    if( $user_answers[$tid]['answer'] != $answer['answer'] ){
                        $error_num++;
                        $tj[$tid]['is_pass'] = 0;
                    }else{
                        $score += $answer['score'];
                        $tj[$tid]['is_pass'] = 1;
                    }
                }
            }


		}

		return array($score, $error_num, $tj);
	}

    /**
     * 提交单个答案
     */
    public function answer_post() {
        // 验证auth
        $auth = I('post.auth');
        if(empty($auth)) {
            $this->_set_error('_ERROR_ARG');
            return false;
        }
        $decodestr = authcode($this->base_decode($auth), self::AUTH_CODE);
        if(!$decodestr) {
            $this->_set_error('_ERROR_ARG');
            return false;
        }
        list($type, $id) = explode("\t", $decodestr);
        $ti_id = I('post.ti_id');
        $answer = I('post.answer');

        $serv_ti_tj = D('Exam/ExamTiTj', 'Service');
        $serv_ti_tj->update_answer($ti_id, $id, $answer);

    }

    /**
     * 服务器时间
     */
    public function time_get() {
        $this->_result = array(
            'nowtime' => NOW_TIME,
        );
        return true;
    }


}