<?php
/**
 * voa_d_oa_nvote_attachment
 * 投票调研-附件
 * User: luckwang
 * Date: 15/3/9
 * Time: 下午4:33
 */

class voa_d_oa_nvote_attachment extends voa_d_abstruct {

    // 初始化
    public function __construct() {

        // 表名
        $this->_table = 'orm_oa.nvote_attachment';
        // 允许的字段
        $this->_allowed_fields = array();
        // 必须的字段
        $this->_required_fields = array();
        // 主键
        $this->_pk = 'id';
        // 字段前缀
        $this->_prefield = '';

        parent::__construct();
    }
}
