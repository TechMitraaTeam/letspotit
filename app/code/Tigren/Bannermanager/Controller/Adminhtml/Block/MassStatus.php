<?php
/**
 *
 * Copyright © 2016 Tigren. All rights reserved.
 *
 */
namespace Tigren\Bannermanager\Controller\Adminhtml\Block;

use Tigren\Bannermanager\Model\ResourceModel\Block\CollectionFactory;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Ui\Component\MassAction\Filter;

class MassStatus extends \Tigren\Bannermanager\Controller\Adminhtml\Block\Index
{
    const STATUS_ENABLED = 1;

    const STATUS_DISABLED = 0;


    /**
     * MassActions filter
     *
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @param Action\Context $context
     * @param \Magento\Catalog\Model\Indexer\Product\Price\Processor $productPriceIndexerProcessor
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        PageFactory $resultPageFactory,
        Filter $filter,
        CollectionFactory $collectionFactory
    )
    {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context, $resultPageFactory);
    }

    /**
     * Update product(s) status action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $blockIds = $collection->getAllIds();
        $status = (int)$this->getRequest()->getParam('status');

        try {
            foreach ($collection->getItems() as $block) {
                $block->setIsActive($status);
                $block->save();
            }
            $this->messageManager->addSuccess(__('A total of %1 record(s) have been updated.', count($blockIds)));
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->_getSession()->addException($e, __('Something went wrong while updating the block(s) status.'));
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('bannersmanager/*/');
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Tigren_Bannermanager::block');
    }
}
