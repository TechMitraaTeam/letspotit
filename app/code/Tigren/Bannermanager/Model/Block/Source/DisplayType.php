<?php
/**
 * @copyright Copyright (c) 2016 www.tigren.com
 */

namespace Tigren\Bannermanager\Model\Block\Source;

class DisplayType implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['label' => '--Select type--', 'value' => ''],
            ['label' => __('All Images'), 'value' => 1],
            ['label' => __('Random'), 'value' => 2],
            ['label' => __('Slider'), 'value' => 3],
            ['label' => __('Slider with description'), 'value' => 4],
            ['label' => __('Fade'), 'value' => 5],
            ['label' => __('Fade with description'), 'value' => 6]
        ];
    }
}
