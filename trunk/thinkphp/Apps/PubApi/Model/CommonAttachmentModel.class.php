<?php
/**
 * @Author: ppker
 * @Date:   2015-08-24 16:41:49
 * @Last Modified by:   ChangYi
 * @Last Modified time: 2015-09-29 16:18:51
 */

namespace PubApi\Model;

class CommonAttachmentModel extends AbstractModel {

	/** 图片文件 */
	const MEDIA_TYPE_IMAGE = 1;
	/** 音频文件 */
	const MEDIA_TYPE_VOICE = 2;
	/** 视频文件 */
	const MEDIA_TYPE_VIDEO = 3;
	/** 普通文件 */
	const MEDIA_TYPE_FILE = 99;

	/** 正常 */
	const STATUS_NORMAL = 1;
	/** 临时文件 */
	const STATUS_TEMP = 2;
	/** 已删除 */
	const STATUS_REMOVE = 3;

	// 构造方法
	public function __construct() {

		parent::__construct();
		// 字段前缀
		$this->prefield = 'at_';
	}
}
