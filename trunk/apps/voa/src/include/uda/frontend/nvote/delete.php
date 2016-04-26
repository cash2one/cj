<?php
/**
 * voa_uda_frontend_nvote_delete
 * 投票调研-uda删除投票
 * User: luckwang
 * Date: 15/3/9
 * Time: 上午11:30
 */


class voa_uda_frontend_nvote_delete extends voa_uda_frontend_nvote_abstract {

    public function __construct() {
        parent::__construct();
    }

    /**
     * 删除投票
     * @param $vote_ids array(投票id1, 投票id2, 投票id3)
     * @return bool
     */
    public function delete($vote_ids) {


        $serv_attachment = &service::factory('voa_s_oa_nvote_attachment');
        $serv_mem = &service::factory('voa_s_oa_nvote_mem');
        $serv_option = &service::factory('voa_s_oa_nvote_option');
        $serv_mem_option = &service::factory('voa_s_oa_nvote_mem_option');

        // @ 待删除的公共附件表id
        $at_ids = array();

        try {

            // 开始删除过程
            $this->_serv->begin();

            // @ 删除主表记录
            $this->_serv->delete_by_conds(array('id IN (?)' => $vote_ids));

            //删除投票用户
            $serv_mem->delete_by_conds(array('nvote_id IN (?)' => $vote_ids));

            //删除投票选项
            $serv_option->delete_by_conds(array('nvote_id IN (?)' => $vote_ids));

            //删除投票用户选项
            $serv_mem_option->delete_by_conds(array('nvote_id IN (?)' => $vote_ids));

            // 找到投票附件对应公共附件的id
            $attach_list = $serv_attachment->get_by_conds(array('nvote_id IN (?)' => $vote_ids));
            // 存在附件
            if ($attach_list) {
                // @ 删除投票附件表
                $serv_attachment->delete_by_conds(array('nvote_id' => array($vote_ids)));

                // 找到公共附件表at_id
                foreach ($attach_list as $_data) {
                    $at_ids[] = $_data['at_id'];
                }
                unset($attach_list);
            }

            // 提交删除过程
            $this->_serv->commit();

        } catch (Exception $e) {
            logger::error(print_r($e, true));
            $this->_serv->rollback();
            $this->errmsg(100, '操作失败');
            return false;
        }

        if ($at_ids) {
            // 删除公共附件表
            $uda_attachment_delete = &uda::factory('voa_uda_frontend_attachment_delete');
            $uda_attachment_delete->delete($at_ids);
        }

        return true;
    }

}
