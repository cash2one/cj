<?php
/**
 * voa_uda_frontend_news_like_list
 * 统一数据访问/新闻公告/获取点赞列表
 * $Author ppker
 * $Id$
 */
class voa_uda_frontend_news_like_list extends voa_uda_frontend_news_abstract{

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
            array('m_uid', self::VAR_INT, null, null, true),
            array('description', self::VAR_INT, null,null,true),
            array('ne_id', self::VAR_INT, null,null,true),
            array('ip', self::VAR_STR, null,null,true),
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
        // 点赞表
        $this->like = new voa_s_oa_news_like();
        $this->_total = $this->like->count_by_conds($conds);
        // 读取表格字段

        $out = $this->like->list_by_conds($conds, $option, array('like_id' => 'DESC'));
        if (empty($out)) {
            $out = array();
        }
        $this->_fmt && $this->_format($out, true);

        return true;
    }

}
