<?php
/**
 * voa_uda_frontend_cnvote_get
 * 投票调研-uda投票列表
 * User: luckwang
 * Date: 15/3/9
 * Time: 上午11:18
 */

class voa_uda_frontend_cnvote_get extends voa_uda_frontend_cnvote_abstract {

    public function __construct() {
        parent::__construct();
    }


    /**
     * 获取投票信息(投票选项)
     * @param $nvote_id 投票id
     * @param $nvote 投票信息(输出) &
     * @return bool
     */
    public function get_vote($nvote_id, &$nvote) {
        $nvote_id = rintval($nvote_id);
        if ($nvote_id < 1) {
            return false;
        }

        $nvote = $this->_serv->get($nvote_id);
        if (empty($nvote)) {
            return false;
        }
        $nvote['at_id'] = 0;
        //获取附件
        $serv_attach = &service::factory('voa_s_oa_cnvote_attachment');

        $nvote['at_id'] = $serv_attach->at_id_by_novte_opton_id($nvote['id']);

        $nvote['attachment'] = !empty($nvote['at_id']) ? voa_h_attach::attachment_url($nvote['at_id']) : '';

        $nvote['options'] = $this->__options_by_nvote_id($nvote_id);

        return true;
    }

    /**
     * 获取投票选项根据投票id
     * @param int $nvote_id
     * @return array
     */
    private function __options_by_nvote_id($nvote_id) {

        $serv_option = &service::factory('voa_s_oa_cnvote_option');
        $options = $serv_option->list_by_conds(array('nvote_id = ?' => $nvote_id));

        $serv_attach = &service::factory('voa_s_oa_cnvote_attachment');

        //附件信息
        foreach ($options as &$option) {

            $option['at_id'] = $serv_attach->at_id_by_novte_opton_id($nvote_id, $option['id']);

            $option['attachment'] = !empty($option['at_id']) ? voa_h_attach::attachment_url($option['at_id']) : '';
        }

        return $options;
    }

}
