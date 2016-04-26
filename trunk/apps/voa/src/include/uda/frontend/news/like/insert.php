<?php
/**
 * voa_uda_frontend_news_like_insert
 * 统一数据访问/新闻公告/点赞
 * $Author ppker
 * $Id$
 */

class voa_uda_frontend_news_like_insert extends voa_uda_frontend_news_abstract {
	/** service 类 */
	private $__service = null;
	public function __construct() {
		parent::__construct();
		if ($this->__service == null) {
			//$this->__service = new voa_s_oa_news_like();
			$this->__service = &service::factory("voa_s_oa_news_like");
		}
		$this->news_num_like = &service::factory("voa_s_oa_news");
	}

	/**
	 * 新增点赞记录
	 * @param array $request 请求的参数
	 * @param array $result (引用结果)新增的点赞记录
	 * @return boolean
	 */
	public function add_like(array $request, array &$result) {
		// 数据表字段过滤
		// 
		$fields = array(
			'ip' => array(
				'ip', parent::VAR_STR,
				array($this->__service, 'validator_ip'),
				null,false
			),
			'm_uid' => array(
				'm_uid', parent::VAR_INT,
				array($this->__service, 'validator_uid'),
				null,false
			),
			'ne_id' => array(
				'ne_id', parent::VAR_INT,
				array($this->__service, 'validator_neid'),
				null,false
			),
			
		);
		// 检查过滤，参数
		if(!$this->extract_field($this->__request, $fields, $request)) {
			return false;
		}
		try {
			$this->__service->begin();
			// 获取当前用户此篇文章 最近的一次点赞次数，false 则1  最后一次点赞时间，用于过滤
			$list = $this->__service->list_by_conds(array('m_uid'=>$this->__request['m_uid'],'ne_id'=>$this->__request['ne_id']),array(0,1),array('created'=>'DESC','like_id'=>'DESC'));
			if($list){
				$current = reset($list);// 第一条记录
				$new_des = $current['description'];
				$new_like_time = $current['created'];

				/*foreach ($list as $key => $val) {
					$new_des = $val['description'];
					$new_like_time = $val['created'];
				}*/
				// 判断时间的合法性,900秒 15分钟稍长，现在改成15秒
				// 判断 点赞 和 取消
				
				if($new_des == 1 && startup_env::get('timestamp')-15 <= $new_like_time ){
					// $this->errmsg('1', '请勿频繁点赞！');
					$this->set_errmsg(voa_errcode_oa_news::ERR_TIME_DES);
					return false;
				}
			}
			
			// 隔离前端私自构造数据	
			if(!$list) $this->__request['description'] = 1;
			else $this->__request['description'] = $new_des;
			// 添加点赞数据记录
			$like_data = array(
				'ip' => $this->__request['ip'],
				'm_uid' => $this->__request['m_uid'],
				'ne_id' => $this->__request['ne_id'],
				'description' => $this->__request['description']
			);
			// 更新 description 状态码
			$new_like_data = $like_data;
			$new_like_data['description'] = $like_data['description'] == 1 ? 2 : ($like_data['description'] == 2?1:1) ;
			
			$record = $this->__service->insert($new_like_data);
			// news 主要的数据存储 num_like
			$ne_id = $this->__request['ne_id'];
			// 获取相对先钱的初始 点赞状态
			$description = $like_data['description'];

			// 获取当前最新点赞次数
			$news_data = $this->news_num_like->get($ne_id);
			$news_data['num_like'] = intval($news_data['num_like']);

			$data = array();
			switch ($description) {
				case '1':
					$data['num_like'] = $news_data['num_like'] + 1;
					break;
				case '2':
					$data['num_like'] = $news_data['num_like'] - 1;
					break;
				default:
					// 没必要修改
					break;
			}
			// save 数据
			$this->news_num_like->update($ne_id,$data);

			// 把点赞次数 增加到 返回数据集里面
			$record['num_like'] = $data['num_like'];
			$result = $this->__service->format_one($record);

			$this->__service->commit();
			
		} catch (Exception $e) {
			$this->__service->rollback();
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
		return true;
	}

}
