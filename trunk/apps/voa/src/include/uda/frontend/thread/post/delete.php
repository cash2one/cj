<?php
/**
 * voa_uda_frontend_thread_post_delete
 * 统一数据访问/社区应用/删除评论
 *
 * $Author$
 * $Id$
 */

class voa_uda_frontend_thread_post_delete extends voa_uda_frontend_thread_post_abstract {

    public function __construct() {

        parent::__construct();
    }

    /**
     * 输入参数
     * @param array $in 输入参数
     * @param array &$out 输出参数
     * @return boolean
     */
    public function execute($in, &$out) {

        $this->_params = $in;
        // 查询表格的条件
        $fields = array(
            array('pid', self::VAR_ARR, null, null, false),
        	array('tid', self::VAR_INT, null, null, true)
        );
        $data = array();
        if (!$this->extract_field($data, $fields)) {
            return false;
        }

        // 删除主题的评论信息
        $this->_serv->delete($data['pid']);

        //评论数减一
        $serv = &service::factory('voa_d_oa_thread');
        $serv->update_by_conds($data['tid'], array('`replies`=`replies`-?' => count($data['pid'])));

        return true;
    }



}
