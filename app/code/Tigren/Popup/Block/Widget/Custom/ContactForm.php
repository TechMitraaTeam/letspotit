<?php
/**
 * @copyright Copyright (c) 2016 www.tigren.com
 */

namespace Tigren\Popup\Block\Widget\Custom;

class ContactForm extends \Tigren\Popup\Block\Widget\AbstractPopup
{
    /**
     * @return string
     */
    public function getContactusFormActionUrl()
    {
        return $this->getUrl('contact/index/post', ['_secure' => true]);
    }

    /**
     * Preparing global layout
     *
     * You can redefine this method in child classes for changing layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $captchaBlock = $this->getLayout()
            ->createBlock('Magento\Captcha\Block\Captcha')
            ->setFormId('contact_us')
            ->setImgWidth(230)
            ->setImgHeight(50);

        $this->setChild('form.additional.info', $captchaBlock);
    }
}