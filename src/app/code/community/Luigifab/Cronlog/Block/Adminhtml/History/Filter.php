<?php
/**
 * Created V/05/11/2021
 * Updated V/27/03/2022
 *
 * Copyright 2012-2023 | Fabrice Creuzot (luigifab) <code~luigifab~fr>
 * https://github.com/luigifab/openmage-cronlog
 *
 * This program is free software, you can redistribute it or modify
 * it under the terms of the GNU General Public License (GPL) as published
 * by the free software foundation, either version 2 of the license, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but without any warranty, without even the implied warranty of
 * merchantability or fitness for a particular purpose. See the
 * GNU General Public License (GPL) for more details.
 */

class Luigifab_Cronlog_Block_Adminhtml_History_Filter extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Select {

	public function getHtml() {

		$value  = $this->getData('current_in_list') ? '' : $this->getEscapedValue();
		$search = $this->getData('search_messages');

		return str_replace('<select', '<select onchange="this.parentNode.querySelector(\'input\').value = \'\';"', parent::getHtml()).
			'<div style="display:flex; margin-top:3px; overflow:hidden;">'.
				'<input type="text" name="'.$this->_getHtmlName().'" id="'.$this->_getHtmlId().'_txt" value="'.$value.'" class="input-text no-changes" placeholder="code" style="margin-right:3px; width:50%;" />'.
				'<input type="text" name="'.$this->_getHtmlName().'_msg" id="'.$this->_getHtmlId().'_msg" value="'.$search.'" class="input-text no-changes" placeholder="message" style="width:50%;" />'.
			'</div>';
	}

	public function getCondition() {

		if (is_null($this->getValue()))
			return null;

		if ($this->getData('current_in_list'))
			return ['eq' => $this->getValue()];

		return ['like' => '%'.$this->getValue().'%'];
	}
}