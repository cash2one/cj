<?php
/**
 * SignAttachmentService.class.php
 * $author$
 */

namespace Sign\Service;

class SignAttachmentService extends AbstractService {

	// 构造方法
	public function __construct() {

		$this->_d = D("Sign/SignAttachment");
		parent::__construct();
	}


	/**
	 * 获取关联图片
	 * @param unknown $record
	 * @return unknown
	 */
	public function out_img($record) {
		
		// 查询上传的图片
		$slist = array();
		foreach ($record as $_va) {
			$slist[] = $_va['sl_id'];
		}
		$data = $this->_d->out_img($slist);
		
		//获取setting表缓存
		$cache = &\Common\Common\Cache::instance();
		$sets = $cache->get('Common.setting');
		$url = $sets['domain'];

		// 将记录的关联图片追加到数组中
		if (!empty($data)) {
			foreach ($record as &$_record) {
				$_record['attachs'] = array();
				foreach ($data as $_img) {
					if ($_img['outid'] == $_record['sl_id']) {
						$_record['attachs'][] = cfg('PROTOCAL') . $url . '/attachment/read/' . $_img['atid'];
					}		
				}
			}
		}
		return $record;
	}


	/**
	 * [insert_multi 插入图片数据]
	 * @param  [type] $data [description]
	 * @return [type]       [description]
	 */
	public function insert_multi($data) {
		
		if (!$this->_d->insert_multi($data)) {
			$this->_set_error('_ERR_SIGN_IMG');

			return false;
		}

		return true;
	}


	/**
	 * [list_by_conds 根据条件获取信息]
	 * @param  [type] $conds [description]
	 * @return [type]        [description]
	 */
	public function list_by_conds($conds) {
		
		return $this->_d->list_by_conds($conds);
	}


	/**
	 * [upload_fj 图片上传]
	 * @param  [string] $atids          [传入的附件ID字符串]
	 * @param  [array] $result          [传入的数据]
	 * @return [array] $re              [返回的数据]
	 */
	public function upload_fj($atids, $result) {
		
		$data_at = array();
		$re = null;
		if (!empty ($atids)) {
			$atids = explode(',', $atids);
			
			// 构造插入数据
			foreach ($atids as $ids) {
				$data_at [] = array(
					'outid' => $result ['sl_id'],
					'atid' => $ids
				);
			}
			$re = $this->insert_multi($data_at);
		}

		return $re;
	}


}
