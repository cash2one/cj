<?php
/**
 * BlessingRedpackSettingCpController.class.php
 * 红包配置
 * @author: anything
 * @createTime: 2015/11/23 22:28
 * @version: $Id$ 
 * @copyright: 畅移信息
 */
namespace BlessingRedpack\Controller\Apicp;
use Common\Common\Cache;

class BlessingRedpackSettingCpController extends AbstractController {

    /**
     * 获取设置表数据
     */
    public function setting_get(){
        $cache = &Cache::instance();
        $cache_setting = $cache->get('BlessingRedpack.setting');
        //$cache_setting['wxpay_certificate1'] = unserialize($cache_setting['wxpay_certificate1']);
        //$cache_setting['wxpay_certificate2'] = unserialize($cache_setting['wxpay_certificate2']);
        //$cache_setting['wxpay_certificate3'] = unserialize($cache_setting['wxpay_certificate3']);
        $cache_setting['redpack_min'] =  number_format($cache_setting['redpack_min'] / 100, 2);
        $cache_setting['redpack_max'] =  number_format($cache_setting['redpack_max'] / 100, 2);

        // 获取部门名称
        $cache_departments = $cache->get('Common.department');
        foreach ($cache_departments as $k => $v) {
            if($k == $cache_setting['invite_department']){
                $cache_setting['dempartmentName'] = $v['cd_name'];
            }
        }
        // 返回数据
        $this->_result = array(
            'setting' => $cache_setting
        );



    }

    /**
     * 更新红包配置
     */
    public function update_setting_post(){

        $params = I('post.');

//        if(empty($params['wxpay_certificate1'])){
//            E('_ERROR_PARAM_VAL_NULL');
//            return false;
//        }
//        if(empty($params['wxpay_certificate2'])){
//            E('_ERROR_PARAM_VAL_NULL');
//            return false;
//        }
//        if(empty($params['invite_department'])){
//            E('_ERROR_PARAM_VAL_NULL');
//            return false;
//        }
//
//        if(empty($params['wxpay_certificate3'])){
//            E('_ERROR_PARAM_VAL_NULL');
//            return false;
//        }

        $redpack_min = $params['redpack_min'] * 100;
        $redpack_max = $params['redpack_max'] * 100;

        //如果为空，默认1元
        if(empty($redpack_min)){
            $redpack_min = 100;
        }else{
            //红包最小金额不能小于1元
            if($redpack_min < 100){
                E('_ERR_BLESSING_REDPACK_MIN_ERROR');
                return false;
            }else if($redpack_min > 2000000){
                E('_ERR_BLESSING_REDPACK_MAX_ERROR');
                return false;
            }
        }

        //如果为空，默认200元
        if(empty($redpack_max)){
            $redpack_max = 20000;
        }else{
            //最大不能超过20000元，微信支付限制最大两万
            if($redpack_max > 2000000){
                E('_ERR_BLESSING_REDPACK_MAX_ERROR');
                return false;
            }
        }

        $params['redpack_min'] = $redpack_min;
        $params['redpack_max'] = $redpack_max;

        $blessing_redpack_setting_service = D('BlessingRedpack/BlessingRedpackSetting', 'Service');

        $blessing_redpack_setting_service->update_setting($params);

    }
}
