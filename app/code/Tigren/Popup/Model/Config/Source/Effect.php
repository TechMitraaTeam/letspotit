<?php
/**
 * @copyright Copyright (c) 2016 www.tigren.com
 */

namespace Tigren\Popup\Model\Config\Source;

class Effect implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => 'fadein', 'label' => __('Fade In')],
            ['value' => 'slidetoright', 'label' => __('Slide to Right')],
            ['value' => 'slidetoleft', 'label' => __('Slide to Left')],
            ['value' => 'slidedown', 'label' => __('Slide Down')],
            ['value' => 'slideup', 'label' => __('Slide Up')],
        ];
    }
}