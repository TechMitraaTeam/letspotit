<?php
/**
 * @copyright Copyright (c) 2016 www.tigren.com
 */

namespace Tigren\Bannermanager\Model\Banner\Source;

class IsActive implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var \Tigren\Bannermanager\Model\Banner
     */
    protected $_banner;

    /**
     * Constructor
     *
     * @param \Tigren\Bannermanager\Model\Banner $banner
     */
    public function __construct(\Tigren\Bannermanager\Model\Banner $banner)
    {
        $this->_banner = $banner;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options[] = ['label' => '', 'value' => ''];
        $availableOptions = $this->_banner->getAvailableStatuses();
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }
}
