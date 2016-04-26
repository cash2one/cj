<?php
/**
 * project.php
 * 任务应用 错误代码库
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_errcode_api_project {

	const NEW_SUBJECT_NULL = '30000:任务名称不能为空';
	const NEW_BEGINTIME_NULL = '30001:任务开始时间不能为空';
	const NEW_ENDTIME_NULL = '30002:任务结束时间不能为空';
	const NEW_TIME_ERROR = '30003:结束时间必须大于开始时间';
	const NEW_BEGINTIME_SET_ERROR = '30004:任务开始时间必须大于当前时间%s';
	const NEW_ENDTIME_SET_ERROR = '30005:任务结束时间必须大于当前时间%s';
	const NEW_PROJECT_UIDS_NULL = '30006:参加任务人员不能为空';
	const NEW_FALIED_ID_NULL = '30007:新增任务失败';
	const NEW_FAILED_DB = '30008:任务新增失败';

	const LIST_UNDEFINED_ACTION = '30009:未知的动作(%s)';
	const LIST_UNDEFINED_FUNCTION = '30010:未知的动作指定(%s)';

	const VIEW_NOT_EXISTS = '30011:指定任务不存在或已删除（%s）';
	const VIEW_NO = '30012:您没有权限查看当前进度';

	const CLOSED_NOT_EXISTS = '30013:指定任务不存在或已删除';
	const CLOSED_NO = '30014:您没有权限关闭此任务(%s - %s)';
	const CLOSED_OVER = '30015:任务已关闭不能再次关闭';

	const PROGRESS_NOT_EXISTS = '30016:指定任务不存在或已删除';
	const PROGRESS_NO = '30017:您没有权限查看当前进度';
	const PROGRESS_MESSAGE_NULL = '30018:进展描述信息不能为空';
	const PROGRESS_ERROR_DB = '30019:任务进度更新失败';
	const PROGRESS_VALUE_ERROR = '30020:错误的进度值（%s％）';

	const ADVANCED_NOT_EXISTS = '30021:指定任务不存在或已删除';
	const ADVANCED_NO = '30022:您没有权限进行此操作';
	const ADVANCED_MESSAGE_NULL = '30023:推进消息不能为空';
	const ADVANCED_UIDS_NULL = '30024:任务人员不能为空';
	const ADVANCED_NEW_UIDS_NULL = '30025:任务人员不能为空';
	const ADVANCED_FAILED = '30026:项目推进失败';

	const DELETE_FILE_NULL = '30027:请指定待删除的文件';

	const COUNT_TYPE_UNDEFINED = '30028:未定义的类型（%s）';
	const COUNT_NOT_EXISTS = '30029:指定任务不存在或已被删除';
	const COUNT_NO = '30030:您没有权限查看当前任务';

	const UPLOAD_COUNT_TOO_SHORT = '30031:至少要求上传 %s 张图片，您上传了 %s 张';
	const UPLOAD_COUNT_TOO_MUCH = '30032:最多只允许上传 %s 张图片，您已上传了 %s 张';

}
