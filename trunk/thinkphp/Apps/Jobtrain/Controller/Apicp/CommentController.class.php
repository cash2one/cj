<?php

namespace Jobtrain\Controller\Apicp;
use Common\Common\Pager;
use Common\Common\Cache;

class CommentController extends AbstractController {
    /**
     * 获取评论列表
     */
    public function list_get() {
        $s_comment = D('Jobtrain/JobtrainComment', 'Service');
        $page = I('get.page', 0, 'intval');
        $limit = I('get.limit', 0, 'intval');
        $aid = I('get.aid', 0, 'intval');
        // 分页参数
        list($start, $limit, $page) = page_limit($page, $limit);
        // 获取列表
        $list = $s_comment->list_by_conds_join_member($aid, $start, $limit);
        $total = $s_comment->count_by_conds(array('aid' => $aid));
        //分页
        $multi = null;
        if ($total > 0) {
            $pagerOptions = array(
                'total_items' => $total,
                'per_page' => $limit,
                'current_page' => $page,
                'show_total_items' => true,
            );
            $multi = Pager::make_links($pagerOptions);
        }
        // 格式化输出
        $list = $this->_template_format($list);

        $this->_result = array(
            'list' => $list,
            'page' => $page,
            'total' => $total,
            'limit' => $limit,
            'multi' => $multi,
        );
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
    /**
     * 添加评论
     */
    public function add_post() {
        $s_comment = D('Jobtrain/JobtrainComment', 'Service');

        $aid = I('post.aid', 0, 'intval'); // 当前新闻公告ID
        $content = I('post.content', '', 'htmlspecialchars'); //评论的内容
        $to_uid = I('post.to_uid', '', 'intval'); //回复者的uid
        $to_username = I('post.to_username' ,''); //当前回复的上一级的姓名
        $toid = I('post.toid', '', 'intval'); //回复id
        // 添加评论
        $comment = array(
            'content' => $content,
            'm_uid' => 0,
            'aid' => $aid,
            'm_username' => '管理员',
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
            $desc = "标题：".$article['title']."\n回复者：管理员\n回复：".$content;
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
     * 格式化输出
     * @param array $list 要格式的数据
     * @return array $list 格式后的数据
     */
    protected function _template_format($list = array()) {

        if (!empty($list)) {
            foreach ($list as &$v) {
                $v['created_u'] = rgmdate($v['created'], 'Y-m-d H:i');
            }
        }

        return $list;
    }
}