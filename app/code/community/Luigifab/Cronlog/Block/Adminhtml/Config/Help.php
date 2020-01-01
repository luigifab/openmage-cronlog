<?php
/**
 * Created V/23/05/2014
 * Updated J/26/09/2019
 *
 * Copyright 2012-2020 | Fabrice Creuzot (luigifab) <code~luigifab~fr>
 * https://www.luigifab.fr/magento/cronlog
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

class Luigifab_Cronlog_Block_Adminhtml_Config_Help extends Mage_Adminhtml_Block_Abstract implements Varien_Data_Form_Element_Renderer_Interface {

	public function render(Varien_Data_Form_Element_Abstract $element) {

		$msg = $this->checkRewrites();
		if ($msg !== true)
			return sprintf('<p class="box">%s %s <span style="float:right;"><a href="https://www.%s">%3$s</a> | ⚠ IPv6</span></p>'.
				'<p class="box" style="margin-top:-5px; color:white; background-color:#E60000;"><strong>%s</strong><br />%s</p>',
				'Luigifab/Cronlog', $this->helper('cronlog')->getVersion(), 'luigifab.fr/magento/cronlog',
				$this->__('INCOMPLETE MODULE INSTALLATION'),
				$this->__('There is conflict (<em>%s</em>).', $msg));

		return sprintf('<p class="box">%s %s <span style="float:right;"><a href="https://www.%s">%3$s</a> | ⚠ IPv6</span></p>',
			'Luigifab/Cronlog', $this->helper('cronlog')->getVersion(), 'luigifab.fr/magento/cronlog');
	}

	private function checkRewrites() {

		$rewrites = [
			['model', 'cron/observer'],
			['model', 'cron/schedule']
		];

		foreach ($rewrites as $rewrite) {
			if (($rewrite[0] == 'model') && (mb_stripos(get_class(Mage::getModel($rewrite[1])), 'luigifab') === false))
				return $rewrite[1];
			else if (($rewrite[0] == 'block') && (mb_stripos(get_class(Mage::getBlockSingleton($rewrite[1])), 'luigifab') === false))
				return $rewrite[1];
		}

		return true;
	}
}