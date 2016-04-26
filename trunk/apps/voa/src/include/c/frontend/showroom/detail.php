<?php
/**
 * index.php
 * 培训/文章详情
 * $Author$
 * $Id$
 */
class voa_c_frontend_showroom_detail extends voa_c_frontend_showroom_base {

	public function execute() {

		$ta_id = (int)$this->request->get('id');  // 获取文章ID
		$m_uid = (int)$this->_user['m_uid'];  // 获取用户ID
		try{
			// 获取文章信息
			$uda = &uda::factory('voa_uda_frontend_showroom_action_articleview');
			$article = $uda->view($ta_id, $m_uid);
			if (!$article) { //判断文章是否存在
				voa_h_func::throw_errmsg(voa_errcode_oa_showroom::ARTICLE_NOT_EXIST);
			}
			//判断有无阅读文章权限
			$department_ids = $uda->get_department_id($m_uid);
			if ($article['is_all'] != voa_d_oa_showroom_articleright::IS_ALL
					&& !in_array($m_uid, $article['contacts']) && !array_intersect($department_ids, $article['deps'])
			) {
				voa_h_func::throw_errmsg(voa_errcode_oa_showroom::ARTICLE_NO_RIGHT);
			}
		} catch (help_exception $h) {
			$this->_error_message($h->getMessage());
			return false;
		} catch (Exception $e) {
			logger::error($e);
			$this->_error_message($e->getMessage());
			return false;
		}

		$article =  $this->_format_data($article, $m_uid);

		$this->view->set('article', $article);
		$this->view->set('navtitle', $article['tc_name']);

		// 引入应用模板
		$this->_output('mobile/showroom/view');
	}

	/**
	 * 格式化文章
	 * @param array $article 文章
	 * @return array
	 */
	protected  function _format_data($article,  $m_uid) {

		$result = array();
		if ($article) {
			$result['ta_id'] = $article['ta_id'];
			$result['uid'] = $m_uid;
			$result['title'] = rhtmlspecialchars($article['title']);
			$result['author'] = rhtmlspecialchars($article['author']);
			$result['tc_id'] = $article['tc_id'];
			$result['tc_name'] = rhtmlspecialchars($article['tc_name']);
			$result['created'] =  rgmdate($article['created'], 'Y-m-d H:i');
			$result['updated'] =  rgmdate($article['updated'], 'Y-m-d H:i');
			$result['content'] = $article['content'];
		}

		return $result;
	}
}
