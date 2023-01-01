<?php
/**
 * Created S/22/08/2015
 * Updated D/06/11/2022
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

class Luigifab_Cronlog_Block_Adminhtml_Config_Comment extends Mage_Adminhtml_Block_System_Config_Form_Field {

	public function render(Varien_Data_Form_Element_Abstract $element) {

		if (str_contains($element->getHtmlId(), 'cronlog')) {
			$html = parent::render($element);
			$html = str_replace(['{{', '}}'], ['<a href="'.$this->getUrl('*/system_config/edit', ['section' => 'system']).'">', '</a>'], $html);
		}
		else {
			$html = parent::render($element);
			$html = str_replace(['{{', '}}'], ['<a href="'.$this->getUrl('*/system_config/edit', ['section' => 'cronlog']).'">', '</a>'], $html);

			if (!empty(Mage::getStoreConfig('cronlog/general/lifetime')))
				$html = str_replace('<input', '<input disabled="disabled"', $html);
		}

		return $html;
	}
}