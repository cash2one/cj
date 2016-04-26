<?php
/**
 * 点赞接口
 * $Author$
 * $Id$
 */

class voa_c_api_news_post_praise extends voa_c_api_news_abstract {

	public function execute() {

		
		// 请求的参数
		$fields = array(
			// 新闻公告ID
			'ne_id' => array('type' => 'int', 'required' => true)
		);
		if (!$this->_check_params($fields)) {
			return false;
		}

		$this->_params['m_uid'] = $this->_member['m_uid'];
		
		//linshiling 检测有没有点赞权限
		$cd_id = $this->_member['cd_id'];
		$right = new voa_d_oa_news_right();
		$rs = $right->has_right($this->_params['ne_id'], $this->_params['m_uid'], $cd_id);
		if(!$rs) {
			return $this->_set_errcode('没有点赞权限');
		}

		
		//入库
		$d = new voa_d_oa_news_praise();
		$data = array('ne_id' => $this->_params['ne_id'], 'm_uid' => $this->_params['m_uid']);
		$rs = $d->get_by_conds($data);
		
		if(!$rs) {
			$rs = $d->insert($data);
		}else{
			return $this->_set_errcode('你已点赞过了');
		}
		
		// 输出结果
		$this->_result = $rs;

		return true;
	}

}

