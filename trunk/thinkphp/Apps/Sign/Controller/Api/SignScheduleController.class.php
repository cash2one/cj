<?php
/**
 * SignScheduleController.class.php
 * $author$
 */

namespace Sign\Controller\Api;

use Common\Common\Cache;
use Sign\Model\SignScheduleModel;
use Think\Log;

class SignScheduleController extends AbstractController {


    /**
     * 根据m_uid和cd_id 查询部门排班数据
     * @return bool
     */
    public function dept_schedule_get(){

        $params = I('get.');
        if(empty($params['type'])){
            E('_ERROR_PARAM_VAL_NULL');
            return false;
        }

		if(empty($params['schedule_type'])){
			E('_ERROR_PARAM_VAL_NULL');
			return false;
		}

        //如果是休息日上班，验证班次id
        if($params['type'] == SignScheduleModel::REST_AND_WORK_STATUS){
            if(empty($params['batch_id'])){
                E('_ERROR_PARAM_VAL_NULL');
                return false;
            }
        }
        if(empty($params['schedule_id'])){
            E('_ERROR_PARAM_VAL_NULL');
            return false;
        }
        if(is_empty_variable($params['cd_id'])){
            E('_ERROR_PARAM_VAL_NULL');
            return false;
        }
        if(is_empty_variable($params['batch_index'])){
            E('_ERROR_PARAM_VAL_NULL');
            return false;
        }

        $sign_schedule_service = D('Sign/SignSchedule', 'Service');
        $params['m_uid'] = $this->_login->user['m_uid'];
        $result = $sign_schedule_service->dept_schedule_by_params($params);

        $this->_result = array(
            'data' => $result
        );


    }

    /**
     * 根据m_uid获取所属全部部门和班次信息
     */
    public function dept_batch_get(){

        $sign_schedule_service = D('Sign/SignSchedule', 'Service');
        $user = $this->_login->user;
        $m_uid = $user['m_uid'];
        //本地测试写死
        //$m_uid = 10805;

        $result = $sign_schedule_service->get_batch_by_muid($m_uid);
		Log::record('返回结果：'.var_export($result,true));
        $this->_result = array(
            'list' => $result
        );
    }

    public function schedule_rule_get(){
        $params = I('get.');
        if(empty($params['type'])){
            E('_ERROR_PARAM_VAL_NULL');
            return false;
        }
        //如果是休息日上班，验证班次id
        if($params['type'] == SignScheduleModel::REST_AND_WORK_STATUS){
            if(empty($params['batch_id'])){
                E('_ERROR_PARAM_VAL_NULL');
                return false;
            }
        }
        if(empty($params['schedule_id'])){
            E('_ERROR_PARAM_VAL_NULL');
            return false;
        }
        if(is_empty_variable($params['cd_id'])){
            E('_ERROR_PARAM_VAL_NULL');
            return false;
        }
        if(is_empty_variable($params['batch_index'])){
            E('_ERROR_PARAM_VAL_NULL');
            return false;
        }

        $sign_schedule_service = D('Sign/SignSchedule', 'Service');
        $result = $sign_schedule_service->get_schedule_rule($params);
        $this->_result = array(
            'data' => $result
        );
    }


}


