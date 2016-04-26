<?php
/**
 * outlist.php
 * 外部参与人员填写列表
 * Created by zhoutao.
 * Created Time: 2015/5/8  9:52
 */

class voa_d_oa_activity_outlist extends voa_d_abstruct {

    /** 初始化 */
    public function __construct($cfg = null) {

        /** 表名 */
        $this->_table = 'orm_oa.activity_outlist';
        /** 允许的字段 */
        $this->_allowed_fields = array();
        /** 必须的字段 */
        $this->_required_fields = array();
        /** 主键 */
        $this->_pk = 'outpid';

        parent::__construct(null);
    }

}
