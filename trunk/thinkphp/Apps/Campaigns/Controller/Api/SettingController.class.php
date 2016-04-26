<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/4/12
 * Time: 19:06
 */

namespace Campaigns\Controller\Api;

class SettingController extends AbstractController {
    /**
     * 获取分类
     */
    public function getList_get(){
        $st_sr=D('Setting','Service');
        $this->_result = $st_sr->get_list();
        return true;
    }
}