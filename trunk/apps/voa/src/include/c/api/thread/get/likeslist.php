<?php
/**
 * voa_c_api_thread_get_likeslist
 * 社区/点赞列表
 * $Author$
 * $Id$
 */
class voa_c_api_thread_get_likeslist extends voa_c_api_thread_base {

    public function execute() {

        $tid = rintval($this->request->get('tid'));

        //读取点赞
        $uda_likes = &uda::factory('voa_uda_frontend_thread_likes_list');
        $likes = array();
        $conds = array();
        $conds['tid'] = $tid;
        //分页参数
        $conds['perpage']= rintval($this->request->get('limit'));
        $conds['page']=rintval($this->request->get('page'));


        if (!$uda_likes->execute($conds, $likes)) {
            $this->_error_message($uda_post->errmsg);
            return true;
        }

        $uids = array();
        if($likes){
            foreach ($likes as $k => $v) {
                $uids[$v['uid']] =  $v['uid'];
            }
        }


        // 读取用户信息,设置用户头像
        $serv_m = &service::factory('voa_s_oa_member');
        $users = $serv_m->fetch_all_by_ids(array_keys($uids));
        voa_h_user::push($users);

        foreach ($likes as &$_p) {
            $_p['face'] = voa_h_user::avatar($_p['uid']);
        }
        unset($_p);


        /*输出结果*/
        $this->_result = array(
            'total' => $uda_likes->get_total(),
            'limit' => $this->_params['limit'],
            'page' => $this->_params['page'],
            'data' => $likes ? array_values($likes) : array()
        );

        return true;
    }

}
