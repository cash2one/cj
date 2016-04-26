<?php
/**
 * voa_c_api_thread_get_threadlist
 * 社区/主题列表
 * $Author$
 * $Id$
 */
class voa_c_api_thread_get_threadlist extends voa_c_api_thread_base {

    public function execute() {
        $p_sets = voa_h_cache::get_instance()->get('plugin.thread.setting', 'oa');//读同事社区配置缓存

        $ac = (string)$this->request->get('ac');

        //根据动作，设置标题
        switch ($ac) {
            case 'hot':
                $this->view->set('navtitle', '热门话题');
                break;
            case 'choice':
                $this->view->set('navtitle', '精选话题');
                break;
            default:
                $this->view->set('navtitle', '所有话题');
        }

        // 读取话题
        $uda_post = &uda::factory('voa_uda_frontend_thread_list');
        $posts = array();
        $conds = array();
        if (!$uda_post->execute($conds, $posts)) {
            $this->_error_message($uda_post->errmsg);
            return true;
        }

         //获取用户ID
        $mems = array();
        if ($posts) {
            foreach ($posts as $key => $row) {
                $mems[$row['m_uid']] = $row['uid'];
            }
        }

        /** 用户头像信息 */
        $servm = &service::factory('voa_s_oa_member', array('pluginid' => 0));
        $users = $servm->fetch_all_by_ids(array_keys($mems));
        voa_h_user::push($users);

        /** 整理输出 */
//         $uda = &uda::factory('voa_uda_frontend_thread_format');
//         $posts = $uda->format_post_reply($list);
/*         foreach ($posts as &$v) {
            $v['avatar'] = voa_h_user::avatar($v['uid'], isset($users[$v['uid']]) ? $users[$v['uid']] : array());

        }

        unset($v); */

        foreach ($posts as &$_p) {
            $_p['face'] = voa_h_user::avatar($_p['uid']);
        }
        unset($_p);

        /*输出结果*/
        $this->_result = array(
            'total' => $uda_post->get_total(),
            'limit' => $this->_params['limit'],
            'page' => $this->_params['page'],
            'data' => $posts ? array_values($posts) : array()
        );

        return true;
    }
}
