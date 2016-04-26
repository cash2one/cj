<?php
/**
 * voa_c_frontend_nvote_new
 * 投票调研-创建投票
 * User: luckwang
 * Date: 15/3/9
 * Time: 上午11:01
 */

class voa_c_frontend_nvote_new extends voa_c_frontend_nvote_base {

    public function execute()
    {
	    // 当前操作者
	    $m_uid = startup_env::get('wbs_uid');
	    try{
		    $uda_issue = &uda::factory('voa_uda_frontend_nvote_issue');
		    $uda_issue->issue($m_uid);
	    } catch (Exception $e) {
		    logger::error($e);
		    $this->_error_message($e->getMessage());
		    return false;
	    }

		//如果为post请求执行添加
        if ($this->request->get_method() === 'POST') {
            $this->__add();
        } else {
            $this->_output('mobile/nvote/new');
        }
    }

    /**
     * 添加投票
     */
    private function __add() {

        $data = $this->request->postx();

        $nvote = array(
            'subject' => '',
            'is_single' => '1',
            'is_show_name' => '1',
            'is_show_result' => '1',
            'is_repeat' => '2',
            'end_time' => '',
            'at_id' => '0',
        );
        $this->__init_params($nvote, $data['nvote']);
        //用户id
        $m_uids = array();
        if (!empty($data['m_uids'])) {
            $m_uids = explode(',', $data['m_uids']);
        }
        //部门id
        $cd_ids = array();
        if (!empty($data['cd_ids'])) {
            $cd_ids = explode(',', $data['cd_ids']);
        }

        //投票选项
        $options = array();
        if (!empty($data['options'])) {
            $options = $data['options'];
        }

	    // 添加当前发布者
	    $m_uids[] = startup_env::get('wbs_uid');

        //取当前登陆用户，活动发起人
        $nvote['submit_ca_id'] = 0;
        $nvote['submit_id'] = startup_env::get('wbs_uid');
        $uda = &uda::factory('voa_uda_frontend_nvote_add');
        try{
            if (!$uda->add($nvote, $m_uids, $cd_ids, $options, $this->session)) {
                echo $this->_error_message($uda->error);
                exit;
            }

        } catch(Exception $e) {
           // logger::error(print_r($e, true));
            $this->_error_message('创建投票错误');
        }
        echo rjson_encode(
            array(
                'errcode' => 0,
                'errmsg' => 'success',
                'timestamp' => startup_env::get('timestamp'),
                'result' => array(
                    'url' =>  "/frontend/nvote/view?nv_id=" . $nvote['id'],
                    'message' => '创建投票成功'
                )
            )
        );
        exit;
    }

    /**
     * 初始化提交参数
     * @param $params
     * @param $data
     * @return mixed
     */
    private function __init_params(&$params, $data) {

        foreach ($params as $key => &$v) {
            if (!empty($data[$key]) &&
                trim($data[$key]) != '') {
                $v = $data[$key];
            }
        }
        return $params;
    }

}
