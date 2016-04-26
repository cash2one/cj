<?php
/**
 * voa_d_oa_campaign_orders
 * 活动推广 接单详情
 * User: Mu zhitao
 * Date: 2015/8/26 0026
 * Time: 15:19
 */

class voa_d_oa_campaign_orders extends voa_d_abstruct {

    /** 初始化 */
    public function __construct($cfg = null) {

        /** 表名 */
        $this->_table = 'orm_oa.campaign_orders';
        /** 允许的字段 */
        $this->_allowed_fields = array();
        /** 必须的字段 */
        $this->_required_fields = array();
        /** 主键 */
        $this->_pk = 'o_id';
        parent::__construct(null);
    }
}

// end