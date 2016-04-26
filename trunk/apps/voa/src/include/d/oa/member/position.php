<?php
/**
 * voa_d_oa_member_position.
 * 用户职务
 * User: luckwang
 * Date: 15/5/9
 * Time: 上午10:16
 */

class voa_d_oa_member_position extends voa_d_abstruct {

    // 初始化
    public function __construct() {

        // 表名
        $this->_table = 'orm_oa.member_position';
        // 允许的字段
        $this->_allowed_fields = array();
        // 必须的字段
        $this->_required_fields = array();
        // 主键
        $this->_pk = 'mp_id';
        // 字段前缀
        $this->_prefield = 'mp_';

        parent::__construct();
    }
}
