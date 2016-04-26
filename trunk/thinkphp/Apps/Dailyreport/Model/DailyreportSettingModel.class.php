<?php

/**
 * GuestbookSettingModel.class.php
 * $author$
 */

namespace Dailyreport\Model;

class DailyreportSettingModel extends \Common\Model\AbstractSettingModel {

    private $menu_names = array(
        'menu_1',
        'menu_2',
        'menu_2_1',
        'menu_2_2',
        'menu_3',
        'menu_3_1',
        'menu_3_2'
    );

    // 构造方法
    public function __construct() {
        parent::__construct();
        $this->prefield = 'drs_';
    }

    private function _handle_edit_post(&$post, &$menu, &$c_menu) {
        
        if (count($post) != 7) {
            E('_ERR_DAILYREPORT_WECHAT_MENU_NAME_ERR');
        }
        foreach ($post as $k => $v) {
            $str_len = mb_strlen($v, 'utf8');
            if ($str_len <= 0 || $str_len >= 6 || !in_array($k, $this->menu_names)) {
                E('_ERR_DAILYREPORT_WECHAT_MENU_NAME_ERR');
                break;
            }
        }
        foreach ($menu as $ks => $vs) {
            if (!isset($vs['sub_button'])) {
                $vs['name'] = $post[$vs['form_name']];
                $c_menu[$ks] = $vs;
                unset($c_menu[$ks]['form_name']);
                $c_menu[$ks]['url']='http://'.$_SERVER['HTTP_HOST'].$c_menu[$ks]['url'];
            } else {
                $c_menu[$ks] = $vs;
                unset($c_menu[$ks]['form_name']);
                $vs['name'] = $post[$vs['form_name']];
                $c_menu[$ks]['name']=$vs['name'];
                foreach ($vs['sub_button'] as $ck => $cv) {
                    $cv['name'] = $post[$cv['form_name']];
                    $vs['sub_button'][$ck] = $cv;
                    unset($cv['form_name']);
                    $c_menu[$ks]['sub_button'][$ck] = $cv;
                    $c_menu[$ks]['sub_button'][$ck]['url']='http://'.$_SERVER['HTTP_HOST'].$c_menu[$ks]['sub_button'][$ck]['url'];
                }
            }
            $menu[$ks] = $vs;
        }
        return true;
    }

    public function save_wechat_menu($post, $menu, $pluginid, $agentid) {
        $c_menu = array();
        $this->_handle_edit_post($post, $menu, $c_menu); //处理数据
        $this->_create_wechat_menu($c_menu, $pluginid, $agentid); //创建微信菜单
        $this->_save_wechat_menu($menu); //保存菜单到数据库
        return true;
    }

    public function reset_wechat_menu($menu, $pluginid, $agentid) {
        $c_menu = array();
        $r_menu = array();
        foreach ($menu as $ks => $vs) {
            $r_menu[$ks]['form_name']=$vs['form_name'];
            $r_menu[$ks]['name']=$vs['name'];
            unset($vs['form_name']);
            if (isset($vs['sub_button'])) {
                foreach ($vs['sub_button'] as $ck => $cv) {
                    $r_menu[$ks]['sub_button'][$ck]['form_name']=$cv['form_name'];
                    $r_menu[$ks]['sub_button'][$ck]['name']=$cv['name'];
                    unset($cv['form_name']);
                    $vs['sub_button'][$ck] = $cv;
                    $vs['sub_button'][$ck]['url']='http://'.$_SERVER['HTTP_HOST'].$vs['sub_button'][$ck]['url'];
                }
            }else{
                $vs['url']='http://'.$_SERVER['HTTP_HOST'].$vs['url'];
            }
            $c_menu[$ks] = $vs;
        }
        $this->_create_wechat_menu($c_menu, $pluginid, $agentid); //创建微信菜单
        $this->_save_wechat_menu($menu); //保存菜单到数据库
        return $r_menu;
    }

    private function _create_wechat_menu($c_menu, $pluginid, $agentid) {
        $oWxqy = new \Common\Common\Wxqy\Service();
        $oWxqy->create_menu($c_menu, intval($agentid), intval($pluginid));
        return true;
    }
    private function _save_wechat_menu($menu) {
        $conds = array('drs_key' => array('eq', 'wechat_menu_new'));
        $data['drs_value'] = json_encode($menu);
        $this->update_by_conds($conds, $data);
    }
    public function get_wechat_menu(){
        $menu = $this->_m->field('drs_value')->where(array('drs_key'=>array('eq','wechat_menu_new')))->find();
        $menu=array_map(function($v){
            if(isset($v['sub_button'])){
                foreach($v['sub_button'] as $k=>$vs){
                    unset($v['sub_button'][$k]['type'],$v['sub_button'][$k]['url']);
                }
            }else{
                unset($v['type'],$v['url']);
            }
            return $v;
        },  json_decode($menu['drs_value'],true));
        return $menu;
    }
}
