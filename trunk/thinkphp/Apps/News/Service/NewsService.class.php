<?php
/**
 * 新闻公告 Service
 * User: Muzhitao
 * Date: 2015/9/16
 * Time: 09:43
 * Email:muzhitao@vchangyi.com
 */

namespace  News\Service;

class NewsService extends AbstractService {

	protected $_rigth_model;
	protected $_read_model;
	protected $_comment_model;
	protected $_memeber_model;
	protected $_dempartment_model;
	protected $_like_model;
	protected $_check_model;

	// 构造方法
	public function __construct() {

		parent::__construct();

		// 实例化相关模型
		$this->_d = D("News/News");
		$this->_rigth_model = D('News/NewsRight');
		$this->_read_model = D('News/NewsRead');
		$this->_comment_model = D('News/NewsComment');
		$this->_memeber_model = D('Common/Member');
		$this->_dempartment_model = D('Common/MemberDepartment');
		$this->_like_model = D("News/NewsLike");
		$this->_check_model = D('News/NewsCheck');
	}

	/**
	 * 与当前用户相关的公告列表
	 * @param $conds
	 * @param $page_option
	 * @return mixed
	 */
	public function list_my_news($conds, $page_option = array()) {

		$list = array();
		// 查询分类下的权限公告
		$right_list = $this->_rigth_model->list_ne_by_nca_id($conds['nca_id']);

		/* 如果为空，直接返回空数据 */
		if (empty($right_list)) {
			return array($list, 0);
		}

		// 获取当前用户所在的部门ID
		$cd_id = $this->_d->get_member_by_uid($conds['m_uid']);
		$cd_id = array_column($cd_id, 'cd_id');

		// 公告ID数据集合
		$nca_data = array();

		foreach ($right_list as $_v) {

			// 如果为浏览权限为全部
			if ($_v['is_all'] == 1) {
				$nca_data[] = $_v['ne_id'];
			} else {
				if ($_v['m_uid'] == $conds['m_uid'] || in_array($_v['cd_id'], $cd_id)) {
					$nca_data[] = $_v['ne_id'];
				}
			}
		}

		//  如果当前查询的公告IDS为空，直接返回空数据
		if (empty($nca_data)) {
			return array($list, 0);
		}

		// 返回条件查询的公告列表
		$news_data = $this->_d->list_by_ne_id($nca_data, $conds['keyword'], $page_option);

		// 公告记录数
		$news_total = $this->_d->count_by_ne_id($nca_data, $conds['keyword']);

		// 获取用户已经阅读的公告
		$read_data = $this->_read_model->list_by_uid($conds['m_uid']);

		// 格式化数组
		$list = $this->_format_data($news_data, $read_data);

		return array($list, $news_total['total']);
	}

	public function count_by_ne_id($conds) {

		$this->_d->count_by_ne_id($conds['']);
	}

	/**
	 * 判断当前用户是否有权限浏览
	 * @param $m_uid
	 * @return bool
	 */
	public function issure($ne_id, $m_uid, $is_all = 0) {

		/* 如果当前公告不是全部人员可读 */
		if ($is_all == 0) {

			$right_data = $this->_rigth_model->list_by_conds(array('ne_id' => $ne_id));
			$uids = array_filter(array_column($right_data, 'm_uid'));

			// 判断当前用户是在所选的人员中
			if (in_array($m_uid, $uids)) {
				return true;
			}

			// 查询当前用户所在的部门
			$user_data = $this->_d->get_member_by_uid($m_uid);
			$user_data = array_column($user_data, 'cd_id');

			/* 判断当前用户是否在权限部门里 */
			$dps  = array_filter(array_column($right_data, 'cd_id'));
			if ($dps) {
				foreach ($user_data as $_dep) {
					if (in_array($_dep, $dps)) {
						return true;
					}
				}
			}

			return false;
		}



		return true;
	}

	/**
	 * 格式化公告列表
	 * @param $news_data
	 * @param $read_data
	 * @return mixed
	 */
	protected function _format_data($news_data, $read_data) {

		// 如果为空
		if (empty($news_data)) {
			return $news_data;
		}

		 // 默认当前设置为未阅读
		foreach ($news_data as $_k => $_v) {
			$news_data[$_k]['status'] = self::NO_READ;
		}

		// 当前已经阅读的公告
		if ($read_data) {
			$ne_data = array_column($read_data, 'ne_id');
			foreach ($news_data as $_k => $_v) {
				if (in_array($_v['ne_id'], $ne_data)) {
					$news_data[$_k]['status'] = self::IS_READ;
				}
			}
		}

		// 返回处理的数据
		return $news_data;
	}

