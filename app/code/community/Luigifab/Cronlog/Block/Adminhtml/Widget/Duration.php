<?php
/**
 * Created S/26/04/2014
 * Updated D/22/03/2015
 * Version 4
 *
 * Copyright 2012-2015 | Fabrice Creuzot (luigifab) <code~luigifab~info>
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

class Luigifab_Cronlog_Block_Adminhtml_Widget_Duration extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

	public function render(Varien_Object $row) {

		if (!in_array($row->getData('executed_at'), array('', '0000-00-00 00:00:00', null)) &&
		    !in_array($row->getData('finished_at'), array('', '0000-00-00 00:00:00', null))) {

			$data = strtotime($row->getData('finished_at')) - strtotime($row->getData('executed_at'));

			if ($data > 599)
				$data = (($data % 60) > 9) ? intval($data / 60).':'.($data % 60) : intval($data / 60).':0'.($data % 60);
			else if ($data > 59)
				$data = (($data % 60) > 9) ? '0'.intval($data / 60).':'.($data % 60) : '0'.intval($data / 60).':0'.($data % 60);
			else if ($data > 0)
				$data = ($data > 9) ? '00:'.$data : '00:0'.$data;
			else
				$data = '&lt; 1';

			return ($data > 180) ? '<strong>'.$data.'</strong>' : $data;
		}
	}
}