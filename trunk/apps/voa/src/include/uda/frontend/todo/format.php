<?php
/**
 * voa_uda_frontend_todo_format
 * 统一数据访问/待办事项应用/数据格式化
 *
 * $Author$
 * $Id$
 */
class voa_uda_frontend_todo_format extends voa_uda_frontend_todo_base {

	public function in_list(&$columns) {
		$columns['_subject'] = rhtmlspecialchars($columns['td_subject']);
		$columns['_stared'] = (int)$columns['td_stared'];
		$columns['_completed'] = (int)$columns['td_completed'];
		$columns['_created'] = rgmdate($columns['td_created'], 'Y-m-d H:i');
		$columns['_updated'] = rgmdate($columns['td_updated'], 'Y-m-d H:i');
		$columns['_exptime'] = rgmdate($columns['td_exptime'], 'Y-m-d H:i');
		$columns['_calltime'] = rgmdate($columns['td_calltime'], 'm月d日 H:i');

		return true;
	}

	public function in_post(&$columns) {
		$columns['_subject'] = rhtmlspecialchars($columns['td_subject']);
		$columns['_stared'] = (int)$columns['td_stared'];
		$columns['_completed'] = (int)$columns['td_completed'];
		$columns['_created'] = rgmdate($columns['td_created'], 'Y-m-d H:i:s');
		$columns['_updated'] = rgmdate($columns['td_updated'], 'Y-m-d H:i:s');
		$columns['_exptime'] = rgmdate($columns['td_exptime']);
		$columns['_calltime'] = rgmdate($columns['td_calltime']);

		return true;
	}

	/**
	 * 格式化待办主表数据
	 * @param array $todo
	 * @return boolean
	 */
	public function main(&$todo) {
		/** 发起时间 */
		$todo['_created'] = rgmdate($todo['td_created'], 'Y-m-d H:i');
		/** 截止时间 */
		$todo['_exptime'] = rgmdate($todo['td_exptime'], 'u');
		/** 通知时间 */
		$todo['_calltime'] = rgmdate($todo['td_calltime'], 'u');
		/** 是否置顶 */
		$todo['_stared'] = $todo['td_stared'];
		/** 是否完成 */
		$todo['_completed'] = $todo['td_completed'];
		/** 标题 */
		$todo['_subject'] = rhtmlspecialchars($todo['td_subject']);

		return true;
	}

	/**
	 * 格式化待办/回复详情
	 * @param array $data
	 */
	public function todo_post(&$data) {
		$data['td_subject'] = rhtmlspecialchars($data['td_subject']);
		$data['td_created'] = rgmdate($data['td_created'], 'u');
		return true;
	}

	/**
	 * 格式化待办/回复详情
	 * @param array $data
	 */
	public function todo_edit(&$data) {
		$data['td_subject'] = rhtmlspecialchars($data['td_subject']);
		$data['td_updated'] = rgmdate($data['td_updated'], 'u');
		return true;
	}
}
