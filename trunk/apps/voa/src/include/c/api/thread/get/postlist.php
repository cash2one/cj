<?php
/**
 * voa_c_api_thread_get_postlist
 * 社区/评论列表
 * $Author$
 * $Id$
 */
class voa_c_api_thread_get_postlist extends voa_c_api_thread_base {

    public function execute() {

        $tid = rintval($this->request->get('tid'));

        // 读取评论
        $uda_post = &uda::factory('voa_uda_frontend_thread_post_list');
        $posts = array();
        //条件查询
        $conds = array(
            'tid' => $tid,
            'first' => 0
        );

        //分页参数
        $conds['perpage']= rintval($this->request->get('limit'));
        $conds['page']=rintval($this->request->get('page'));

        if (!$uda_post->execute($conds, $posts)) {
            $this->_error_message($uda_post->errmsg);
            return true;
        }

         //获取用户ID
        $mems = array();
        if ($posts) {
            foreach ($posts as $key => $row) {
                $mems[$row['uid']] = $row['uid'];
            }
        }

        /** 用户头像信息 */
        $servm = &service::factory('voa_s_oa_member', array('pluginid' => 0));
        $users = $servm->fetch_all_by_ids(array_keys($mems));
        voa_h_user::push($users);


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
