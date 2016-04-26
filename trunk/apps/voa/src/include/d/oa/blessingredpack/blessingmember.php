<?php
/**
 * voa_d_oa_blessingredpack_blessingmember.php
 * 红包活动用户表
 * @author: anything
 * @createTime: 2015/11/26 17:57
 * @version: $Id$ 
 * @copyright: 畅移信息
 */

class voa_d_oa_blessingredpack_blessingmember extends voa_d_abstruct {

    /** 初始化 */
    public function __construct($cfg = null) {

        /** 表名 */
        $this->_table = 'orm_oa.blessing_redpack_member';
        /** 允许的字段 */
        $this->_allowed_fields = array();
        /** 必须的字段 */
        $this->_required_fields = array();
        /** 主键 */
        $this->_pk = 'id';

        parent::__construct(null);
    }

}
