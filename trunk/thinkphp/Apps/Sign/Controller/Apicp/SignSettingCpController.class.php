<?php
/**
 * SignSettingCpController.class.php
 * 考勤配置
 * @author: anything
 * @createTime: 2015/02/22 18:28
 * @version: $Id$ 
 * @copyright: 畅移信息
 */
namespace Sign\Controller\Apicp;
use Common\Common\Cache;
use Sign\Model\SignSettingModel;

class SignSettingCpController extends AbstractController {

    const SIGN_MSG_MAXLENTH = 20;//签到/签退提示内容长度
    /**
     * 获取设置表数据
     */
//    public function setting_get(){
//
//        $cache = &Cache::instance();
//        $cache_setting = $cache->get('Sign.setting');
//
//        /*如果sign_setting 没有菜单配置数据，取配置文件里的默认配置*/
//        $wxcpmenu_bool = false;
//        if(!empty($cache_setting['wxcpmenu'])){
//            $wxcpmenu_bool = true;
//        }
//
//        $result = array(
//            'out_sign_include' => $cache_setting['out_sign_include'],
//            'wxcpmenu' => $wxcpmenu_bool ? $cache_setting['wxcpmenu'] : cfg('menu_qywx')
//        );
//
//        // 返回数据
//        $this->_result = array(
//            'setting' => $result
//        );
//
//    }

    /**
     * 更新“外出考勤是否记为出勤”配置 暂时不上
     */
//    public function update_setting_post(){
//
//        $params = I('post.');
//
//        if(empty($params['out_sign_include'])){
//            E('_ERROR_PARAM_VAL_NULL');
//            return false;
//        }
//
//        $sign_setting_service = D('Sign/SignSetting', 'Service');
//
//
//        $sign_setting_service->update_setting($params);
//
//    }


    /**
     * 获取考勤配置数据
     */
    public function config_get(){

        $type = I('get.type');

        if(empty($type)){
            E('_ERROR_PARAM_VAL_NULL');
            return false;
        }

        $sign_setting_service = D('Sign/SignSetting', 'Service');
        $result = $sign_setting_service->get_config($type);

        // 返回数据
        $this->_result = array(
            'config' => $result
        );

    }

    /**
     * 更新考勤微信菜单
     */
    public function update_wxcpmenu_post(){
        $params = I('post.');
        if(empty($params['wxcpmenu'])){
            E('_ERROR_PARAM_VAL_NULL');
            return false;
        }
        if(count($params['wxcpmenu']) != 3){
            E('_ERROR_PARAM_VAL_NULL');
            return false;
        }

        $sign_setting_service = D('Sign/SignSetting', 'Service');
        $sign_setting_service->update_wxcpmenu($params['wxcpmenu']);
    }

