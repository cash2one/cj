<?php
/**
 * voa_c_frontend_news_add
 * 新闻公告添加
 * @date: 2015年5月18日
 * @author: kk
 * @version:
 */

class voa_c_frontend_news_add extends voa_c_frontend_news_base {
	public function execute() {

		// 记录日志
		logger::error($this->_user['m_username']);
		//获取公告id
		$news_id = rintval($this->request->get('ne_id'));
		$tem_id = rintval($this->request->get('tem_id'));
		$result = array();
		$issue = array();
        $type = null;

        // 判断当前操作动作 是添加还是编辑
		$action = '';
		try{
			$m_uid = startup_env::get('wbs_uid');

			//判断用户是否有权限发布新闻
			$uda = &uda::factory('voa_uda_frontend_news_issue');
			$uda->issue(array('m_uid'=>$m_uid), $issue);

            /* 如果是编辑 */
			if($news_id){
				//读取内容
				$new = &uda::factory('voa_uda_frontend_news_view');
				$request = array(
					'm_uid' => $m_uid,
					'ne_id' => $news_id
				);
				$new->get_view_edit($request, $result);
                if (!empty($result['multiple'])) {
                    $type = '1';
                }
				$action = 'edit';
			}
			$uda_cat = &uda::factory('voa_uda_frontend_news_category');
			$categories = $uda_cat->list_categories();
		}catch (help_exception $h) {
			$this->_no_authority($h->getMessage(), null, null, '发生错误');
			return false;
		} catch (Exception $e) {
			logger::error($e);
			$this->_error_message($e->getMessage());
			return false;
		}

        // 前端模版选择去除
		if($tem_id) {
			$result = array();
			$url = config::get('voa.cyadmin_url') . 'OaRpc/Rpc/NewsTemplates';
			if (!voa_h_rpc::query($result, $url, 'get_by_id', $tem_id)) {
				$result = array();
			}
		}

		$this->view->set('result', $result);
        $this->view->set('type', $type);
		$this->view->set('action', $action);
		$this->view->set('categories', $categories);

        // 输出模版
		$this->_output('mobile/news/add');
	}
}

//end
