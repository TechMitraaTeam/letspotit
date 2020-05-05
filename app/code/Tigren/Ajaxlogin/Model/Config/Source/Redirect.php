<?php
/**
 * @copyright Copyright (c) 2017 www.tigren.com
 */

namespace Tigren\Ajaxlogin\Model\Config\Source;

class Redirect implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 0, 'label' => __('Reload')],
            ['value' => 1, 'label' => __('Customer Dashboard')],
            ['value' => 2, 'label' => __('Homepage')],
            ['value' => 3, 'label' => __('Cart Page')],
            ['value' => 4, 'label' => __('Wishlist Page')]
        ];
    }
}