    /**
     * 更新签到/签退 通用接口
     */
    public function update_sign_post(){
        $params = I('post.');
        $type = $params['type'];//1-签到；2-签退
        if(empty($type)){
            E('_ERROR_PARAM_VAL_NULL');
            return false;
        }

        if($type == SignSettingModel::CONFIG_TYPE_SIGN){//签到
            $sign_keys = array(
                'sign_start_range',
                'sign_come_late_range',
                'sign_remind_on_rage',
                'sign_remind_on'
            );

			foreach($sign_keys as $v){
				if(empty($params[$v]) && $params[$v] !=0){
					E('_ERROR_PARAM_VAL_NULL');
					return false;
				}
			}

            //签到时间不能大于签到时间范围
            if($params['sign_remind_on_rage'] > $params['sign_start_range']){
                E('_ERR_SIGN_REMIND_THAN_START_RANGE');
                return false;
            }

			//签到时间点前XX分钟提醒
			if($params['sign_remind_on_rage'] > 60 || $params['sign_remind_on_rage'] < 1 ){
				E('_ERR_SIGN_REMIND_ON_RANGE_FAILD');
				return false;
			}

			//迟到规则
			if($params['sign_come_late_range'] > 120 || $params['sign_come_late_range'] < 1 ){
				E('_ERR_SIGN_COME_LATE_RANGE_FAILD');
				return false;
			}

            //签到消息提醒内容不能超过20个字符
            if(rstrlen($params['sign_remind_on']) > self::SIGN_MSG_MAXLENTH){
                E('_ERR_SIGN_MSG_MAXLENTH_FAILD');
                return false;
            }
            //签到时间范围:1-720之间
            if($params['sign_start_range'] <1 || $params['sign_start_range'] >720){
                E('_ERR_SIGN_START_RANGE_FAILD');
                return false;
            }

        }elseif ($type == SignSettingModel::CONFIG_TYPE_SIGN_OUT){//签退
            $sign_keys = array(
                'sign_end_rage',
                'sign_leave_early_range',
                'sign_late_range',
                'sign_remind_off_rage',
                'sign_remind_off'
            );
            foreach($sign_keys as $v){
                if(empty($params[$v]) && $params[$v] !=0){
                    E('_ERROR_PARAM_VAL_NULL');
                    return false;
                }
            }

            //签到消息提醒内容不能超过20个字符
            if(rstrlen($params['sign_remind_off']) > self::SIGN_MSG_MAXLENTH){
                E('_ERR_SIGN_MSG_MAXLENTH_FAILD');
                return false;
            }
            //签退时间范围:1-720之间
            if($params['sign_end_rage'] <1 || $params['sign_end_rage'] >720){
                E('_ERR_SIGN_END_RANGE_FAILD');
                return false;
            }
			//早退规则
			if($params['sign_leave_early_range'] <1 || $params['sign_leave_early_range'] >120){
				E('_ERR_SIGN_LEAVE_EARLY_RANGE_FAILD');
				return false;
			}

			//加班规则
			if($params['sign_late_range'] <1 || $params['sign_late_range'] >120){
				E('_ERR_SIGN_LATE_RANGE_RULE_FAILD');
				return false;
			}

			//签退时间点后XX分钟提醒
			if($params['sign_remind_off_rage'] <1 || $params['sign_remind_off_rage'] >60){
				E('_ERR_SIGN_REMIND_OFF_RANGE_RULE_FAILD');
				return false;
			}

            //加班规则不能大于签退时间范围
            if($params['sign_late_range'] > $params['sign_end_rage']){
                E('_ERR_SIGN_LATE_RANGE_FAILD');
                return false;
            }
        }

        $sign_setting_service = D('Sign/SignSetting', 'Service');
        unset($params['type']);
        $sign_setting_service->update_setting($params);
    }

    /**
     * 更新考勤开关设置 通用接口
     */
    public function update_swith_post(){
        $params = I('post.');
        $type = $params['type'];//修改类型：1-外出考勤必须上传图片；2-休息日允许考勤
        if(empty($type)){
            E('_ERROR_PARAM_VAL_NULL');
            return false;
        }

        if($type == SignSettingModel::OUT_UPLOAD_IMG){
            if(empty($params['out_sign_upload_img'])){
                E('_ERROR_PARAM_VAL_NULL');
                return false;
            }
        }elseif ($type == SignSettingModel::REST_DAY_SIGN){
            if(empty($params['rest_day_sign'])){
                E('_ERROR_PARAM_VAL_NULL');
                return false;
            }
        }else{
            E('_ERROR_PARAM_VAL_NULL');
            return false;
        }

        $sign_setting_service = D('Sign/SignSetting', 'Service');
        unset($params['type']);
        $sign_setting_service->update_setting($params);
    }

    /**
     * 获取默认全局考勤规则数据接口
     */
    public function defult_rule_get(){
        $cache = &Cache::instance();
        $cache_setting = $cache->get('Sign.setting');

        $result = array(
            'sign_start_range' => (int)$cache_setting['sign_start_range'],
            'sign_come_late_range' => (int)$cache_setting['sign_come_late_range'],
            'sign_remind_on_rage' => (int)$cache_setting['sign_remind_on_rage'],
            'sign_remind_on' => $cache_setting['sign_remind_on'],
            'sign_end_rage' => (int)$cache_setting['sign_end_rage'],
            'sign_leave_early_range' => (int)$cache_setting['sign_leave_early_range'],
            'sign_late_range' => (int)$cache_setting['sign_late_range'],
            'sign_remind_off_rage' => (int)$cache_setting['sign_remind_off_rage'],
            'sign_remind_off' => $cache_setting['sign_remind_off']
        );
        // 返回数据
        $this->_result = array(
            'defult_rule' => $result
        );
    }
}