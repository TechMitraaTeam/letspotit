<?php
/**
 *
 */
namespace Tigren\Popup\Controller\Newsletter;

use Magento\Customer\Api\AccountManagementInterface as CustomerAccountManagement;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Url as CustomerUrl;
use Magento\Framework\App\Action\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Newsletter\Model\SubscriberFactory;

/**
 * Class NewAction
 */
class Index extends \Magento\Newsletter\Controller\Subscriber\NewAction
{
    /**
     * @var CustomerAccountManagement
     */
    protected $customerAccountManagement;

    protected $resultJsonFactory;

    protected $resultPageFactory;

    protected $_subscriber;

    /**
     * Initialize dependencies.
     *
     * @param Context $context
     * @param SubscriberFactory $subscriberFactory
     * @param Session $customerSession
     * @param StoreManagerInterface $storeManager
     * @param CustomerUrl $customerUrl
     * @param CustomerAccountManagement $customerAccountManagement
     */
    public function __construct(
        Context $context,
        SubscriberFactory $subscriberFactory,
        Session $customerSession,
        StoreManagerInterface $storeManager,
        CustomerUrl $customerUrl,
        CustomerAccountManagement $customerAccountManagement,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Newsletter\Model\Subscriber $subscriber
    ) {
        $this->customerAccountManagement = $customerAccountManagement;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->_subscriber= $subscriber;
        parent::__construct(
            $context,
            $subscriberFactory,
            $customerSession,
            $storeManager,
            $customerUrl,
            $customerAccountManagement
        );
    }

    /**
     * Retrieve available Order fields list
     *
     * @return array
     */
    public function aroundExecute($subject, $procede)
    {
        $response = [];
        if ($this->getRequest()->isPost() && $this->getRequest()->getPost('email')) {
            $email = (string)$this->getRequest()->getPost('email');

            try {
                $this->validateEmailFormat($email);
                $this->validateGuestSubscription();
                $checkSubscriber = $this->_subscriber->loadByEmail($email);
                if ($checkSubscriber->isSubscribed()){
                    $response = [
                        'status' => 'ERROR',
                        'msg' => $this->messageContent('This email address is already assigned to another user.'),
                    ];
                    return $this->resultJsonFactory->create()->setData($response);
                }
                $status = $this->_subscriberFactory->create()->subscribe($email);
                if ($status == \Magento\Newsletter\Model\Subscriber::STATUS_NOT_ACTIVE) {
                    $response = [
                        'status' => 'OK',
                        'msg' => $this->messageContent('The confirmation request has been sent.'),
                    ];
                } else {
                    $response = [
                        'status' => 'OK',
                        'msg' => $this->messageContent('Thank you for your subscription.'),
                    ];
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $response = [
                    'status' => 'ERROR',
                    'msg' => $this->messageContent(__('There was a problem with the subscription: %1', $e->getMessage())),
                ];
            } catch (\Exception $e) {
                $response = [
                    'status' => 'ERROR',
                    'msg' => $this->messageContent('Something went wrong with the subscription.'),
                ];
            }
        }

        return $this->resultJsonFactory->create()->setData($response);
    }

    protected function messageContent($message){
         $html = "<div class=\"mb-popup-overlay\" style=\"display: block; opacity: 1;\"></div>";
         $html .= "<div class=\"mb-popup-wrapper mb-popup-custom-newsletter message-popup-newsletter\">
                    <div class=\"mb-popup-border\">
                        <div class=\"mb-popup-title\"><strong>Message</strong></div>
                        <div class=\"mb-popup-full mb-newsletter-container\">
                            <div class=\"mb-newsletter-description\"><span>$message</span></div>
                        </div>
                    </div>
                    <a class=\"close\"></a>
                   </div>";
        return $html;
    }

}