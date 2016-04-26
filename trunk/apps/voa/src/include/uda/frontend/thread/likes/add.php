<?php
/**
 * voa_uda_frontend_thread_add
 * 统一数据访问/社区应用/新增点赞记录
 *
 * $Author$
 * $Id$
 */
class voa_uda_frontend_thread_likes_add extends voa_uda_frontend_thread_likes_abstract {
    
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
            array('tid',self::VAR_INT,null,null,false),
            array('uid',self::VAR_INT,null,null,false),
            array('username',self::VAR_STR,null,null,false)
        );
    
        $data = array();
        if (!$this->extract_field($data, $fields)) {
            return false;
        }
        // 点赞信息入库
        $newlikes = array(
            'tid' => $data['tid'],
            'uid' => $data['uid'],
            'username' =>$data['username']
        );
        $out = $this->_serv->insert($newlikes);
        
        //更新点赞数
        $serv_t = &service::factory('voa_s_oa_thread');
        $thread = $serv_t->update_by_conds($data['tid'], array('`likes`=`likes`+?' => 1));
   
        return true;
    }
    
    
}
