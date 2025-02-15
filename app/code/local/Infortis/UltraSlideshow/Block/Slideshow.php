<?php

class Infortis_UltraSlideshow_Block_Slideshow extends Mage_Core_Block_Template
{
	protected $_isPredefinedHomepageSlideshow = false;
	protected $_slides = NULL;
	protected $_banners = NULL;
	protected $_cacheKeyArray = NULL;
	protected $_coreHelper;

	/**
	 * There are two options to provide slides for a slideshow:
	 * - all slides are contained in a single static block
	 * - or each slide is in a separate static block
	 * In the second case, this variable is set to true.
	 */
	protected $_hasMultiBlockIds = false;

	/**
	 * Initialize block's cache
	 */
	protected function _construct()
	{
		parent::_construct();

		$this->_coreHelper = Mage::helper('ultraslideshow');

		$this->addData(array(
			'cache_lifetime'    => 99999999,
			'cache_tags'        => array(Mage_Catalog_Model_Product::CACHE_TAG),
		));
	}

	/**
	 * Get Key pieces for caching block content
	 *
	 * @return array
	 */
	public function getCacheKeyInfo()
	{
		if (NULL === $this->_cacheKeyArray)
		{
			$this->_cacheKeyArray = array(
				'INFORTIS_ULTRASLIDESHOW',
				Mage::app()->getStore()->getId(),
				Mage::getDesign()->getPackageName(),
				Mage::getDesign()->getTheme('template'),
				Mage::getSingleton('customer/session')->getCustomerGroupId(),
				'template' => $this->getTemplate(),
				'name' => $this->getNameInLayout(),
				(int)Mage::app()->getStore()->isCurrentlySecure(),
				implode(".", $this->getSlideIds()),
				$this->getAdditionalBannerId(),
				$this->_isPredefinedHomepageSlideshow,
			);
		}
		return $this->_cacheKeyArray;
	}

	/**
	 * Create unique block id for frontend
	 *
	 * @return string
	 */
	public function getFrontendHash()
	{
		return md5(implode("+", $this->getCacheKeyInfo()));
	}

	/**
	 * Get array of slides (static blocks) identifiers. Blocks will be displayed as slides.
	 *
	 * @return array
	 */
	public function getSlideIds()
	{
		$blockIds = array();
		if ($this->_slides)
		{
			return $this->_slides;
		}
		else // No predefined slides
		{
			// Get slides from parameter
			$blockIds = $this->getParamStaticBlockIds();
			if (empty($blockIds))
			{
				// If this is predefined slideshow, get slides from module config
				if ($this->_isPredefinedHomepageSlideshow)
				{
					$blockIds = $this->getConfigStaticBlockIds();
				}
			}
		}

		// Retrieved slides ids can be saved for further processing
		$this->_slides = $blockIds;
		return $this->_slides;
	}

	/**
	 * Get array of static block identifiers from parameter
	 *
	 * @return array
	 */
	protected function getParamStaticBlockIds()
	{
		$blockIds = $this->getSlides(); // Param: slides
		if ($blockIds === NULL) // Param not set
		{
			return array();
		}

		return $this->getStaticBlockIds($blockIds);
	}

	/**
	 * Get array of static block identifiers from module config
	 *
	 * @return array
	 */
	protected function getConfigStaticBlockIds()
	{
		$blockIds = $this->_coreHelper->getCfg('general/blocks');
		if (empty($blockIds))
		{
			return array();
		}

		return $this->getStaticBlockIds($blockIds);
	}

	/**
	 * Get array of static block identifiers
	 *
	 * @param string
	 * @return array
	 */
	protected function getStaticBlockIds($blockIds)
	{
		// Check if ids are comma separated
		if (strpos($blockIds, ",") !== false)
		{
			$this->_hasMultiBlockIds = true;
			return explode(",", str_replace(" ", "", $blockIds));
		}
		else
		{
			$this->_hasMultiBlockIds = false;
			$blockIds = trim($blockIds);

			// Important: even when single block id was found, return it inside an array
			return array($blockIds);
		}
	}

