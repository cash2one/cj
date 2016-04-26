<?php
/**
 * GuestbookSettingService.class.php
 * $author$
 */

namespace Sign\Service;

use Common\Service\AbstractSettingService;
use Common\Common\Cache;
use Sign\Model\SignScheduleModel;
use Sign\Model\SignSettingModel;

class SignSettingService extends AbstractSettingService {

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->_d = D('Sign/SignSetting');
	}

	/**
	 * 获取设置表里的所有数据
	 *
	 * @return mixed
	 */
	public function list_all() {

		$list = array();
		foreach ($this->_d->list_all() as $k => $v) {
			$list[$v['ss_key']] = $v['ss_value'];
		}

		return $list;
	}


    /**
     * 更新考勤配置
     * @param $params
     * @return bool
     */
    public function update_setting($params){

        // 循环更新
        foreach ($params as $_k => $_v) {
            $ups[$_k] = $_v;
            $this->update_kv($ups);
        }

        //reload cache
        clear_cache();

        return true;
    }

    public function update_wxcpmenu($menu_array){

		$cache = &Cache::instance();
		$cache_setting = $cache->get('Sign.setting');

		$pluginid = $cache_setting['pluginid'];
		$agentid = $cache_setting['agentid'];

        if(empty($pluginid) || empty($agentid)){
			Log::record('pluginid || agentid is null');
            E('_ERR_SIGN_SYSTEM_BUSY');
            return false;
        }

        //调用企业微信创建菜单接口
        $serv = &\Common\Common\Wxqy\Service::instance();
        $wxqy_menu_serv = new \Common\Common\Wxqy\Menu($serv);

        try{
            $result = $wxqy_menu_serv->create($menu_array, $agentid, $pluginid);
            if(!$result){
                E('_ERR_SIGN_SYSTEM_BUSY');
                return false;
            }
        }catch (\Exception $e){
            \Think\Log::record('调用企业微信创建菜单接口异常：');
            \Think\Log::record($e->getMessage());
            E('_ERR_SIGN_SYSTEM_BUSY');
            return false;
        }



        //更新sing_setting表
        $ups = array(
            'wxcpmenu' => serialize($menu_array)
        );
        $this->update_kv($ups);

        //同步更新cpmenu表外出考勤，公司考勤的名称
        $commonCpmenu_model = D("Common/CommonCpmenu");

        $conds['ccm_operation'] = 'sign';
        $conds['ccm_subop'] = 'list';
        $tmp_obj = $menu_array[0];
        $data['ccm_name'] = $tmp_obj['name'];
        $commonCpmenu_model->update_by_conds($conds, $data);

        $conds['ccm_subop'] = 'upposition';
        $tmp_obj = $menu_array[1];
        $data['ccm_name'] = $tmp_obj['name'];
        $commonCpmenu_model->update_by_conds($conds, $data);

        //更新缓存
        clear_cache();

        return true;

    }


    /**
     * 获取设置数据
     * @param $type 1-签到设置; 2-签退设置; 3-微信菜单设置
     * @return array|bool
     */
    public  function get_config($type){

        $cache = &Cache::instance();
        $cache_setting = $cache->get('Sign.setting');

        $result = array(
            'type' => $type,
        );

        if($type == SignSettingModel::CONFIG_TYPE_SIGN){//签到设置
            //签到时间范围
            $result['sign_start_range'] = (int)$cache_setting['sign_start_range'];
            //迟到规则
            $result['sign_come_late_range'] = (int)$cache_setting['sign_come_late_range'];
            //签到时间点前XX分钟提醒
            $result['sign_remind_on_rage'] = (int)$cache_setting['sign_remind_on_rage'];
            //提醒内容
            $result['sign_remind_on'] = $cache_setting['sign_remind_on'];

        }elseif ($type == SignSettingModel::CONFIG_TYPE_SIGN_OUT){//签退设置
            //签退时间范围
            $result['sign_end_rage'] = (int)$cache_setting['sign_end_rage'];
            //早退规则
            $result['sign_leave_early_range'] = (int)$cache_setting['sign_leave_early_range'];
            //加班规则
            $result['sign_late_range'] = (int)$cache_setting['sign_late_range'];
            //签退时间点后XX分钟提醒
            $result['sign_remind_off_rage'] = (int)$cache_setting['sign_remind_off_rage'];
            //提醒内容
            $result['sign_remind_off'] = $cache_setting['sign_remind_off'];

        }elseif ($type == SignSettingModel::CONFIG_TYPE_WXCPMENU){//微信菜单设置
            $wxcpmenu_bool = false;
            if(!empty($cache_setting['wxcpmenu'])){
                $wxcpmenu_bool = true;
            }
            //如果sign_setting 没有菜单配置数据，取配置文件里的默认配置
            $result['wxcpmenu'] = $wxcpmenu_bool ? $cache_setting['wxcpmenu'] : cfg('MENU_QYWX');

        }elseif ($type == SignSettingModel::CONFIG_TYPE_SWITCH){//开关数据
            //外出考勤是否必须上传图片 1-关闭；2-开起
            $result['out_sign_upload_img'] = $cache_setting['out_sign_upload_img'];
            //休息日是否允许考勤  1-关闭；2-开起
            $result['rest_day_sign'] = $cache_setting['rest_day_sign'];
        }else{
            E('_ERROR_PARAM_VAL_NULL');
            return false;
        }

        return $result;
    }

    /**
     * 查询外出考勤是否必须上传图片
     * @return int
     */
    public function get_upload_img_flag(){
        $cache = &Cache::instance();
        $cache_setting = $cache->get('Sign.setting');

        if(empty($cache_setting['out_sign_upload_img'])){
            return SignSettingModel::OUT_SIGN_UPLOAD_IMG;
        }

        return intval($cache_setting['out_sign_upload_img']);
    }
}
