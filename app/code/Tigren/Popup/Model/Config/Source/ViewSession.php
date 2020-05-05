<?php
/**
 * @copyright Copyright (c) 2016 www.tigren.com
 */

namespace Tigren\Popup\Model\Config\Source;

class ViewSession implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => 'after_page_loads', 'label' => __('After Page Loads')],
            ['value' => 'after_x_seconds', 'label' => __('Define Seconds after Page Loads')],
            ['value' => 'after_use_scroller', 'label' => __('After Customer Use Scroller')]
        ];
    }
}