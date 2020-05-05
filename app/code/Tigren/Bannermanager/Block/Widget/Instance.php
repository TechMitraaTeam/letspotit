<?php
/**
 * @copyright Copyright (c) 2016 www.tigren.com
 */

namespace Tigren\Bannermanager\Block\Widget;

class Instance extends \Magento\Framework\View\Element\Template implements \Magento\Widget\Block\BlockInterface
{
    /**
     * template for all image banner.
     */
    const DISPLAYTYPE_ALL_IMAGE_TEMPLATE = 'type/all_image.phtml';

    /**
     * template for random banner.
     */
    const DISPLAYTYPE_RANDOM_TEMPLATE = 'type/random.phtml';

    /**
     * template for slider banner.
     */
    const DISPLAYTYPE_SLIDER_TEMPLATE = 'type/slide/slider.phtml';

    /**
     * template for slider with description banner.
     */
    const DISPLAYTYPE_SLIDER_WITH_DESCRIPTION_TEMPLATE = 'type/slide/slider_with_description.phtml';

    /**
     * template for Basic Slider customDirectionNav.
     */
    const BASIC_SLIDE_CUSTOM_DIRECTION_NAV_TEMPLATE = 'type/slide/custom_direction_nav.phtml';

    /**
     * template for Basic Slider customDirectionNav.
     */
    const MIN_MAX_RANGES_TEMPLATE = 'type/slide/min_max_ranges.phtml';

    /**
     * template for Basic Carousel.
     */
    const BASIC_CAROUSEL_TEMPLATE = 'type/slide/carousel.phtml';

    /**
     * template for fade banner.
     */
    const DISPLAYTYPE_FADE_TEMPLATE = 'type/fade.phtml';

    /**
     * template for fade with description banner.
     */
    const DISPLAYTYPE_FADE_WITH_DESCRIPTION_TEMPLATE = 'type/fade_with_description.phtml';

    /**
     * Date conversion model.
     *
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_stdlibDateTime;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * banner helper.
     *
     * @var \Tigren\Bannermanager\Helper\Data
     */
    protected $_bannerHelper;

    /**
     * @var \Tigren\Bannermanager\Model\ResourceModel\Banner\CollectionFactory
     */
    protected $_bannerCollectionFactory;

    /**
     * scope config.
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * stdlib timezone.
     *
     * @var \Magento\Framework\Stdlib\DateTime\Timezone
     */
    protected $_stdTimezone;


    /**
     * @var Session
     */
    protected $_customerSession;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Tigren\Bannermanager\Model\ResourceModel\Banner\CollectionFactory $bannerCollectionFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $stdlibDateTime,
        \Magento\Customer\Model\Session $customerSession,
        \Tigren\Bannermanager\Helper\Data $bannerHelper,
        \Magento\Framework\Stdlib\DateTime\Timezone $_stdTimezone,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->_stdlibDateTime = $stdlibDateTime;
        $this->_bannerHelper = $bannerHelper;
        $this->_storeManager = $context->getStoreManager();
        $this->_customerSession = $customerSession;
        $this->_bannerCollectionFactory = $bannerCollectionFactory;
        $this->_scopeConfig = $context->getScopeConfig();
        $this->_stdTimezone = $_stdTimezone;

    }

    public function getBannerHtmlId()
    {
        $htmlId = 'mb-banner-' . $this->getData('unique_id');
        return $htmlId;
    }

    public function isShowTitle()
    {
        return false;
    }

    /**
     * get first banner.
     *
     * @return \Tigren\Bannermanager\Model\Banner
     */
    public function getFirstBannerItem()
    {
        return $this->getBannerCollection()
            ->setPageSize(1)
            ->setCurPage(1)
            ->getFirstItem();
    }

    /**
     * get banner collection of slider.
     *
     * @return \Tigren\Bannermanager\Model\ResourceModel\Banner\Collection
     */
    public function getBannerCollection()
    {
        $bannerIds = $this->getData('banner_id');
        $bannerIdsArr = explode(',', $bannerIds);
        $dateTimeNow = $this->_stdTimezone->date()->format('Y-m-d H:i:s');

        $bannerCollection = $this->_bannerCollectionFactory->create()
            ->addFieldToFilter('banner_id', ['in' => $bannerIdsArr])
            ->addFieldToFilter('is_active', 1)
            ->addFieldToFilter('start_time', [['to' => $dateTimeNow], ['start_time', 'null' => '']])
            ->addFieldToFilter('end_time', [['gteq' => $dateTimeNow], ['end_time', 'null' => '']])
            ->setOrder('sort_order', 'ASC');

        if ($this->getData('display_type') == 2) {
            $bannerCollection->getSelect()->order('rand()');
            $bannerCollection->setPageSize(1);
        }


        return $bannerCollection;
    }

    /**
     * get banner image url.
     *
     * @param \Tigren\Bannermanager\Model\Banner $banner
     *
     * @return string
     */
    public function getBannerImageUrl(\Tigren\Bannermanager\Model\Banner $banner)
    {
        return $this->_bannerHelper->getImageUrl($banner->getBannerImage());
    }

    /**
     * get flexslider html id.
     *
     * @return string
     */
    public function getBannerItemHtmlId()
    {
        return 'tigren-bannermanager-banner-' . $this->getData('unique_id') . $this->_stdlibDateTime->gmtTimestamp();
    }

    public function getMinImages()
    {
        return $this->getData('min_items');
    }

    public function getMaxImages()
    {
        return $this->getData('max_items');
    }

    protected function _toHtml()
    {
        if ($this->getData('is_active') && $this->_isValidCustomer()) {
            $template = $this->getTypeTemplate();
            $this->setTemplate($template);
            return parent::_toHtml();
        }

        return '';
    }

    /**
     * @return boolean
     */
    protected function _isValidCustomer()
    {
        $customerGroups = explode(',', $this->getData('customer_group'));

        if (in_array((int)$this->_customerSession->getCustomerGroupId(), $customerGroups)) {
            return true;
        }

        return false;
    }

    public function getTypeTemplate()
    {
        $displayType = $this->getData('display_type');
        if ($displayType) {
            switch ($displayType) {
                case '1':
                    $typeTemplate = self::DISPLAYTYPE_ALL_IMAGE_TEMPLATE;
                    break;
                case '2':
                    $typeTemplate = self::DISPLAYTYPE_RANDOM_TEMPLATE;
                    break;
                case '3':
                    $typeTemplate = self::DISPLAYTYPE_SLIDER_TEMPLATE;
                    break;
                case '4':
                    $typeTemplate = self::DISPLAYTYPE_SLIDER_WITH_DESCRIPTION_TEMPLATE;
                    break;
                case '5':
                    $typeTemplate = self::BASIC_SLIDE_CUSTOM_DIRECTION_NAV_TEMPLATE;
                    break;
                case '6':
                    $typeTemplate = self::MIN_MAX_RANGES_TEMPLATE;
                    break;
                case '7':
                    $typeTemplate = self::BASIC_CAROUSEL_TEMPLATE;
                    break;
                case '8':
                    $typeTemplate = self::DISPLAYTYPE_FADE_TEMPLATE;
                    break;
                case '9':
                    $typeTemplate = self::DISPLAYTYPE_FADE_WITH_DESCRIPTION_TEMPLATE;
                    break;
                default:
                    $typeTemplate = self::DISPLAYTYPE_SLIDER_TEMPLATE;
                    break;
            }
            return $typeTemplate;
        }

    }


}
