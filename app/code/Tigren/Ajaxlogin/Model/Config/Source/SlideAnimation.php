<?php
/**
 * @copyright Copyright (c) 2017 www.tigren.com
 */

namespace Tigren\Ajaxlogin\Model\Config\Source;

class SlideAnimation implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'show', 'label' => __('Show')],
            ['value' => 'fade_fast', 'label' => __('Fade (Fast)')],
            ['value' => 'fade_medium', 'label' => __('Fade (Medium)')],
            ['value' => 'fade_slow', 'label' => __('Fade (Slow)')],
            ['value' => 'slide_fast', 'label' => __('Slide (Fast)')],
            ['value' => 'slide_medium', 'label' => __('Slide (Medium)')],
            ['value' => 'slide_slow', 'label' => __('Slide (Slow)')],
        ];
    }
}
