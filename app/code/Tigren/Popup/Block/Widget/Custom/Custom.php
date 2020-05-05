<?php
/**
 * @copyright Copyright (c) 2016 www.tigren.com
 */

namespace Tigren\Popup\Block\Widget\Custom;

class Custom extends \Tigren\Popup\Block\Widget\AbstractPopup
{
    /**
     * @var \Magento\Cms\Model\Template\FilterProvider
     */
    protected $_filterProvider;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

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
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        array $data = []
    )
    {
        parent::__construct($context, $popupHelper, $customerSession, $data);
        $this->_filterProvider = $filterProvider;
        $this->_storeManager = $context->getStoreManager();
    }

    /**
     * @return string
     */
    public function getCustomContentHtml()
    {
        $storeId = $this->_storeManager->getStore()->getId();
        $html = $this->_filterProvider->getBlockFilter()->setStoreId($storeId)->filter($this->escapeHtml($this->getData('popup_content')));
        return $html;
    }
}