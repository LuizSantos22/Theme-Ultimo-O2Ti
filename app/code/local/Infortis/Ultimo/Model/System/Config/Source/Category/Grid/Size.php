<?php

class Infortis_Ultimo_Model_System_Config_Source_Category_Grid_Size
{
	public function toOptionArray()
	{
		return array(
			array('value' => 'xl',	'label' => Mage::helper('infortis')->__('XL')),
			array('value' => 'l',	'label' => Mage::helper('infortis')->__('L')),
			array('value' => '',	'label' => Mage::helper('infortis')->__('M (default)')),
			array('value' => 's',	'label' => Mage::helper('infortis')->__('S')),
			array('value' => 'xs',	'label' => Mage::helper('infortis')->__('XS')),
		);
	}
}
