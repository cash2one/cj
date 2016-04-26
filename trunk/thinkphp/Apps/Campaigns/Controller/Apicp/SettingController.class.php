<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/4/12
 * Time: 19:06
 */

namespace Campaigns\Controller\Apicp;

class SettingController extends AbstractController {
    /**
     * 保存分类设置接口
     */
    public function save_post(){
        $st_sr=D('Setting','Service');
        $st_sr->save_type(I('post.',array()));
        return true;
    }
    /**
     * 编辑时获取分类设置
     */
    public function getList_get(){
        $st_sr=D('Setting','Service');
        $this->_result = $st_sr->get_list();
        return true;
    }
}