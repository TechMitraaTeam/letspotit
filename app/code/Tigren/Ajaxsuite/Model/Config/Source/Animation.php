<?php
/**
 * @copyright Copyright (c) 2017 www.tigren.com
 */

namespace Tigren\Ajaxsuite\Model\Config\Source;

class Animation implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => 'fade', 'label' => __('Fade In')],
            ['value' => 'slide_top', 'label' => __('Slide from Top')],
            ['value' => 'slide_bottom', 'label' => __('Slide from Bottom')],
            ['value' => 'slide_left', 'label' => __('Slide from Left')],
            ['value' => 'slide_right', 'label' => __('Slide from Right')]
        ];
    }
}
