<?php
/**
 * 超级报表错误
 * $Author$
 * $Id$
 */

class voa_errcode_api_superreport {

	const UID_ERROR = '80000:用户ID错误';
	const SID_ERROR = '80001:报表ID错误';
	const COMMENT_ERROR = '80002：评论不能为空';
	const UID_BLANK = '80003:用户ID不能为空';
	const SID_BLANK = '80004:报表ID不能为空';
	const SPID_ERROR = '8005:门店ID错误';
	const YEAR_ERROR = '8006:年份错误';
	const MONTH_ERROR = '8007:月份错误';
	const FIELDVALUE_ERROR = '8008:字段值错误';
	const MONTHLYREPORT_ERROR = '8009:月报数据不存在';
	const ADD_COMMENT_ERROR = '8010:写入评论失败';
	const DAILYREPORT_ERROR = '8011:日报数据不存在';
	const DATE_ERROR = '8012:日期错误';
	const DAILYREPORT_FORMAT_ERROR = '8013:日报数据格式错误';
	const NO_TEMPLATE_ERROR ="8014: 还没有设置模板";
	const NO_RIGHT_ERROR ="8015: 没有操作权限";
	const NO_MONTH_ERROR ="8016: 数据出错，月报不存在";
	const DELETE_REPORT_FAILED = "8017: 删除报表失败";
	const LIMIT_ERROR = "8018: 每页显示数量错误";
	const ADD_TEMPLATE_ERROR = '8019:写入模板失败';
	const DUMPLICATE_ERROR = '8020:今日日报已存在，不能重复发送';

}
