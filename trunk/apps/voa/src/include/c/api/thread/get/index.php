<?php
/**
 * voa_c_api_thread_get_index
 * 社区/社区主页（热门话题|精选话题|所有话题）
 * $Author$
 * $Id$
 */
class voa_c_api_thread_get_index  extends voa_c_api_thread_base {

    public function execute() {

    	//读同事社区配置缓存
        $p_sets = voa_h_cache::get_instance()->get('plugin.thread.setting', 'oa');

        //热门话题、精选话题、所有话题、我的社区分类查询
        $acs = array('hot', 'choice','all','mine');
        $ac = (string)$this->request->get('ac');
        if (!in_array($ac, $acs)) {
            $ac = 'hot';
        }

        // 读取话题
        $uda = &uda::factory('voa_uda_frontend_thread_list');
        $threads = array();
        $conds = array();
        if($ac != 'all'){
            if($ac == 'mine'){
                //我的社区设置我的uid
                $conds['uid'] = startup_env::get('wbs_uid');
            }else{
                //设置热门、精选取值
                $key = $p_sets[$ac."_key"];
                $val = $p_sets[$ac."_value"];
                $conds[$key] = $val;
            }
        }

        //设置分页参数
        $perpage = rintval($this->request->get('limit'));
        $page = rintval($this->request->get('page'));
        $conds['perpage'] = $perpage;
        $conds['page']=$page;

        if (!$uda->execute($conds, $threads)) {
            $this->_error_message($uda->errmsg);
            return true;
        }


        $mems = array(); //获取用户ID
        $post_conds = array();//获取话题id
        if ($threads) {
            foreach ($threads as $key => $row) {
                $mems[$row['uid']] = $row['uid'];//用户id
                $post_conds['tid'][] = $row['tid'];//话题id
            }


            /** 用户头像信息 */
            $servm = &service::factory('voa_s_oa_member', array('pluginid' => 0));
            $users = $servm->fetch_all_by_ids(array_keys($mems));
            voa_h_user::push($users);

            $attach = array();
            foreach ($threads as &$_p) {


                //设置头像
                if($_p['uid'] == 0)	{
                    //uid是0设置缓存取官网头像
                	$_p['face'] = '/attachment/read/'.$p_sets['offical_img'];
                }else{
	                $_p['face'] = voa_h_user::avatar($_p['uid']);
                }

                //附件
                $ids = explode(",",$_p['attach_id']);
                foreach ($ids as $_v) {
                    if(!empty($_v)){
                        $attach[]['aid'] = $_v;
                    }
                }
                $_p['attachs'] =  $attach;

                //判断话题类型(热门、精选)
                if($p_sets['hot_key'] == 'likes'){
                    if(rintval($_p['likes'])>=rintval($p_sets['hot_value'])){
                        $_p['good'] = 1;//热门话题
                    }
                }else{
                    if(rintval($_p['replies'])>=rintval($p_sets['hot_value'])){
                        $_p['good'] = 1;//热门话题
                    }
                }

                if($p_sets['choice_key'] == 'likes'){
                    if(rintval($_p['likes'])>=rintval($p_sets['choice_value'])){
                        $_p['choice'] = 1;//精选话题
                    }
                }else{
                    if(rintval($_p['replies'])>=rintval($p_sets['choice_value'])){
                        $_p['choice'] = 1;//精选话题
                    }
                }

                unset($attach);
            }
            unset($_p);

            //读取话题内容
            $uda_post = &uda::factory('voa_uda_frontend_thread_post_list');
            $posts = array();
            $post_conds['first'] = 1;
            if (!$uda_post->execute($post_conds, $posts)) {
                $this->_error_message($uda_post->errmsg);
                return true;
            }

            //设置话题内容
            foreach ($posts as $_p) {
                $threads[$_p['tid']]['message'] = $_p['_message'];
            }

            //用户是否点赞
            $uda_islike = &uda::factory('voa_uda_frontend_thread_likes_list');
            $islike = array();
            if (!$uda_islike->execute(array('uid' => startup_env::get('wbs_uid'),'tid' => $post_conds['tid']), $islike)) {
                $this->_error_message($uda_post->errmsg);
                return true;
            }

            foreach ($islike as $_lk) {
                if(!empty($threads[$_lk['tid']])){
                    $threads[$_lk['tid']]['islike'] = $_lk;
                }
            }

        }

        /*输出结果*/
        $this->_result = array(
            'total' => $uda->get_total(),
            'limit' => $this->_params['limit'],
            'page' => $this->_params['page'],
            'data' => $threads ? array_values($threads) : array()
        );

        return true;
    }

}
