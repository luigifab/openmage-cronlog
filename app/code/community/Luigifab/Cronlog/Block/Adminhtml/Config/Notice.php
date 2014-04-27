<?php
/**
 * Created S/03/03/2012
 * Updated S/26/04/2014
 * Version 5
 *
 * Copyright 2012-2014 | Fabrice Creuzot (luigifab) <code~luigifab~info>
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

class Luigifab_Cronlog_Block_Adminhtml_Config_Notice extends Mage_Adminhtml_Block_Abstract implements Varien_Data_Form_Element_Renderer_Interface {

	public function render(Varien_Data_Form_Element_Abstract $element) {
		return '<tr><td colspan="4"><p style="margin:9px 5px 5px;">'.$this->__('Other options available in <a href="%s">cron jobs configuration</a>.', $this->getUrl('adminhtml/system_config/edit', array('section' => 'system'))).'</p></td></tr>';
	}
}