<?php
/**
 * Created by PhpStorm.
 * User: gaoyaqiu
 * Date: 15/11/24
 * Time: 21:54
 * 获取缓存配置相关数据
 */
namespace BlessingRedpack\Controller\Api;
use Common\Common\Cache;

class SettingController extends AbstractController{
    /**
     * 获取企业二维码
     * @return bool
     */
    public function Qrcode(){
        $cache = &Cache::instance();
        $cache_setting = $cache->get('Common.setting');
        if(!empty($cache_setting)){
            $this->_result = array(
                "qrcode" => $cache_setting['qrcode']
            );
        }
        return true;
    }

}

