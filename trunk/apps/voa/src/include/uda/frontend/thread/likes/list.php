<?php
/**
 * voa_uda_frontend_thread_likes_list
 * 统一数据访问/社区应用/获取点赞列表
 *
 * $Author$
 * $Id$
 */
class voa_uda_frontend_thread_likes_list extends voa_uda_frontend_thread_likes_abstract{

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
            array('tid', self::VAR_ARR, null, null, true),
            array('uid', self::VAR_INT, null, null, true),
            array('page', self::VAR_INT, null, null, true),
            array('perpage', self::VAR_INT, null, null, true)
        );
        $conds = array();
        if (!$this->extract_field($conds, $fields)) {
            return false;
        }
        // 分页信息
        $option = array();
        $this->_get_page_option($option, $conds);
        $this->_total = $this->_serv->count_by_conds($conds);
        // 读取表格字段

        $out = $this->_serv->list_by_conds($conds, $option, array('lid' => 'DESC'));
        if (empty($out)) {
            $out = array();
        }
        $this->_fmt && $this->_format($out, true);

        return true;
    }

}
