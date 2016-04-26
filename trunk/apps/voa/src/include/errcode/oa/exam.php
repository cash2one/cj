<?php
/**
 * 新闻公告
 * voa_errcode_oa_news
 * OA travel 相关 呼叫错误码
 * 使用7位编码
 *
 * $Author$
 * $Id$
 */

class voa_errcode_oa_exam {

	const NAME_ERROR = '3806001:题库名称不合法';
	const TYPE_ERROR = '3806002:题目类型不合法';
	const ORDERBY_ERROR = '3806003:排序值不合法';
	const SCORE_ERROR = '3806004:分数值不合法';
	const TITLE_ERROR = '3806005:题目名称不合法';
	const OPTIONS_ERROR = '3806006:选项内容不合法';
	const ANSWER_ERROR = '3806007:答案不合法';
	const ANSWER_EMPTY = '3806008:选择题选项不能为空';
	const DELETE_TI_FAILED = '3806009:题目删除失败';
	const TIKU_ID_ERROR = '3806010:题库ID不合法';
	const DELETE_TIKU_FAILED = '3806011:题库删除失败';
	const PAPER_NAME_ERROR = '3806012:试卷名称不合法';
	const PAPER_TYPE_ERROR = '3806013:试卷类型不合法';
	const PAPER_TIKU_ID_ERROR = '3806014:题库ID不合法';
	const PAPER_RULES_ERROR = '3806014:抽题规则不合法';
	const PAPER_DETAIL_TI_ID_ERROR = '3806015:题目ID不合法';
	const PAPER_COVER_ID_ERROR = '3806016:封面图片ID不合法';
	const PAPER_BEGIN_TIME_ID_ERROR = '3806017:考试开始时间不合法';
	const PAPER_END_TIME_ID_ERROR = '3806018:考试结束时间不合法';
	const PAPER_INTRO_ERROR = '3806019:考试说明不合法';
	const PAPER_TIME_ERROR = '3806020:考试时间不合法';
}
