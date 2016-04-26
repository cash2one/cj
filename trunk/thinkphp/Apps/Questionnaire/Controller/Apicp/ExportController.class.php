<?php
/**
 * Created by PhpStorm.
 * User: zhoutao
 * Date: 16/3/23
 * Time: 下午2:14
 */

namespace Questionnaire\Controller\Apicp;

class ExportController extends AbstractController {

	/**
	 * 导出试卷
	 * @return bool
	 */
	public function Naire_get() {

		// 生成临时文件
		$html = '';
		$qu_data = $this->_create_html($html);

		$file_name = $qu_data['title'] . rgmdate(NOW_TIME, 'YmdHi') . '.html';
		$file_dir = get_sitedir();
		$file_path = $file_dir . $file_name;
		file_put_contents($file_path, $html);
		// 检查文件是否存在
		if (!file_exists($file_path)) {
			echo "文件找不到";
			exit ();
		} else {
			// 打开文件
			$file = fopen($file_path, "r");
			// 输入文件标签
			Header("Content-type: application/octet-stream");
			Header("Accept-Ranges: bytes");
			Header("Accept-Length: " . filesize($file_path));
			Header("Content-Disposition: attachment; filename=" . $file_name);
			// 输出文件内容
			// 读取文件内容并直接输出到浏览器
			echo fread($file, filesize($file_path));
			fclose($file);

			// 删除临时文件
			@unlink($file_path);
			exit ();
		}
	}

	/**
	 * 生成 Html 代码
	 * @param $html
	 * @return bool
	 */
	protected function _create_html(&$html) {

		// 获取问卷数据
		$qu_id = I('get.qu_id', 0, 'intval');
		if (empty($qu_id)) {
			E('_ERR_NO_EXIST_QUESTIONNAIRE');
			return false;
		}
		$serv_qu = D('Questionnaire/Questionnaire', 'Service');
		$qu_data = $serv_qu->get($qu_id);
		if (empty($qu_data)) {
			E('_ERR_NO_EXIST_QUESTIONNAIRE');
			return false;
		}

		$html .= '<html>';
		$html .= '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
		$html .= '<head><style>';
		$html .= '.title {height: 50px; text-align: center; font-size: 30px; margin: auto;}';
		$html .= '.describe {width: 80%; margin: 0 auto; word-break: break-all;}';
		$html .= '.subject {width: 80%; word-break: break-all; margin: 20px auto; line-height: 30px;}';
		$html .= '</style></head>';
		$html .= '<div class="title">';
		// 标题
		$html .= $qu_data['title'];
		$html .= '</div>';
		$html .= '<div class="describe">';
		// 描述
		$html .= $qu_data['body'];
		$html .= '</div>';
		$html .= '<div class="subject">';
		// 问题
		$this->__deal_subject($html, $qu_data);
		$html .= '</div>';
		$html .= '</html>';

		return $qu_data;
	}

