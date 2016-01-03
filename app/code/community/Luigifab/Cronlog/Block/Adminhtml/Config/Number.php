<?php
/**
 * Created S/27/06/2015
 * Updated J/26/11/2015
 * Version 5
 *
 * Copyright 2012-2016 | Fabrice Creuzot (luigifab) <code~luigifab~info>
 * https://redmine.luigifab.info/projects/magento/wiki/cronlog
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

class Luigifab_Cronlog_Block_Adminhtml_Config_Number extends Mage_Adminhtml_Block_System_Config_Form_Field {

	protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element) {

		if (true) {
			$resource = Mage::getSingleton('core/resource');
			$element->setValue($resource->getConnection('core_read')->fetchOne('
				SELECT table_rows FROM information_schema.TABLES WHERE table_name = "'.$resource->getTableName('cron_schedule').'"
			'));

			return '<span id="'.$element->getHtmlId().'">'.$this->__('~%d (very approximate)', round(intval($element->getValue()), -2, PHP_ROUND_HALF_UP)).'</span>';
		}
		else {
			return '<span id="'.$element->getHtmlId().'">'.Mage::getResourceModel('cron/schedule_collection')->getSize().'</span>';
		}
	}
}