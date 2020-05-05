<?php
/**
 * @copyright Copyright (c) 2016 www.tigren.com
 */

namespace Tigren\Popup\Block\Widget;

class AbstractPopup extends \Magento\Framework\View\Element\Template implements \Magento\Widget\Block\BlockInterface
{
    protected $_popupHelper;

    protected $_popupData;

    /**
     * @var Session
     */
    protected $_customerSession;

    /**
     * Construct
     *
     * @param \Magento\Framework\View\Element\Context $context
     * @param \Magento\Cms\Model\Template\FilterProvider $filterProvider
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Tigren\Popup\Helper\Data $popupHelper,
        \Magento\Customer\Model\Session $customerSession,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->_popupHelper = $popupHelper;
        $this->_customerSession = $customerSession;
    }

    /**
     * @return boolean
     */
    public function isEnabledStatistics()
    {
        return $this->_popupHelper->isEnabledStatistics()
            ? $this->_popupHelper->isEnabledStatistics()
            : 0;
    }

    /**
     * @return string
     */
    public function getImpressionActionUrl()
    {
        return $this->getUrl('popup/impression');
    }

    /**
     * @return integer
     */
    public function getInstanceId()
    {
        if (!$this->_popupData) {
            $this->_popupData = $this->_popupHelper->getInstanceByUniqueId($this->getData('unique_id'));
        }

        return !empty($this->_popupData['instance_id']) ? $this->_popupData['instance_id'] : 0;
    }

    /**
     * @return integer
     */
    public function getInstanceSortOrder()
    {
        if (!$this->_popupData) {
            $this->_popupData = $this->_popupHelper->getInstanceByUniqueId($this->getData('unique_id'));
        }

        return !empty($this->_popupData['sort_order']) ? $this->_popupData['sort_order'] : 0;
    }

    /**
     * @return string
     */
    public function getPopupHtmlId()
    {
        $htmlId = 'mb-popup-' . $this->getData('unique_id');
        return $htmlId;
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->_popupHelper->isEnabledPopup()
            && $this->getData('is_active')
            && $this->_isValidCustomer()
        ) {
            return parent::_toHtml();
        }

        return '';
    }

    /**
     * @return boolean
     */
    protected function _isValidCustomer()
    {
        $customerGroups = explode(',', $this->getData('customer_groups'));

        if (in_array((int)$this->_customerSession->getCustomerGroupId(), $customerGroups)) {
            return true;
        }

        return false;
    }
}