<?php

class Infortis_Infortis_Model_System_Config_Source_Layout_Screen_Width_WideCustom
{
    public function toOptionArray()
    {
		return array(
			array('value' => '1920',	'label' => Mage::helper('infortis')->__('Breakpoint 3XL (1920 px)')),
			array('value' => '1680',	'label' => Mage::helper('infortis')->__('Breakpoint 2XL (1680 px)')),
			array('value' => '1440',	'label' => Mage::helper('infortis')->__('Breakpoint XL (1440 px)')),
			array('value' => '1200',	'label' => Mage::helper('infortis')->__('Breakpoint L (1200 px)')),
			array('value' => '992',		'label' => Mage::helper('infortis')->__('Breakpoint M (992 px)')),
			array('value' => '768',		'label' => Mage::helper('infortis')->__('Breakpoint S (768 px)')),
			array('value' => 'full',	'label' => Mage::helper('infortis')->__('Full width')),
			array('value' => 'custom',	'label' => Mage::helper('infortis')->__('Custom width...'))
        );
    }
}