	/**
	 * Get id of the static block which contains additional banners for the slideshow
	 *
	 * @return string
	 */
	public function getAdditionalBannerId()
	{
		$bid = '';
		if ($this->_banners)
		{
			$bid = $this->_banners;
		}
		else // No predefined banners
		{
			// Get banners from parameter
			$bid = $this->getBanner(); // Param: banner (note there is no "s" at the end)
			if ($bid === NULL) // Param not set
			{
				// If this is predefined slideshow, get banners from module config
				if ($this->_isPredefinedHomepageSlideshow)
				{
					// Get banners from module config
					$bid = $this->_coreHelper->getCfg('banners/banners');
				}
			}
			$bid = trim($bid);
		}

		// Retrieved banner id can be saved for further processing
		$this->_banners = $bid;
		return $this->_banners;
	}

	/**
	 * Return value of a variable
	 *
	 * @return bool
	 */
	public function isMultiBlock()
	{
		return $this->_hasMultiBlockIds;
	}

	/**
	 * Add slide ids
	 *
	 * @param string
	 * @return Infortis_UltraSlideshow_Block_Slideshow
	 */
	public function addSlides($ids)
	{
		$this->_slides = $ids;
		return $this;
	}

	/**
	 * Add banner id
	 *
	 * @param string
	 * @return Infortis_UltraSlideshow_Block_Slideshow
	 */
	public function addBanner($id)
	{
		$this->_banners = $id;
		return $this;
	}

	/**
	 * Set/Unset as predefined slideshow (e.g. for homepage)
	 *
	 * @param string
	 * @return Infortis_UltraSlideshow_Block_Slideshow
	 */
	public function setPredefined($value)
	{
		$this->_isPredefinedHomepageSlideshow = $value;
		return $this;
	}

	/**
	 * Check if slideshow is set as predefined
	 *
	 * @return bool
	 */
	public function isPredefined()
	{
		return $this->_isPredefinedHomepageSlideshow;
	}

	/**
	 * Get CSS style string with margins for slideshow wrapper
	 *
	 * @return string
	 */
	public function getMarginStyles()
	{
		//Slideshow margin
		$slideshowMarginStyleProperties = '';

		$marginTop = intval($this->_coreHelper->getCfg('general/margin_top'));
		if ($marginTop !== 0)
		{
			$slideshowMarginStyleProperties .= "margin-top:{$marginTop}px;";
		}

		$marginBottom = intval($this->_coreHelper->getCfg('general/margin_bottom'));
		if ($marginBottom !== 0)
		{
			$slideshowMarginStyleProperties .= "margin-bottom:{$marginBottom}px;";
		}

		if ($slideshowMarginStyleProperties)
		{
			return 'style="' . $slideshowMarginStyleProperties . '"';
		}
	}

	/**
	 * Get CSS classes for navigation buttons
	 *
	 * @return string
	 */
	public function getNavButtonsClasses()
	{
		$classes = '';

		$navButtons = $this->getNavButtons(); // Param: nav_buttons
		if ($navButtons === NULL) // Param not set
		{
			$navButtons = $this->_coreHelper->getCfg('navigation/nav_buttons');
		}

		// If navigation buttons enabled, get other parameters and create CSS classes
		if (!empty($navButtons))
		{
			$classes = 'slider-arrows2';
		}

		return $classes;
	}

	/**
	 * Get CSS classes for pagination
	 *
	 * @return string
	 */
	public function getPaginationClasses()
	{
		$classes = '';

		$pagination = $this->getPagination(); // Param: pagination
		if ($pagination === NULL) // Param not set
		{
			$pagination = $this->_coreHelper->getCfg('navigation/pagination');
		}
		
		// If pagination enabled, get other parameters and create CSS classes
		if (!empty($pagination))
		{
			$paginationPosition = $this->getPaginationPosition(); // Param: pagination_position
			if ($paginationPosition === NULL) // Param not set
			{
				$paginationPosition = $this->_coreHelper->getCfg('navigation/pagination_position');
			}

			$classes = 'slider-pagination' . $pagination; // Concat with number of pagination type

			if ($paginationPosition)
			{
				$classes .= ' pagination-pos-' . $paginationPosition; // Concat with name of pagination position
			}
		}

		return $classes;
	}

