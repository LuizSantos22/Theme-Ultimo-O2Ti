<?php

class Infortis_Ultimo_Helper_Labels extends Mage_Core_Helper_Abstract
{
	/**
	 * Is "new" label enabled
	 *
	 * @var bool
	 */
	protected $labelNewEnabled = true;

	/**
	 * Is "saved value" label enabled
	 *
	 * @var bool
	 */
	protected $labelSavedValueEnabled = true;

    /**
     * Number of decimal digits to round to
     *
     * @var int
     */
    protected $savedValuePrecision = 2;

	/**
	 * Is "sale" label enabled
	 *
	 * @var bool
	 */
	protected $labelSaleEnabled = true;

	/**
	 * Is custom label enabled
	 *
	 * @var bool
	 */
	protected $labelCustomEnabled = true;

	/**
	 * Custom label attribute code
	 *
	 * @var string
	 */
	protected $customLabelAttributeCode;

	/**
	 * Style of labels - round
	 *
	 * @var bool
	 */
	protected $roundLabels = false;

	/**
	 * Initialize
	 */
	public function __construct()
	{
		$this->labelNewEnabled = Mage::getStoreConfig('ultimo/product_labels/new');
		$this->labelSaleEnabled = Mage::getStoreConfig('ultimo/product_labels/sale');
		$this->labelSavedValueEnabled = Mage::getStoreConfig('ultimo/product_labels/saved_value');
		$this->savedValuePrecision = Mage::getStoreConfig('ultimo/product_labels/saved_value_precision');
		$this->roundLabels = Mage::getStoreConfig('ultimo/product_labels/round_labels');

		// $this->labelCustomEnabled = Mage::getStoreConfig('ultimo/product_labels/custom');
		// if ($this->labelCustomEnabled)
		// {
		//     $this->customLabelAttributeCode = Mage::getStoreConfig('ultimo/product_labels/custom_label_attr_code');
		// }
	}

	/**
	 * Get classes of product labels
	 *
	 * @return string
	 */
	public function getLabelsClasses()
	{
		if ($this->roundLabels)
		{
			return 'round-stickers';
		}

		return '';
	}

	/**
	 * Get product labels HTML
	 *
	 * @return string
	 */
	public function getLabels($product)
	{
		$html = '';

		$showNewLabel = false;
		if ($this->labelNewEnabled)
		{	
			$showNewLabel = $this->isNew($product);
		}

		$showSavedValueLabel = false;
		$savedValue = '';
		if ($this->labelSavedValueEnabled)
		{
			$savedValue = $this->_getSavedPercentage($product);
			if (!empty($savedValue))
			{
				$showSavedValueLabel = true;
			}
		}

		// The "saved value" label excludes the "sale" label, so only if it's not displayed
		// we can check if the "sale" label should be displayed.
		$showSaleLabel = false;
		if ($showSavedValueLabel === false && $this->labelSaleEnabled)
		{
			if ($this->_hasSpecialPrice($product)) //
			{
				$showSaleLabel = true;
			}
		}

		$showCustomLabel = false;
		// $customAttrValue = '';
		// if (!empty($this->customLabelAttributeCode))
		// {
		// 	$customAttrValue = $this->_getCustomAttribute($product);
		// 	if ($customAttrValue !== false)
		// 	{
		// 		$showCustomLabel = true;
		// 	}
		// }

		$hasAnyLabels = ($showNewLabel || $showSavedValueLabel || $showSaleLabel || $showCustomLabel) ? true : false;

		if ($hasAnyLabels)
		{
			$html .= '<span class="sticker-wrapper top-left">'; // Open wrapper
		}

		if ($showNewLabel)
		{
			$html .= '<span class="sticker new">' . $this->__('New') . '</span>';
		}

		if ($showSavedValueLabel)
		{
			$html .= '<span class="sticker sale save">-' . $savedValue . '%</span>';
		}

		if ($showSaleLabel)
		{
			$html .= '<span class="sticker sale">' . $this->__('Sale') . '</span>';
		}

		// if ($showCustomLabel)
		// {
		// 	$html .= '<span class="sticker custom">' . $customAttrValue . '</span>';
		// }

		if ($hasAnyLabels)
		{
			$html .= '</span>'; // Close wrapper
		}
		
		return $html;
	}
	
	/**
	 * Check if product is marked as "new"
	 *
	 * @return bool
	 */
	public function isNew($product)
	{
		// Check if date range is correct
		if ($this->_nowIsBetween($product->getData('news_from_date'), $product->getData('news_to_date')))
		{
			return true;
		}

		return false;
	}

	/**
	 * Check if product has special price and calculate saved value (percentage)
	 *
	 * @return string
	 */
	protected function _getSavedPercentage($product)
	{
		$percentage = '';

		if ($this->_nowIsBetween($product->getData('special_from_date'), $product->getData('special_to_date')))
		{
			$specialPrice = $product->getFinalPrice();
			$regularPrice = $product->getPrice();

            if (empty($regularPrice))
            {
                return 0;
            }

			if ($specialPrice !== $regularPrice)
			{
				$percentage = round((($regularPrice - $specialPrice) / $regularPrice) * 100, $this->savedValuePrecision);
			}
		}

		return $percentage;
	}
	
	/**
	 * Check if product has special price
	 *
	 * @return bool
	 */
	protected function _hasSpecialPrice($product)
	{
		if ($this->_nowIsBetween($product->getData('special_from_date'), $product->getData('special_to_date')))
		{
			$specialPrice = $product->getFinalPrice();
			$regularPrice = $product->getPrice();

			// If date range is correct, also check if final price is different than regular price
			if ($specialPrice !== $regularPrice)
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if product is marked as "on sale" by an attribute
	 *
	 * @return bool
	 */
	protected function _isSaleAttributeTrue($product)
	{
		if ($product->getData('sale'))
		{
			return true;
		}

		return false;
	}

	/**
	 * Get value of a custom attribute
	 *
	 * @return string|bool
	 */
	protected function _getCustomAttribute($product)
	{
		$attr = $product->getResource()->getAttribute($this->customLabelAttributeCode);
		$value = $attr->getFrontend()->getValue($product);
		// $value = $product->getResource()->getAttribute($this->customLabelAttributeCode)->getFrontend()->getValue($product); // Shorter
		// $value = $product->getAttributeText($this->customLabelAttributeCode); // Alt

		if ($value === false || $value === null)
		{
			return false;
		}
		elseif (is_array($value))
		{
			return implode(' ', $value);
		}
		else
		{
			return $value;
		}
	}

	/**
	 * Check if now is in the date range
	 *
	 * @return bool
	 */
	protected function _nowIsBetween($fromDate, $toDate)
	{
		if ($fromDate)
		{
			$fromDate = strtotime($fromDate);
			$toDate = strtotime($toDate);
			$now = strtotime(Mage::app()->getLocale()->date()->setTime('00:00:00')->toString(Varien_Date::DATETIME_INTERNAL_FORMAT));
			
			if ($toDate)
			{
				if ($fromDate <= $now && $now <= $toDate)
					return true;
			}
			else
			{
				if ($fromDate <= $now)
					return true;
			}
		}
		
		return false;
	}

	/**
	 * @deprecated
	 * Check if "sale" label is enabled and if product has special price
	 *
	 * @return bool
	 */
	public function isOnSale($product)
	{
		$specialPrice = $product->getFinalPrice();
		$regularPrice = $product->getPrice();
		
		if ($specialPrice != $regularPrice)
		{
			return $this->_nowIsBetween($product->getData('special_from_date'), $product->getData('special_to_date'));
		}
		else
		{
			return false;
		}
	}
}