	/**
	 * 处理题目html 代码
	 * @param $html
	 * @param $qu_data
	 * @return bool
	 */
	private function __deal_subject(&$html, $qu_data) {

		$qu_data['field'] = json_decode($qu_data['field'], true);
		$number = 1;
		foreach ($qu_data['field'] as $_field) {

			// 所有允许的类型
			$allow_type = array('number', 'text', 'address', 'date', 'time', 'datetime', 'mobile', 'username', 'email', 'tel', 'radio', 'select', 'checkbox', 'note', 'textarea', 'score');
			if (!in_array($_field['type'], $allow_type)) {
				continue;
			}

			// 是否必填
			if (isset($_field['required']) && $_field['required'] == 'true') {
				$html .= '*';
			}
			// 序号
			if ($_field['type'] != 'note') {
				$html .= $number . '.';
			}

			// 单行文本类型
			$single_text_subject = array('text', 'address', 'date', 'time', 'datetime', 'mobile', 'username', 'email', 'tel');
			// 单选类型
			$single_select_subject = array('radio', 'select');

			// 如果是单行文本类型的表单格式
			if (in_array($_field['type'], $single_text_subject)) {
				$this->__single_text_html($html, $_field);
			// 如果是单选类型的表单格式
			} elseif (in_array($_field['type'], $single_select_subject)) {
				$this->__single_select_html($html, $_field);
			} else {
				switch ($_field['type']) {
					// 多选项
					case 'checkbox':
						$html .= $_field['title'] . '[多选项]';
						if (!empty($_field['min']) && !empty($_field['max'])) {
							$html .= '[请选择' . $_field['min'] . '至' . $_field['max'] . '个选项]';
						} elseif (!empty($_field['min']) && empty($_field['min'])) {
							$html .= '[最少选择' . $_field['min'] . '项]';
						} elseif (empty($_field['min']) && !empty($_field['min'])) {
							$html .= '[最多选择' . $_field['max'] . '项]';
						}
						$html .= '<br/>';
						foreach ($_field['option'] as $_key => $_option) {
							if (empty($_option['value'])) {
								$html .= '<label><input type="checkbox" name="' . $_field['id'] . 'radio">其他</label>______________<br />';
							} else {
								$html .= '<label><input type="checkbox" name="' . $_field['id'] . 'radio">';
								$html .= $_option['value'] . '</label> <br />';
							}
						}
						break;
					// 段落说明
					case 'note':
						$html .= $_field['title'] . '<br />';
						$html .= $_field['placeholder'] . '<br />';
						continue;
						break;
					// 多行文本
					case 'textarea':
						$html .= $_field['title'] . '<br />';
						$html .= '______________' . '<br />';
						$html .= '______________' . '<br />';
						break;
					// 评分
					case 'score':
						$html .= $_field['title'];
						if (!empty($_field['placeholder'])) {
							$html .= '[' . $_field['placeholder'] . ']';
						}
						$html .= '<br />';
						for($i = 1; $i <= $_field['max']; $i ++) {
							$html .= '<label><input type="radio" name="' . $_field['id'] . 'radio">';
							$html .= $i . '分</label> </t>';
						}
						$html .= '<br />';
						break;
					case 'number':
						$html .= $_field['title'];
						if (!empty($_field['placeholder'])) {
							$html .= '[' . $_field['placeholder'] . ']';
						}
						if (!empty($_field['min']) && $_field['min'] > 0 && !empty($_field['max']) && $_field['max'] > 0) {
							$html .= '[请填写' . $_field['min'] . '-' . $_field['max'] . '之间的数字]';
						} elseif (!empty($_field['min']) && $_field['min'] > 0) {
							$html .= '[请填写不小于' . $_field['min'] . '的数字]';
						} elseif (!empty($_field['max']) && $_field['max'] > 0) {
							$html .= '[请填写不大于' . $_field['max'] . '的数字]';
						}
						$html .= '<br />';
						$html .= '______________' . '<br />';
						break;
				}
			}

			// 序号加一
			if ($_field['type'] != 'note') {
				$number ++;
			}
		}

		return true;
	}

	/**
	 * 处理单行文本类型的题目html 代码
	 * @param $html
	 * @param $_field
	 * @return bool
	 */
	private function __single_text_html(&$html, $_field) {

		$html .= $_field['title'];
		if (!empty($_field['placeholder'])) {
			$html .= '[' . $_field['placeholder'] . ']';
		}
		if (!empty($_field['min']) && $_field['min'] > 0 && !empty($_field['max']) && $_field['max'] > 0) {
			$html .= '[请填写' . $_field['min'] . '至' . $_field['max'] . '个字符]';
		} elseif (!empty($_field['min']) && $_field['min'] > 0) {
			$html .= '[请填写最少' . $_field['min'] . '个字符]';
		} elseif (!empty($_field['max']) && $_field['max'] > 0) {
			$html .= '[请填写最多' . $_field['max'] . '个字符]';
		}
		$html .= '<br />';
		$html .= '______________' . '<br />';

		return true;
	}

	/**
	 * 处理单选类型的题目html 代码
	 * @param $html
	 * @param $_field
	 * @return bool
	 */
	private function __single_select_html(&$html, $_field) {

		$html .= $_field['title'] . '[单选项]';
		if (!empty($_field['placeholder'])) {
			$html .= '[' . $_field['placeholder'] . ']';
		}
		$html .= '<br />';
		foreach ($_field['option'] as $_key => $_option) {
			if (empty($_option['value'])) {
				$html .= '<label><input type="radio" name="' . $_field['id'] . 'radio">其他</label>______________<br />';
			} else {
				$html .= '<label><input type="radio" name="' . $_field['id'] . 'radio">';
				$html .= $_option['value'] . '</label> <br />';
			}
		}

		return true;
	}
}