	/**
	 * 格式化公告详情
	 * @param $data
	 */
	public function format_detail(&$data, $m_uid) {

		$data['cover']= '';
		// 获取封面
		if ($data['cover_id']) {
			$data['cover'] = $this->get_attachment($data['cover_id']);
		}

		// 判断当前用户是否是发布作者
		if ($data['m_uid'] == $m_uid) {
			$data['is_author'] = self::IS_AUTHOR;
		} else {
			$data['is_author'] = self::NO_AUTHOR;
		}
		$data['published'] = $data['published'] ? $data['published'] : time();
		// 未读人数
		//$data['unread_nums'] = $this->_count_unread($data);

		$data['liked'] = $this->_liked($data['ne_id'], $m_uid);
	}

	/**
	 * 未读人数
	 * @param $data
	 * @return int
	 */
	protected function _count_unread($data) {

		// 已经阅读的人数
		$read_nums = $this->_read_model->count_by_conds(array('ne_id' => $data['ne_id']));

		//
		if ($data['is_all']) {
			// 总数目
			$total_nums =  $this->_memeber_model->count();

			// 未读人员的数目
			$total = $total_nums - $read_nums;
		} else {
			/* 查询某个公告下的阅读权限列表 */
			$rigth_data = $this->_rigth_model->list_ne_by_ne_id($data['ne_id']);


			$m_uids = array_filter(array_column($rigth_data, 'm_uid'));
			$cd_ids = array_filter(array_column($rigth_data,'cd_id'));

			/* 判断当前是否有选择的部门 */
			if (!empty($cd_ids)) {
				$conditions = array(
					'cd_id' =>$cd_ids,
				);

				$d_user_number =  $this->_dempartment_model->list_by_conds($conditions);
				$d_news_muid = array_column($d_user_number, 'm_uid');

				// 合并并删除重复数组
				$user_data = array_unique(array_merge($d_news_muid, $m_uids));

				// 获取总数
				$user_data_count = count($user_data);

				// 未读人员的数目
				$total = $user_data_count - $read_nums;
			} else {
				$total = count($m_uids) - $read_nums;
			}
		}

		// 返回个数
		return $total;
	}

	/**
	 * 判断当前用户是否已经点赞过了
	 * @param $ne_id
	 * @param $m_uid
	 * @return bool
	 */
	protected function _liked($ne_id, $m_uid) {

		$like_data = array('ne_id' => $ne_id,'m_uid' =>$m_uid);
		$like_list = $this->_like_model->list_by_conds($like_data, array(0, 1), array('created'=>'DESC', 'like_id'=>'DESC'));

		// 默认是1
		$new_des = 1;
		if($like_list){
			foreach ($like_list as $key => $val) {
				$new_des = $val['description'];
			}
		}

		return $new_des;
	}

	/**
	 * 查询新闻公告详情
	 * @param Int $ne_id 公告ID
	 * @return mixed
	 */
	public function get_by_ne_id($ne_id, $is_publish) {

		return $this->_d->get_by_ne_id($ne_id, $is_publish);
	}

	/**
	 * 获取审核预览信息
	 * @param $ne_id
	 * @return mixed
	 */
	public function get_preview($ne_id) {

		return $this->_d->get_preview_by_ne_id($ne_id);
	}

	/**
	 * 根据公告ID查询公告发起人
	 * @param $ne_id
	 * @return mixed
	 */
	public function get_uid_by_ne_id($ne_id) {

		return $this->_d->get_uid_by_ne_id($ne_id);
	}

	/**
	 * 获取用户信息
	 * @param $m_uid
	 * @return mixed
	 */
	public function get_by_uid($m_uid) {

		return $this->_d->get_by_uid($m_uid);
	}

	public function list_by_uids($m_uids) {

		return $this->_d->list_by_uids($m_uids);
	}

	public function list_by_ne_id_check($m_uid, $nca_id, $keyword) {

		return $this->_d->list_by_ne_id_check($m_uid, $nca_id, $keyword);
	}
}

//end
