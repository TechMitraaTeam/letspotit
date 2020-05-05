<?php
/**
 * @copyright Copyright (c) 2016 www.tigren.com
 */

namespace Tigren\Popup\Helper;

/**
 * Popup data helper
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Popup instance type
     */
    const INSTANCE_TYPES = [
        'Tigren\Popup\Block\Widget\CmsBlock',
        'Tigren\Popup\Block\Widget\Custom\Custom',
        'Tigren\Popup\Block\Widget\Custom\Newsletter',
        'Tigren\Popup\Block\Widget\Custom\Login',
        'Tigren\Popup\Block\Widget\Custom\ContactForm',
        'Tigren\Popup\Block\Widget\Custom\CookieCompliance'
    ];

    /**
     * Currently selected store ID if applicable
     *
     * @var int
     */
    protected $_storeId;

    /**
     *
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    protected $_backendUrl;

    /**
     * @var \Magento\Framework\DB\Helper
     */
    protected $_resourceHelper;

    /**
     * @var CustomerSession
     */
    protected $_customerSession;

    /**
     * @var \Magento\MediaStorage\Model\File\Uploader
     */
    protected $_uploaderFactory;

    /**
     * @var Filesystem
     */
    protected $_fileSystem;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;

    /**
     * @var \Magento\MediaStorage\Helper\File\Storage\Database
     */
    protected $_fileStorageDatabase;

    /**
     * category collection factory.
     *
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $_categoryCollectionFactory;

    protected $_jsonEncoder;

    protected $_impressionDataCache = [];

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Framework\Registry $coreRegistry
     * @param CustomerSession $customerSession
     * @param \Magento\Framework\DB\Helper $resourceHelper
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Backend\Model\UrlInterface $backendUrl,
        \Magento\MediaStorage\Helper\File\Storage\Database $fileStorageDatabase,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\DB\Helper $resourceHelper,
        \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory,
        \Magento\Framework\Filesystem $fileSystem,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
    )
    {
        $this->_resource = $resource;
        $this->_storeManager = $storeManager;
        $this->_coreRegistry = $coreRegistry;
        $this->_customerSession = $customerSession;
        $this->_backendUrl = $backendUrl;
        $this->_categoryCollectionFactory = $categoryCollectionFactory;
        $this->_resourceHelper = $resourceHelper;
        $this->_uploaderFactory = $uploaderFactory;
        $this->_fileSystem = $fileSystem;
        $this->_fileStorageDatabase = $fileStorageDatabase;
        $this->_localeDate = $localeDate;
        $this->_jsonEncoder = $jsonEncoder;

        parent::__construct($context);
    }

    /**
     * Set a specified store ID value
     *
     * @param int $store
     * @return $this
     */
    public function setStoreId($store)
    {
        $this->_storeId = $store;
        return $this;
    }

    public function isEnabledPopup()
    {
        return (bool)$this->scopeConfig->getValue(
            'popuppro/general/enabled_popup',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->_storeId
        );
    }

    public function isEnabledStatistics()
    {
        return (boolean)$this->scopeConfig->getValue(
            'popuppro/general/enabled_statistics',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->_storeId
        );
    }

    public function getInstanceByUniqueId($uniqueId)
    {
        $connection = $this->_resource->getConnection();

        $select = $connection->select()
            ->from(
                $connection->getTableName('mb_popup_widget_instance'),
                ['instance_id', 'sort_order']
            )
            ->where('unique_id = ?', $uniqueId);

        return $connection->fetchRow($select);
    }

    public function getPopupImpressions($popup, $type = 'impression_count')
    {
        $impressionData = $this->getPopupImpressionData($popup);

        $count = !empty($impressionData[$type]) ? $impressionData[$type] : 0;
        $countString = $count ? $count : '0';

        $percentString = '';

        if ($type !== 'impression_count') {
            $percentString = ' (0%)';

            if (!empty($impressionData['impression_count'])) {
                $percent = $count / $impressionData['impression_count'] * 100;
                $percentString = ' (' . round($percent, 2) . '%' . ')';
            }
        }

        return $countString . $percentString;
    }

    public function getPopupImpressionData($popup)
    {
        if (isset($this->_impressionDataCache[$popup->getId()])) {
            return $this->_impressionDataCache[$popup->getId()];
        }

        $connection = $this->_resource->getConnection();

        $select = $connection->select()
            ->from(
                ['pwi' => $connection->getTableName('mb_popup_widget_instance')],
                []
            )
            ->joinInner(
                ['ps' => $connection->getTableName('mb_popup_statistic')],
                'ps.instance_id = pwi.instance_id',
                [
                    'value' => 'ps.impression_type',
                    'count' => 'COUNT(ps.statistic_id)'
                ]
            )
            ->where('pwi.instance_id = ?', $popup->getId())
            ->group('ps.impression_type');

        $impressionData = $connection->fetchPairs($select);

        $impresionCount = 0;
        foreach ($impressionData as $key => $value) {
            $impresionCount += $value;
        }

        $impressionData['impression_count'] = $impresionCount;

        $this->_impressionDataCache[$popup->getId()] = $impressionData;

        return $this->_impressionDataCache[$popup->getId()];
    }
}
