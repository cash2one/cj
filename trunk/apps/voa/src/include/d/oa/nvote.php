<?php
/**
 * voa_d_oa_nvote
 * 投票调研
 * User: luckwang
 * Date: 15/3/9
 * Time: 上午10:31
 */

class voa_d_oa_nvote extends voa_d_abstruct {

    //单选
    const SINGLE_YES = 1;
    //多选
    const SINGLE_NO = 2;

    //非匿名
    const SHOW_NAME_YES = 1;
    //匿名
    const SHOW_NAME_NO = 2;

    //显示结果
    const SHOW_RESULT_YES = 2;
    //不显示结果
    const SHOW_RESULT_NO = 1;

    //未关闭投票
    const CLOSE_STATUS_NO = 1;
    //关闭的投票
    const CLOSE_STATUS_YES = 2;

    //允许重复投票
    const REPEAT_YES = 1;
    //不允许重复投票
    const REPEAT_NO = 2;

    // 初始化
    public function __construct() {

        // 表名
        $this->_table = 'orm_oa.nvote';
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
