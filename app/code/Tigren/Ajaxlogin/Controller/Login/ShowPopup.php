<?php
/**
 * @copyright Copyright (c) 2017 www.tigren.com
 */

namespace Tigren\Ajaxlogin\Controller\Login;

class ShowPopup extends \Magento\Framework\App\Action\Action
{
    protected $_ajaxLoginHelper;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Tigren\Ajaxsuite\Helper\Data $ajaxsuiteHelper,
        \Tigren\Ajaxlogin\Helper\Data $ajaxLoginHelper
    )
    {
        parent::__construct($context);
        $this->_ajaxLoginHelper = $ajaxLoginHelper;
    }

    public function execute()
    {
        $result = [];
        $params = $this->_request->getParams();

        if (!empty($params['isLogin'])) {
            try {
                $htmlPopup = $this->_ajaxLoginHelper->getLoginPopupHtml();
                $result['success'] = true;
                $result['html_popup'] = $htmlPopup;
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('You can\'t login right now.'));
                $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
                $result['success'] = false;
            }
        }

        if (!empty($params['isRegister'])) {
            try {
                $htmlPopup = $this->_ajaxLoginHelper->getRegisterPopupHtml();
                $result['success'] = true;
                $result['html_popup'] = $htmlPopup;
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('You can\'t login right now.'));
                $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
                $result['success'] = false;
            }
        }

        if (!empty($params['isForgotPassword'])) {
            try {
                $htmlPopup = $this->_ajaxLoginHelper->getForgotPasswordPopupHtml();
                $result['success'] = true;
                $result['html_popup'] = $htmlPopup;
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('You can\'t login right now.'));
                $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
                $result['success'] = false;
            }
        }
        $this->getResponse()->representJson(
            $this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode($result)
        );
    }
}