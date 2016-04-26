<?php

/**
 * 报告阅读状态
 * Create By liyongjian
 */
class voa_d_oa_dailyreport_read extends voa_d_abstruct {

    public static $table = 'oa.dailyreport_read';
    public static $pk = 'rid';

    public function  __construct($cfg = null) {

        // 表名
        $this->_table = 'orm_oa.dailyreport_read';

        /** 允许的字段 */
        $this->_allowed_fields = array();
        /** 必须的字段 */
        $this->_required_fields = array();

        // 主键
        $this->_pk = 'rid';

        parent::__construct(null);


    }
}
