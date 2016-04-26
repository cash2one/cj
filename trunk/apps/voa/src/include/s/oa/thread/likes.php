<?php
/**
 * 话题点赞表
 * $Author$
 * $Id$
 */
class voa_s_oa_thread_likes extends voa_s_abstract {
    
    /**
     * __construct
     *
     * @return void
     */
    public function __construct() {
    
        parent::__construct();
    }
    
    /**
     * 格式化点赞信息
     * @param array $likes 点赞信息
     * @return boolean
     */
    public function format(&$post) {
        $post['_created'] = rgmdate($post['created'], 'u');
        $post['_updated'] = rgmdate($post['updated'], 'u');
        return true;
    }
    
}
