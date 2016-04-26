<?php
namespace Campaigns\Service;
class CamService extends AbstractService{
    
    protected $cam_model;
    public function __construct() {
        parent::__construct();
        $this->cam_model = D('Campaigns');
    }
    public function add_cam($post,$m_uid,$m_username){
        $this->cam_model->add_cam($post,$m_uid,$m_username);
        return true;
    }
    public function save_cam($post){
        $this->cam_model->save_cam($post);
        return true;
    }
    public function list_cam($get){
        return $this->cam_model->list_cam($get);
    }
    public function detail_cam($id){
        return $this->cam_model->detail_cam($id);
    }
    public function dels_cam($ids){
        $this->cam_model->dels_cam($ids);
    }
    public function get_edit_detail($id){
        return $this->cam_model->get_edit_detail($id);
    }
    public function get_list_api($get,$m_uid){
        return $this->cam_model->get_list_api($get,$m_uid);
    }
    public function get_detail_api($id,$uid){
        return $this->cam_model->get_detail_api($id,$uid);
    }
    public function share_cam_api($id,$uid){
        return $this->cam_model->share_cam_api($id,$uid);
    }
    public function my_share_api($uid,$page,$typeid){
        return $this->cam_model->my_share_api($uid,$page,$typeid);
    }
    public function data_center($page){
        return $this->cam_model->data_center($page);
    }
}
