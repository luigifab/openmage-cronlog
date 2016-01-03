<?php
/**
 * Created W/29/02/2012
 * Updated D/01/06/2014
 * Version 4
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

class Luigifab_Cronlog_Block_Adminhtml_History extends Mage_Adminhtml_Block_Widget_Grid_Container {

	public function __construct() {

		parent::__construct();

		$this->_controller = 'adminhtml_history';
		$this->_blockGroup = 'cronlog';
		$this->_headerText = $this->__('Cron jobs');

		$this->_addButton('config', array(
			'label'   => $this->__('Manage cron jobs'),
			'onclick' => "setLocation('".$this->getUrl('*/cronlog_config/index')."');",
			'class'   => 'go'
		));
	}
}