<?php
/**
 * @copyright Copyright (c) 2016 www.tigren.com
 */

namespace Tigren\Bannermanager\Block;

use Tigren\Bannermanager\Model\Block as BlockModel;

/**
 * Block item.
 * @category Magestore
 * @package  Magestore_Bannerslider
 * @module   Bannerslider
 * @author   Magestore Developer
 */
class BannerCustom extends \Magento\Framework\View\Element\Template
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
    const DISPLAYTYPE_SLIDER_TEMPLATE = 'type/slider.phtml';

    /**
     * template for slider with description banner.
     */
    const DISPLAYTYPE_SLIDER_WITH_DESCRIPTION_TEMPLATE = 'type/slider_with_description.phtml';

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
     * block factory.
     *
     * @var \Tigren\Bannermanager\Model\BlockFactory
     */
    protected $_blockFactory;

    /**
     * block model.
     *
     * @var \Tigren\Bannermanager\Model\Block
     */
    protected $_block;

    /**
     * block id.
     *
     * @var int
     */
    protected $_blockId;

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
     * [__construct description].
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Tigren\Bannermanager\Model\ResourceModel\Banner\CollectionFactory $bannerCollectionFactory
     * @param \Tigren\Bannermanager\Model\BlockFactory $blockFactory
     * @param BlockModel $block
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $stdlibDateTime
     * @param \Tigren\Bannermanager\Helper\Data $bannerHelper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Stdlib\DateTime\Timezone $_stdTimezone
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Tigren\Bannermanager\Model\ResourceModel\Banner\CollectionFactory $bannerCollectionFactory,
        \Tigren\Bannermanager\Model\BlockFactory $blockFactory,
        BlockModel $block,
        \Magento\Framework\Stdlib\DateTime\DateTime $stdlibDateTime,
        \Tigren\Bannermanager\Helper\Data $bannerHelper,
        \Magento\Framework\Stdlib\DateTime\Timezone $_stdTimezone,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->_blockFactory = $blockFactory;
        $this->_block = $block;
        $this->_stdlibDateTime = $stdlibDateTime;
        $this->_bannerHelper = $bannerHelper;
        $this->_storeManager = $context->getStoreManager();
        $this->_bannerCollectionFactory = $bannerCollectionFactory;
        $this->_scopeConfig = $context->getScopeConfig();
        $this->_stdTimezone = $_stdTimezone;
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
     * get block max width.
     *
     * @return integer
     */
    public function getBlockMaxWidth()
    {
        return $this->_block->getBlockMaxWidth();
    }

    /**
     * get flexslider html id.
     *
     * @return string
     */
    public function getBannerItemHtmlId()
    {
        return 'tigren-bannermanager-banner-' . $this->getBlock()->getId() . $this->_stdlibDateTime->gmtTimestamp();
    }

    /**
     * @return \Tigren\Bannermanager\Model\Block
     */
    public function getBlock()
    {
        return $this->_block;
    }

    /**
     * set slider model.
     *
     * @param \Tigren\Bannermanager\Model\Block $block [description]
     */
    public function setBlock(\Tigren\Bannermanager\Model\Block $block)
    {
        $this->_block = $block;

        return $this;
    }

    /**
     * @return
     */
    protected function _toHtml()
    {
        $blockId = $this->getBlockId();
        if ($blockId) {
            $this->setBlockId($blockId);
        } else {
            return '';
        }

        if (!$this->_block->getId() || $this->_block->getIsActive() === 1 || !$this->getBannerCollection()->getSize()) {
            return '';
        }
        return parent::_toHtml();
    }

    /**
     * set slider Id and set template.
     *
     * @param int $blockId
     */
    public function setBlockId($blockId)
    {
        $this->_blockId = $blockId;

        $block = $this->_blockFactory->create()->load($this->_blockId);
        if ($block->getId()) {
            $this->setBlock($block);

            switch ($block->getDisplayType()) {
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
                    $typeTemplate = self::DISPLAYTYPE_FADE_TEMPLATE;
                    break;
                case '6':
                    $typeTemplate = self::DISPLAYTYPE_FADE_WITH_DESCRIPTION_TEMPLATE;
                    break;
                default:
                    $typeTemplate = self::DISPLAYTYPE_SLIDER_TEMPLATE;
                    break;
            }

            $this->setTemplate($typeTemplate);
        }

        return $this;
    }

    /**
     * get banner collection of slider.
     *
     * @return \Tigren\Bannermanager\Model\ResourceModel\Banner\Collection
     */
    public function getBannerCollection()
    {
        $banners = $this->_block->getBanners();
        $dateTimeNow = $this->_stdTimezone->date()->format('Y-m-d H:i:s');

        $bannerCollection = $this->_bannerCollectionFactory->create()
            ->addFieldToFilter('banner_id', ['in' => $banners])
            ->addFieldToFilter('is_active', 1)
            ->addFieldToFilter('start_time', [['to' => $dateTimeNow], ['start_time', 'null' => '']])
            ->addFieldToFilter('end_time', [['gteq' => $dateTimeNow], ['end_time', 'null' => '']])
            ->setOrder('sort_order', 'ASC');

        if ($this->_block->getDisplayType() == 2) {
            $bannerCollection->getSelect()->order('rand()');
            $bannerCollection->setPageSize(1);
        }

        return $bannerCollection;
    }
}
