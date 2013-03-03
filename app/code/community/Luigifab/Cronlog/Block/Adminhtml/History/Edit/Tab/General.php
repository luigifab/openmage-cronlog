<?php
/**
 * Created D/10/02/2013
 * Updated S/02/03/2013
 * Version 3
 *
 * Copyright 2013 | Fabrice Creuzot (luigifab) <code~luigifab~info>
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

class Luigifab_Cronlog_Block_Adminhtml_History_Edit_Tab_General extends Mage_Adminhtml_Block_Widget_Form {

	protected function _prepareForm() {

		$sessionData = Mage::getSingleton('adminhtml/session')->getFormData();

		$form = new Varien_Data_Form();
		$this->setForm($form);

		$fieldset = $form->addFieldset('general_form', array(
			'legend'   => $this->__('Job')
		));

		$fieldset->addField('job_code', 'select', array(
			'label'    => $this->__('Code'),
			'name'     => 'job_code',
			'class'    => 'required-entry',
			'required' => true,
			'values'   => Mage::getModel('cronlog/source_jobs')->toOptionArray()
		));

		$fieldset->addField('scheduled_at', 'select', array(
			'label'    => $this->__('Scheduled into'),
			'name'     => 'scheduled_at',
			'class'    => 'required-entry',
			'required' => true,
			'values'   => Mage::getModel('cronlog/source_date')->toOptionArray()
		));

		if (is_array($sessionData) && !empty($sessionData)) {

			$form->setValues(array(
				'job_code' => trim($sessionData['job_code']),
				'scheduled_at' => trim($sessionData['scheduled_at'])
			));

			Mage::getSingleton('adminhtml/session')->unsFormData();
		}

		return parent::_prepareForm();
	}
}