<?php

/**
 * Created by PhpStorm.
 * User: lixue
 * Date: 15/12/18
 * Time: 下午2:03
 */

namespace Dailyreport\Controller\Apicp;

class DailyreportSettingController extends AbstractController {

    protected $_require_login = true;

    /**
     * 获取模板的分页列表
     * @return boolean
     */
    public function GetWechatMenu_get() {
        $serv_drs = D('DailyreportSetting', 'Service');
        $menu=$serv_drs->get_wechat_menu();
        $this->_result = $menu;
        return true;
    }

    /**
     * 保存菜单
     * @return boolean
     */
    public function SaveWechatMenu_post() {
        $post = I('post.');
        $serv_drs = D('DailyreportSetting', 'Service');
        $menus=json_decode($this->_plugin->setting['wechat_menu_new'],true);
        $pluginid = $this->_plugin->info['cp_pluginid'];
        $agentid=$this->_plugin->info['cp_agentid'];
        if ($serv_drs->save_wechat_menu($post,$menus,$pluginid,$agentid)) {
            return true;
        };
        return false;
    }
    /**
     * 一键还原菜单
     */
    public function ResetWechatMenu_post(){
        $serv_drs = D('DailyreportSetting', 'Service');
        $menu_old=json_decode($this->_plugin->setting['wechat_menu_old'],true);
        if ($r_menu = $serv_drs->reset_wechat_menu($menu_old,$this->_plugin->info['cp_pluginid'],$this->_plugin->info['cp_agentid'])) {
            $this->_result=$r_menu;
            return true;
        };
        return false;
    }

}
