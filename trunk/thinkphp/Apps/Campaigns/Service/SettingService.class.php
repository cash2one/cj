<?php

/**
 * User: keller
 * Date: 16/3/16
 * Time: 下午11:20
 */

namespace Campaigns\Service;
class SettingService extends AbstractService {
    
    protected $ct_model;
    public function __construct() {
        parent::__construct();
        $this->ct_model = D('CampaignsType');
    }
    public function save_type($post){
        $this->ct_model->save_type($post);
        return true;
    }
    public function get_list(){
        return $this->ct_model->get_list();
    }
}
