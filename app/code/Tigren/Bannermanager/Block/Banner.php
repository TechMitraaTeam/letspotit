<?php
/**
 * @copyright Copyright (c) 2016 www.tigren.com
 */

namespace Tigren\Bannermanager\Block;

class Banner extends \Magento\Framework\View\Element\Template
{
    /**
     * banner template
     * @var string
     */
    protected $_template = 'banner.phtml';

    /**
     * Registry object.
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * block collecion factory.
     *
     * @var \Tigren\Bannermanager\Model\ResourceModel\Block\CollectionFactory
     */
    protected $_blockCollectionFactory;

    /**
     * scope config.
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

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

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Tigren\Bannermanager\Model\ResourceModel\Block\CollectionFactory $blockCollectionFactory
     * @param \Tigren\Bannermanager\Model\ResourceModel\Banner\CollectionFactory $bannerCollectionFactory
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param \Magento\Framework\Stdlib\DateTime\Timezone $_stdTimezone
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Stdlib\DateTime\Timezone $_stdTimezone,
        \Magento\Customer\Model\Session $customerSession,
        \Tigren\Bannermanager\Model\ResourceModel\Block\CollectionFactory $blockCollectionFactory,
        \Tigren\Bannermanager\Model\ResourceModel\Banner\CollectionFactory $bannerCollectionFactory,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->_coreRegistry = $coreRegistry;
        $this->_stdTimezone = $_stdTimezone;
        $this->_customerSession = $customerSession;
        $this->_blockCollectionFactory = $blockCollectionFactory;

        $this->_scopeConfig = $context->getScopeConfig();
        $this->_storeManager = $context->getStoreManager();
    }

    /**
     * set position for banner block.
     *
     * @param mixed string|array $position
     */
    public function setPosition($position)
    {

        $currentStoreId = 0;
        if ($store = $this->_storeManager->getStore()) {
            $currentStoreId = $store->getId();
        }

        $dateTimeNow = $this->_stdTimezone->date()->format('Y-m-d H:i:s');

        $blockCollection = $this->_blockCollectionFactory->create()
            ->addFieldToFilter('block_position', $position)
            ->addFieldToFilter('from_date', [['to' => $dateTimeNow], ['from_date', 'null' => '']])
            ->addFieldToFilter('to_date', [['gteq' => $dateTimeNow], ['to_date', 'null' => '']])
            ->addFieldToFilter('is_active', 1)
            ->setStoreFilter($currentStoreId);

        $currentCategoryId = 0;
        if ($category = $this->_coreRegistry->registry('current_category')) {
            $currentCategoryId = $category->getEntityId();
        }


        foreach ($blockCollection as $key => $block) {

            $customerGroup = explode(',', $block->getCustomerGroupIds());
            if (!in_array((int)$this->_customerSession->getCustomerGroupId(), $customerGroup)) {
                $blockCollection->removeItemByKey($key);
                continue;
            }

            if ($currentCategoryId) {
                $filterCategoryIds = explode(',', $block->getCategory());
                if ($block->getCategoryType() == 2) {
                    //all categories except filterCategoryIds
                    if (in_array($currentCategoryId, $filterCategoryIds)) {
                        $blockCollection->removeItemByKey($key);
                    }
                } else if ($block->getCategoryType() == 3) {
                    //specific categoryIds
                    if (!in_array($currentCategoryId, $filterCategoryIds)) {
                        $blockCollection->removeItemByKey($key);
                    }
                }
            }
        }

        $this->appendChildBlockBlocks($blockCollection);

        return $this;
    }

    /**
     * add child block banner.
     *
     * @param \Tigren\Bannermanager\Model\ResourceModel\Block\Collection $blockCollection [description]
     *
     * @return \Tigren\Bannermanager\Block\Banner [description]
     */
    public function appendChildBlockBlocks(
        \Tigren\Bannermanager\Model\ResourceModel\Block\Collection $blockCollection
    )
    {
        foreach ($blockCollection as $block) {
            $this->append(
                $this->getLayout()->createBlock(
                    'Tigren\Bannermanager\Block\BannerItem'
                )->setBlockId($block->getId())
            );
        }

        return $this;
    }
}
