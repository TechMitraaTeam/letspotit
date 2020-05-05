<?php
/**
 * @copyright Copyright (c) 2016 www.tigren.com
 */
namespace Tigren\Socialshare\Model\Config\Source;

class Positions implements \Magento\Framework\Option\ArrayInterface
{

    public function toOptionArray()
    {
        return [
            ['value' => 'undertitle', 'label' => __('Under Title')], 
            ['value' => 'sidebar', 'label' => __('SideBar(Default)/Under Title')], 
            ['value' => 'sunkmenu', 'label' => __('Sunk Menu')],
            ['value' => 'footer', 'label' => __('Footer')]
            ];
    }
}
