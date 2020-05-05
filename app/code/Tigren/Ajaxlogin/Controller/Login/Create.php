<?php

/**
 * @copyright Copyright (c) 2017 www.tigren.com
 */

namespace Tigren\Ajaxlogin\Controller\Login;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\Data\RegionInterfaceFactory;
use Magento\Customer\Controller\AbstractAccount;
use Magento\Customer\Model\CustomerExtractor;
use Magento\Customer\Model\Metadata\FormFactory;
use Magento\Customer\Model\Registration;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Customer\Model\Url as CustomerUrl;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\UrlFactory;
use Magento\Newsletter\Model\SubscriberFactory;

class Create extends AbstractAccount
{

    protected $accountManagement;
    protected $formFactory;
    protected $subscriberFactory;
    protected $regionDataFactory;
    protected $addressDataFactory;
    protected $registration;
    protected $customerUrl;
    protected $escaper;
    protected $customerExtractor;
    protected $urlModel;
    protected $dataObjectHelper;
    protected $customerSession;
    protected $jsonHelper;
    protected $_ajaxLoginHelper;
    private $cookieMetadataManager;
    private $cookieMetadataFactory;

    public function __construct(
        Context $context,
        CustomerSession $customerSession,
        AccountManagementInterface $accountManagement,
        FormFactory $formFactory,
        SubscriberFactory $subscriberFactory,
        RegionInterfaceFactory $regionDataFactory,
        AddressInterfaceFactory $addressDataFactory,
        CustomerUrl $customerUrl,
        Registration $registration,
        Escaper $escaper,
        CustomerExtractor $customerExtractor,
        UrlFactory $urlFactory,
        DataObjectHelper $dataObjectHelper,
        JsonHelper $jsonHelper,
        \Tigren\Ajaxlogin\Helper\Data $ajaxLoginHelper
    )
    {

        $this->customerSession = $customerSession;
        $this->accountManagement = $accountManagement;
        $this->formFactory = $formFactory;
        $this->subscriberFactory = $subscriberFactory;
        $this->regionDataFactory = $regionDataFactory;
        $this->addressDataFactory = $addressDataFactory;
        $this->customerUrl = $customerUrl;
        $this->registration = $registration;
        $this->escaper = $escaper;
        $this->customerExtractor = $customerExtractor;
        $this->urlModel = $urlFactory->create();
        $this->dataObjectHelper = $dataObjectHelper;

        $this->jsonHelper = $jsonHelper;
        $this->_ajaxLoginHelper = $ajaxLoginHelper;
        parent::__construct($context);
    }

    /**
     * Create customer account action
     *
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        $result = array();
        if (!$this->registration->isAllowed()) {
            $result['error'] = 'Registration is not allow.';
        } else if ($this->customerSession->isLoggedIn()) {
            $result['error'] = 'You have already logged in.';
        } else {
            $this->customerSession->regenerateId();
            try {
                $address = $this->extractAddress();
                $addresses = $address === null ? [] : [$address];

                $customer = $this->customerExtractor->extract('customer_account_create', $this->_request);
                $customer->setAddresses($addresses);

                $password = $this->getRequest()->getParam('password');
                $confirmation = $this->getRequest()->getParam('password_confirmation');
                /*
                 * Not this case, because validated in form front-end.
                 */
                if (!$this->checkPasswordConfirmation($password, $confirmation)) {
                    $result['error'] = __('Please make sure your passwords match.');
                }
                if (empty($result['error'])) {
                    $customer = $this->accountManagement
                        ->createAccount($customer, $password);

                    if ($this->getRequest()->getParam('is_subscribed', false)) {
                        $this->subscriberFactory->create()->subscribeCustomerById($customer->getId());
                    }

                    $this->_eventManager->dispatch(
                        'customer_register_success', ['account_controller' => $this, 'customer' => $customer]
                    );

                    $confirmationStatus = $this->accountManagement->getConfirmationStatus($customer->getId());
                    if ($confirmationStatus === AccountManagementInterface::ACCOUNT_CONFIRMATION_REQUIRED) {
                        $email = $this->customerUrl->getEmailConfirmationUrl($customer->getEmail());
                        $result['success'] = __(
                            'You must confirm your account. Please check your email for the confirmation link or <a href="%1">click here</a> for a new link.', $email
                        );
                        $result['reload'] = false;
                    } else {
                        $result['success'] = __('You have created account successfully.');
                        $result['reload'] = true;
                        $this->customerSession->setCustomerDataAsLoggedIn($customer);
                    }
                    if ($this->getCookieManager()->getCookie('mage-cache-sessid')) {
                        $metadata = $this->getCookieMetadataFactory()->createCookieMetadata();
                        $metadata->setPath('/');
                        $this->getCookieManager()->deleteCookie('mage-cache-sessid', $metadata);
                    }
                }
            } catch (StateException $e) {
                $url = $this->urlModel->getUrl('customer/account/forgotpassword');
                $result['error'] = __(
                    'There is already an account with this email address. If you are sure that it is your email address, <a href="%1">click here</a> to get your password and access your account.', $url
                );
            } catch (InputException $e) {
                $result['error'] = $this->escaper->escapeHtml($e->getMessage());
            } catch (\Exception $e) {
                $result['error'] = $this->escaper->escapeHtml($e->getMessage());
            }
        }

        if (!empty($result['error'])) {
            $htmlPopup = $this->_ajaxLoginHelper->getErrorMessageRegisterPopupHtml();
            $result['html_popup'] = $htmlPopup;
        } else {
            $htmlPopup = $this->_ajaxLoginHelper->getSuccessMessageRegisterPopupHtml();
            $result['html_popup'] = $htmlPopup;
        }
        $this->getResponse()->representJson($this->jsonHelper->jsonEncode($result));
    }

    /**
     * Add address to customer during create account
     *
     * @return AddressInterface|null
     */
    protected function extractAddress()
    {
        if (!$this->getRequest()->getPost('create_address')) {
            return null;
        }

        $addressForm = $this->formFactory->create('customer_address', 'customer_register_address');
        $allowedAttributes = $addressForm->getAllowedAttributes();

        $addressData = [];

        $regionDataObject = $this->regionDataFactory->create();
        foreach ($allowedAttributes as $attribute) {
            $attributeCode = $attribute->getAttributeCode();
            $value = $this->getRequest()->getParam($attributeCode);
            if ($value === null) {
                continue;
            }
            switch ($attributeCode) {
                case 'region_id':
                    $regionDataObject->setRegionId($value);
                    break;
                case 'region':
                    $regionDataObject->setRegion($value);
                    break;
                default:
                    $addressData[$attributeCode] = $value;
            }
        }
        $addressDataObject = $this->addressDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $addressDataObject, $addressData, '\Magento\Customer\Api\Data\AddressInterface'
        );
        $addressDataObject->setRegion($regionDataObject);

        $addressDataObject->setIsDefaultBilling(
            $this->getRequest()->getParam('default_billing', false)
        )->setIsDefaultShipping(
            $this->getRequest()->getParam('default_shipping', false)
        );

        return $addressDataObject;
    }

    protected function checkPasswordConfirmation($password, $confirmation)
    {
        return $password == $confirmation;
    }

    private function getCookieManager()
    {
        if (!$this->cookieMetadataManager) {
            $this->cookieMetadataManager = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\Stdlib\Cookie\PhpCookieManager::class
            );
        }
        return $this->cookieMetadataManager;
    }

    private function getCookieMetadataFactory()
    {
        if (!$this->cookieMetadataFactory) {
            $this->cookieMetadataFactory = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory::class
            );
        }
        return $this->cookieMetadataFactory;
    }

}
