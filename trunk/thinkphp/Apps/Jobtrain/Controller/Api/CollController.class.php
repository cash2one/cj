<?php

namespace Jobtrain\Controller\Api;
use Common\Common\Cache;

class CollController extends AbstractController {
    /**
	 * 更新收藏情况
	 */
    public function update_get() {
        $s_coll = D('Jobtrain/JobtrainColl', 'Service');
        $s_article = D('Jobtrain/JobtrainArticle', 'Service');
        $cache = &\Common\Common\Cache::instance();
        
        $id = I('get.id',0,'intval');
        $is_coll = true;
        // 获取收藏
        $info = $s_coll->get_by_conds(array('aid' => $id, 'm_uid' => $this->_login->user['m_uid']));
        if(!$info){
        	// 如果未收藏 插入
			$dps = $cache->get('Common.department');
        	$jobs = $cache->get('Common.job');
        	$job = $jobs[$this->_login->user['cj_id']]['cj_name'];
        	$data = array(
				'aid' => $id,
				'm_uid' => $this->_login->user['m_uid'],
				'm_username' => $this->_login->user['m_username'],
				'department' => $dps[$this->_login->user['cd_id']]['cd_name'],
				'job' => $job?$job:'',
				'mobile' => $this->_login->user['m_mobilephone']
        	);
        	$s_coll->insert($data);
            // 增加收藏数量
            $s_article->inc_coll_num($id);
        }else{
            $is_coll = false;
            // 删除已收藏
            $s_coll->delete_real_by_aid($id, $this->_login->user['m_uid']);
            // 减少收藏数量
            $s_article->dec_coll_num($id);
        }
        $this->_result = array(
            'is_coll' => $is_coll
        );
        return true;
    }
    /**
     * 我的收藏
     */
    public function list_get() {
        $s_coll = D('Jobtrain/JobtrainColl', 'Service');
        // 获取搜索参数
        $type_id = I('get.type_id', '');
        $keywords = I('get.keywords','');
        $page = I('get.page', 1, 'intval');
        // 设置分页
        $pagesize = 8;
        list($start, $limit, $page) = page_limit($page, $pagesize);
        // 获取文章列表 多读取一条
        $list = $s_coll->get_list_join_article($type_id, $keywords, $this->_login->user['m_uid'], $start, $limit+1);
        // 计算数量 删除最后一个
        $count = count($list);
        if($count>$pagesize){
            array_pop($list);
        }
        // 输出结果
        $this->_result = array(
            'total' => $count<=$pagesize?0:$pagesize*$page+1,
            'list' => $list
        );
        return true;
    }
}