	/**
	 * Get CSS classes for slideshow which determine:
	 * width (in grid units), position of slider (left or right), 
	 * gutter between slider and additional banners, show/hide banners on mobile devices.
	 *
	 * @return array
	 */
	public function getSlideshowLayoutClasses()
	{
		// All keys have to be created because they are used on frontend
		$classes = array();
		$classes['sliderPosition'] = '';
		$classes['sliderGrid'] = '';
		$classes['bannersGrid'] = '';
		$classes['hideBanners'] = '';

		// Get position of additional banners
        $bannersPosition = $this->getBannerPosition(); // Param: banner_position (note there is no "s" at the end of "banner")
        if ($bannersPosition === NULL) // Param not set
        {
            // If this is predefined slideshow, get value from module config
            if ($this->_isPredefinedHomepageSlideshow)
            {
                $bannersPosition = $this->_coreHelper->getCfg('banners/position');
            }
            else
            {
                $bannersPosition = 'right'; // Set default
            }
        }

        // Get gutter between slider and additional banners
        $bannersHaveGutter = $this->getGutter(); // Param: gutter
        if ($bannersHaveGutter === NULL) // Param not set
        {
            // If this is predefined slideshow, get value from module config
            if ($this->_isPredefinedHomepageSlideshow)
            {
                $bannersHaveGutter = $this->_coreHelper->getCfg('banners/gutter');
            }
            else
            {
                $bannersHaveGutter = true; // Set default
            }
        }

        // Create CSS classes
        if ($bannersPosition == 'left') // Banners at the left, slideshow at the right
        {
            $classes['sliderPosition'] = '_right';
            $classes['sliderGrid'] = 'col-sm-9 no-gutter';
            $classes['bannersGrid'] = 'col-sm-3';
            // // Old grid classes:
            // $classes['sliderGrid'] = 'grid12-9 no-right-gutter';
            // $classes['bannersGrid'] = 'grid12-3 no-left-gutter';

            if ($bannersHaveGutter)
            {
                $classes['bannersGrid'] .= ' no-left-gutter';
            }
            else
            {
                $classes['bannersGrid'] .= ' no-gutter';
            }
        }
        else // Set default: slideshow at the left, banners at the right
        {
            $classes['sliderGrid'] = 'col-sm-9 no-gutter';
            $classes['bannersGrid'] = 'col-sm-3';
            // // Old grid classes:
            // $classes['sliderGrid'] = 'grid12-9 no-left-gutter';
            // $classes['bannersGrid'] = 'grid12-3 no-right-gutter';

            if ($bannersHaveGutter)
            {
                $classes['bannersGrid'] .= ' no-right-gutter';
            }
            else
            {
                $classes['bannersGrid'] .= ' no-gutter';
            }
        }

        // If this is predefined home page slideshow, optionally hide banners on mobile devices. "Yes" is the default.
        if ($this->_isPredefinedHomepageSlideshow)
        {
            if ($this->_coreHelper->getCfg('banners/hide'))
            {
                $classes['hideBanners'] = 'hidden-xs';
            }
        }
        else
        {
            // TODO: currently in slideshow added via shortcode the banners are always hidden. Review this later.
            $classes['hideBanners'] = 'hidden-xs';
        }

		return $classes;
	}

	/**
	 * @deprecated
	 * Get HTML of the static block which contains additional banners for the slideshow
	 *
	 * @return string
	 */
	public function getBannersHtml()
	{
		$bid = '';
		if ($this->_banners)
		{
			$bid = $this->_banners;
		}
		else //No predefined banners
		{
			//Get banners from parameter
			$bid = $this->getBanner(); //param: banner
			if ($bid === NULL) //Param not set
			{
				//If this is predefined slideshow, get banners from module config
				if ($this->_isPredefinedHomepageSlideshow)
				{
					//Get banners from module config
					$bid = $this->_coreHelper->getCfg('banners/banners');
				}
			}
		}

		//If banner id specified
		$bid = trim($bid);
		if ($bid)
		{
			return $this->getLayout()->createBlock('cms/block')->setBlockId($bid)->toHtml();
		}
		return '';
	}
}
