<?php

/**
 * DailyreportService.class.php
 * $author$
 */

namespace Dailyreport\Service;

class DailyreportTplService extends AbstractService {

    protected $drt_model;
    // 构造方法
    public function __construct() {
        parent::__construct();
        $this->drt_model = D('DailyreportTpl');
    }
    /**
     * 添加模板
     * @param type $post
     * @return boolean
     */
    public function add_tpl($post){
        if($this->drt_model->add_tpl($post)){
            return false;
        }
        return true;
    }
    /**
     * 保存模板
     * @param type $post
     * @return boolean
     */
    public function save_tpl($post){
        if($this->drt_model->edit_tpl($post)){
            return false;
        }
        return true;
    }
    /**
     * 获取模板列表
     */
    public function get_list($post){
        if($tpls=$this->drt_model->get_list($post)){
            return $tpls;
        }
        return false;
    }
    /**
     * 禁用启用木板
     * @param int $drt_id 操作模板的id
     * @param int $drt_switch 状态值
     * @return boolean 执行状态
     */
    public function switch_tpl($drt_id,$drt_switch){
        if($this->drt_model->switch_tpl($drt_id,$drt_switch)){
            return true;
        }
        return false;
    }
    /**
     * 禁用启用木板
     * @param int $drt_id 操作模板的id
     * @return boolean 执行状态
     */
    public function del_tpl($drt_id){
        if($this->drt_model->del_tpl($drt_id)){
            return true;
        }
        return false;
    }
    /**
     * 获得一个tpl模板对象
     * @param int $drt_id
     * @return 
     */
    public function get_tpl($drt_id){
        if($tpl = $this->drt_model->get_tpl($drt_id)){
            return $tpl;
        }
        return false;
    }

    /**
     * 获取汇报类型
     * @author keller<likai@vchangyi.com>
     * @route
     * @method
     * @return bool
     */
    public function get_type(){
        if($types = $this->drt_model->get_type()){
            return $types;
        }
        return false;
    }
    /**
     * 获取汇报类型
     * @author keller<likai@vchangyi.com>
     * @route
     * @method
     * @return bool
     */
    public function get_typecp(){
        if($types = $this->drt_model->get_typecp()){
            return $types;
        }
        return false;
    }
    public function get_api_tpl_list($m_uid,$type_id,$target_id){
        if($tpls = $this->drt_model->get_api_tpl_list($m_uid,$type_id,$target_id)){
            return $tpls;
        }
        return false;
    }
    public function get_api_tpl_info($drt_id,$m_username){
        if($tpl = $this->drt_model->get_api_tpl_info($drt_id,$m_username)){
            return $tpl;
        }
        return false;
    }
    public function get_api_in_departments($drt_id,$cd_id){
        if($dps = $this->drt_model->get_api_in_departments($drt_id,$cd_id)){
            return $dps;
        }
    }
    public function get_tpl_sort(){
        if($sort = $this->drt_model->get_tpl_sort()){
            return $sort;
        }
    }
     public function get_api_news_dailyreport_tpls($m_uid){
        if($tpls = $this->drt_model->get_api_news_dailyreport_tpls($m_uid)){
            return $tpls;
        }
        return false;
    }

}
