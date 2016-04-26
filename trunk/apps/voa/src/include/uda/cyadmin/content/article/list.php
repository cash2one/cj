<?php
class voa_uda_cyadmin_content_article_list extends voa_uda_cyadmin_content_article_base {
	private $__service = null;

	public function __construct() {
		parent::__construct();
		if ($this->__service == null) {
			$this->__service = new voa_s_cyadmin_content_article_list();
		}
	}

	/**
	 * 根据主键查询单条数据
	 * 
	 * @param $aid int        	
	 * @return $data array
	 *        
	 */
	public function get_view($aid) {
		return $this->__service->get($aid);
	}

	/**
	 * 添加数据
	 * 
	 * @param $data array()        	
	 * @return boolean
	 *
	 */
	public function add_news(array $request, array &$result) {
		$fields = array(
			// 标题
			'title' => array(
				'title',
				parent::VAR_STR,
				'validator_title',
				null,
				false 
			),
			
			'content' => array(
				'content',
				parent::VAR_STR,
				'validator_content',
				null,
				false 
			) 
		);
		
		if (!$this->extract_field($this->__request, $fields, $request)) {
			
			return false;
		}
		if (!empty($request['sourl']) && !$this->validatot_url($request['sourl'])) {
			
			return false;
		}
		try {
			
			$data = array(
				'title' => $this->__request['title'],
				'content' => $this->__request['content'],
				'sourl' => $request['sourl'],
				'source' => !empty($request['source']) ? $request['source'] : '畅移云工作',
				'logo_atid' => !empty($request['logo_atid']) ? $request['logo_atid'] : 0,
				'face_atid' => !empty($request['face_atid']) ? $request['face_atid'] : 0,
				'asort' => $request['asort'],
				'acid' => $request['acid'],
				'tags' => $request['tags'],
				'description' => $request['description'],
				'is_publish' => $request['is_publish'] 
			);
			
			$this->__service->insert($data);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
		
		return true;
	}

	public function update_news($aid, $request) {
		try {
			$data = array(
				'title' => $request['title'],
				'content' => $request['content'],
				'sourl' => $request['sourl'],
				'source' => !empty($request['source']) ? $request['source'] : '畅移云工作',
				'logo_atid' => !empty($request['logo_atid']) ? $request['logo_atid'] : 0,
				'face_atid' => !empty($request['face_atid']) ? $request['face_atid'] : 0,
				'asort' => $request['asort'],
				'acid' => $request['acid'],
				'tags' => $request['tags'],
				'description' => $request['description'],
				'is_publish' => !empty($requset['is_publish']) ? $request['is_publish'] : 1 
			);
			$this->__service->update($aid, $data);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
		
		return true;
	}

	public function del_news($ids) {
		try {
			$this->__service->delete($ids);
		} catch (Exception $e) {
			
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
		
		return true;
	}
}
