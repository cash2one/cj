<?php

namespace Exam\Controller\Api;
use Common\Common\WxqyMsg;
use Org\Util\String;
use Exam\Common\Lcs;

class MyPaperController extends AbstractController {

    /**
     * 试卷结果
     */
    public function result_get() {
        $id = I('get.id');
        $paper_id = I('get.paper_id');
        $wrong=I('get.wrong');
        if(empty($id)&&empty($paper_id)){
            $this->_set_error('_ERROR_ARG');
            return false;
        }


        // 获取统计
        $serv_tj = D('Exam/ExamTj', 'Service');
        if($id>0){
            $tj = $serv_tj->get_with_paper_by_id($id);
        }else{
            $tj = $serv_tj->get_with_paper_by_id(0,$paper_id,$this->_login->user['m_uid']);
        }

        $serv_titj = D('Exam/ExamTiTj', 'Service');
        $tjs = $serv_titj->list_with_ti_by_tj_id($tj['id']);


        if(empty($tjs)) {
            $this->_set_error('_ERROR_ARG');
            return false;
        }

        $no=1;
        $my_noanswer_num=0;
        $my_right_num=0;
        $my_wrong_num=0;


        foreach ($tjs as $k => $v) {
            if($v['my_answer']){
                $tjs[$k]['result_status']=$v['is_pass']?1:2;
                if($v['is_pass']){
                    $my_right_num++;
                }else{
                    $my_wrong_num++;
                }
            }else{
                $tjs[$k]['result_status']=0;
                $my_noanswer_num++;
            }
            $tjs[$k]['options'] = empty($v['options']) ? array() : explode("\r\n", $v['options']);
            $tjs[$k]['no']=$no++; // 设置题号

            if($wrong&&$tjs[$k]['result_status']==1){
                unset($tjs[$k]);
            }
        }
        $this->_result = array(
            'detail' => array(
                'id'=>$tj['id'],
                'my_is_pass'=>$tj['my_is_pass'],
                'my_score'=>$tj['my_score'],
                'my_time'=>$tj['my_time'],
                'my_error_num'=>$tj['my_error_num'],
                'paper_name'=>$tj['paper_name'],
                'paper_time'=>$tj['paper_time'],
                'total_score' => $tj['total_score'],
                'departments' => implode(",", $tj['departments']),
                'members' => implode(",", $tj['members']),
                'pass_score' => $tj['pass_score'],
                'begin_time' => $tj['begin_time'],
                'end_time' => $tj['end_time'],
                'intro' => $tj['intro'],
                'ti_num' => $tj['ti_num'],
                'my_wrong_num' => $my_wrong_num,
                'my_right_num' => $my_right_num,
                'my_noanswer_num' => $my_noanswer_num,
                'paper_status' => $tj['paper_status'],
                'cover' => $serv_tj->get_attachment($tj['cover_id'])
            ),
            'question_list'=>array_values($tjs),
        );
    }


    public function test() {
        
        cfg('EXAM', load_config(APP_PATH.'Exam/Conf/config.php'));
        echo cfg('exam.app_exam_h5');
        return true;
    }
}