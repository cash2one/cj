<?php
/**
 * User: keller
 * Date: 16/3/16
 * Time: 下午11:20
 */

namespace Dailyreport\Service;


class DailyreportxService extends AbstractService
{
    protected $drfx_model;

    // 构造方法
    public function __construct() {
        parent::__construct();
        $this->drfx_model = D('DailyreportDraftx');
    }
    public function add_draftx($post,$m_uid){
        if($this->drfx_model->add_draftx($post,$m_uid)){
            return true;
        }
        return false;
    }
    public function edit_draftx($post){
        if($this->drfx_model->edit_draftx($post)){
            return true;
        }
        return false;
    }
    public function get_draftx_list($m_uid,$page,$q,$k,$drt_id){
        if($list = $this->drfx_model->get_draftx_list($m_uid,$page,$q,$k,$drt_id)){
            return $list;
        }
        return false;
    }
    public function get_api_draftx($drd_id){
        if($drd = $this->drfx_model->get_api_draftx($drd_id)){
            return $drd;
        }
        return false;
    }
    public function del_api_draftx($drd_id,$m_uid){
        if($this->drfx_model->del_api_draftx($drd_id,$m_uid)){
            return true;
        }
        return false;
    }
    
}