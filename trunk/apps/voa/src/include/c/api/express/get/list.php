<?php
/**
 * voa_c_api_express_get_list
 * 快递助手/快递列表
 * $Author$
 * $Id$
 */
class voa_c_api_express_get_list extends voa_c_api_express_base {

    public function execute() {
    	//读同事社区配置缓存
        $p_sets = voa_h_cache::get_instance()->get('plugin.express.setting', 'oa');

        // 读取话题
        $uda_post = &uda::factory('voa_uda_frontend_express_mem_list');
        $posts = array();
        $conds = array();
        //设置分页参数
        $perpage = rintval($this->request->get('limit'));
        $perpage = 20 > $perpage ? 20 : $perpage;
        $page = rintval($this->request->get('page'));
        $conds['perpage'] = $perpage;
        $conds['page']=$page;

        if (!$uda_post->get_by_conds($conds, $posts)) {
            $this->_error_message($uda_post->errmsg);
            return true;
        }

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
