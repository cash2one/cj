<?php

namespace Jobtrain\Controller\Api;
use Common\Common\Cache;

class CommentController extends AbstractController {
    /**
	 * 添加评论
	 */
    public function add_post() {
        $s_comment = D('Jobtrain/JobtrainComment', 'Service');

        $aid = I('post.aid', 0, 'intval'); // 当前新闻公告ID
        $content = I('post.content', '', 'htmlspecialchars'); //评论的内容
        $to_uid = I('post.p_uid', '', 'intval'); //回复者的uid
        $toid = I('post.toid', '', 'intval'); //回复id
        $to_username = I('post.p_username' ,''); //当前回复的上一级的姓名

        // 添加评论
        $comment = array(
            'content' => $content,
            'm_uid' => $this->_login->user['m_uid'],
            'aid' => $aid,
            'm_username' => $this->_login->user['m_username'],
        );

        // 判断当前回复的上一级作者
        if (!empty($to_username)) {
            $comment['to_uid'] = $to_uid;
            $comment['to_username'] = $to_username;
            $comment['toid'] = $toid;
            // 回复推送
            $cache = &Cache::instance();
            $cache_setting = $cache->get('Common.setting');
            $wxqyMsg = &\Common\Common\WxqyMsg::instance();
            // 获取插件信息
            $model_plugin = D('Common/CommonPlugin');
            $plugin = $model_plugin->get_by_identifier('jobtrain');
            // 读配置生成链接
            cfg('JOBTRAIN', load_config(APP_PATH.'Jobtrain/Conf/config.php'));
            $url = cfg('PROTOCAL') . $cache_setting['domain'] . '/Jobtrain/Frontend/Index/CommentList?aid=' . $aid;
            // 生成描述文字
            $s_article = D('Jobtrain/JobtrainArticle', 'Service');
            $article = $s_article->get($aid);
            $desc = "标题：".$article['title']."\n回复者：".$this->_login->user['m_username']."\n回复：".$content;
            $wxqyMsg->send_news('您有一条回复消息', $desc, $url, array($to_uid), '', '', $plugin['cp_agentid'], $plugin['cp_pluginid']);
        }

        $result = $s_comment->insert($comment);

        // 输出结果
        $this->_result = array(
            'comment' => $result['id']
        );

        return true;
    }

    /**
     * 评论列表
     */
    public function list_get() {
        $s_comment = D('Jobtrain/JobtrainComment', 'Service');
        $s_article = D('Jobtrain/JobtrainArticle', 'Service');
        $s_right = D('Jobtrain/JobtrainRight', 'Service');
        $aid = I('get.aid', 0, 'intval');
        $page = I('get.page', 1, 'intval');
        // 获取状态 d_status: 1正常 2删除 3无权限 4分类被禁用
        $article = $s_article->get( $aid );
        if(empty($article)){
            // 内容被删除无法查看
            $this->_result = array(
                'd_status' => 2
            );
            return false;
        }
        // 检查权限
        $d_status = 1;
        if( !$s_right->check_right($article['id'], $article['cid'], $this->_login->user['m_uid'], $article['is_publish']) ){
            $this->_result = array(
                'd_status' => 3
            );
            return false;
        }
        $pagesize = 10;
        // 生成分页参数
        list($start, $limit, $page) = page_limit($page, $pagesize);
        // 查询评论列表
        $comment_list = $s_comment->list_by_conds_join_member($aid, $start, $limit);
        // 获取评论的数目
        $count = count($comment_list);
        if($count<=0){
            $this->_result = array(
                'd_status' => $d_status,
                'page' => $page,
                'total' => 0,
                'list' => $comment_list
            );
            return false;
        }
        // 总数
        $total = $s_comment->count_by_conds(array('aid' => $aid));
        // 获取评论id数组
        $comment_ids = array_column($comment_list, 'id');
        // 获取点赞并组合
        $s_zan = D('Jobtrain/JobtrainCommentZan', 'Service');
        $zan_list = $s_zan->list_by_conds(array('comment_id'=>$comment_ids,'m_uid'=>$this->_login->user['m_uid']));
        $comment_zan_list = array();
        foreach ($zan_list as $v) {
            $comment_zan_list[$v['comment_id']] = 1;
        }
        foreach ($comment_list as &$v) {
            $v['zan'] = $comment_zan_list[$v['id']]?1:0;
        }
        // 输出结果
        $this->_result = array(
            'd_status' => $d_status,
            'page' => $page,
            'total' => $total,
            'list' => $comment_list
        );
        return true;
    }

    /**
     * 点赞
     */
    public function zan_get() {
        $s_zan = D('Jobtrain/JobtrainCommentZan', 'Service');
        $comment_id = I('get.id', 0, 'intval');

        $info = $s_zan->get_by_conds(array('comment_id' => $comment_id, 'm_uid' => $this->_login->user['m_uid']));
        if ($info) {
             $this->_result = array(
                'success' => false
            );
            return true;
        }
        // 添加
        $zan = array(
            'm_uid' => $this->_login->user['m_uid'],
            'comment_id' => $comment_id
        );
        $s_zan->insert($zan);
        // 增加点赞数量
        $s_comment = D('Jobtrain/JobtrainComment', 'Service');
        $s_comment->inc_zan_num($comment_id);

        $this->_result = array(
            'success' => true
        );
        return true;
    }
    /**
     * 删除评论
     */
    public function del_get() {
        $s_comment = D('Jobtrain/JobtrainComment', 'Service');
        $s_zan = D('Jobtrain/JobtrainCommentZan', 'Service');
        $id = I('get.id', 0, 'intval');
        $s_comment->delete($id);
        $s_zan->delete_by_conds(array('comment_id'=>$id));
        return true;
    }
}