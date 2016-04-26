<?php
class voa_uda_cyadmin_content_article_base extends voa_uda_cyadmin_content_base {

	protected function _before_action($action) {
		if (!parent::_before_action($action)) {
			return false;
		}
		return true;
	}
}
