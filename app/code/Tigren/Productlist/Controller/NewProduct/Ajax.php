<?php
/**
 * @copyright Copyright (c) 2017 www.tigren.com
 */
namespace Tigren\Productlist\Controller\NewProduct;

use Magento\Framework\App\Action\Context;
use Magento\Catalog\Api\CategoryRepositoryInterface;

class Ajax  extends \Magento\Framework\App\Action\Action
{
    protected $_resultJsonFactory;
    protected $_resultPageFactory;
    /**
     * @var CategoryRepositoryInterface
     */
    protected $_categoryRepository;
    public function __construct(
        Context $context,
        CategoryRepositoryInterface $categoryRepository,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_categoryRepository = $categoryRepository;
        parent::__construct($context);
    }
    public function execute()
    {
        $response = [];
        $html = '';
        if ($this->getRequest()->getParam('category_id')) {
            try {
                $category = $this->_categoryRepository->get($this->getRequest()->getParam('category_id'));
                $resultPage = $this->_resultPageFactory->create();
                $block = $resultPage->getLayout()->createBlock('Tigren\Productlist\Block\Item\NewProduct')->setCategory($category)->setTemplate('Tigren_Productlist::newproducts.phtml')->toHtml();
                $response = [
                    'html' => $block,
                    'msg' => 'success',
                ];
            } catch (NoSuchEntityException $e) {
                $category = null;
                $response = [
                    'html' => '',
                    'msg' => 'failed',
                ];
            }
        }
        return $this->_resultJsonFactory->create()->setData($response);
    }
}