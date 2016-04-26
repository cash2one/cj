<?php
/**
 * voa_errcode_api_meeting
 * API 接口通信录错误码定义
 * 约定错误码均以1开头的5位数字
 * 每个错误码以常量定义，格式为“errcode:errmsg”
 * errcode为唯一的整型
 * errmsg为错误信息，可使用%s做为变量
 * 一旦提供文档，错误码errcode不允许变更！！
 * $Author$
 * $Id$
 */
class voa_errcode_api_meeting {

	const NEW_DATE_NULL = '90301:会议日期不能空';
	const NEW_BEGIN_NULL = '90302:会议开始时间不能为空';
	const NEW_END_NULL = '90303:会议结束时间不能为空';

	const NEW_TIME_ERROR = '90304:时间设置错误';
	const NEW_MRID_NULL = '90305:会议室不能为空';
	const NEW_SUBJECT_NULL = "90306:会议主题不能为空";
	const NEW_JOINUIDS_NULL = "90307:会议参与人不能为空";

	const SUBJECT_TOO_SHORT = "90308:主题太短";
	const MESSAGE_TOO_SHORT = "90309:内容太短";
	const ROOM_ID_INVALID = "90310:会议室不存在";
	const JOIN_UIDS_INVALID = "90311:请选择参会人";
	const MEETING_NEW_ERROR = "90312:参议发布失败";
	const LIST_UNDEFINED_ACTION = "90313:非法操作";
	const VIEW_NOT_EXISTS = "90314:不存在或是删除";
	const NO_PRIVILEGE = "90315:没有权限查看";
	const MEETING_NOT_EXIST = "90316:会议不存在或已删除";
	const NEW_MESSAGE_NULL = "90317:缺席原因不能为空";
	const MEETING_ABSENCE_SUCCEED = "90318:已确认缺席";

}
