<?php
/**
 * voa_errcode_api_attachment
 * 附件接口相关错误代码
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_errcode_api_attachment {

	const UPLOAD_TYPE_UNDEFINED = '90001:未定义的上传类型';
	const UPLOAD_DATA_EMPTY = '90002:没有任务数据被上传';
	const UPLOAD_UDA_ERROR = '90003:上传文件发生错误（%s）';
	const UPLOAD_DATA_NULL = '90004:上传文件发生错误';

	const DELETE_ERROR = '90005:删除文件发生错误（%s）';
	const DELETE_NULL = '90006:请指定要删除的文件';
	const DELETE_ID_NULL = '90007:待删除的文件不存在或已被删除';

	const SERVERID_NULL = '90008:serverid 必须提供';
	const UPLOAD_GET_AID_ERROR = '90009:上传文件发生错误';
}
