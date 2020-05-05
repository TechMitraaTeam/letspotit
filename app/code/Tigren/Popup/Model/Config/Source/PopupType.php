<?php
/**
 * @copyright Copyright (c) 2016 www.tigren.com
 */

namespace Tigren\Popup\Model\Config\Source;

class PopupType implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => 'default', 'label' => __('Default')],
            ['value' => 'newsletter', 'label' => __('Newsletter')],
            ['value' => 'contact-form', 'label' => __('Contact Form')]
        ];
    }
}