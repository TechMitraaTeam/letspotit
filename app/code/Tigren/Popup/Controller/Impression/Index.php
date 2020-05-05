<?php
/**
 * @copyright Copyright (c) 2016 www.tigren.com
 */

namespace Tigren\Popup\Controller\Impression;

use Magento\Framework\App\Action\Action;

class Index extends Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Tigren\Popup\Helper\Data
     */
    protected $_popupHelper;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param array $searchModules
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\UrlFactory $urlFactory,
        \Tigren\Popup\Helper\Data $popupHelper
    )
    {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_date = $date;
        $this->_popupHelper = $popupHelper;
    }

    /**
     * Global Search Action
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $data = $this->getRequest()->getParams();

        $data['customer_id'] = $this->customerSession->getId();
        $data['created_time'] = $this->_date->gmtDate();

        $result = ['success' => false];

        try {
            $statisticModel = $this->_objectManager->create('Tigren\Popup\Model\Statistic');
            $statisticModel->setData($data)->save();

            $result['success'] = true;
        } catch (\Exception $e) {
            // do nothing
        }

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($result);
    }
}
