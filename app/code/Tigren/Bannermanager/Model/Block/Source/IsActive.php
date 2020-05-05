<?php
/**
 * @copyright Copyright (c) 2016 www.tigren.com
 */

namespace Tigren\Bannermanager\Model\Block\Source;

class IsActive implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var \Tigren\Bannermanager\Model\Block
     */
    protected $block;

    /**
     * Constructor
     *
     * @param \Tigren\Bannermanager\Model\Block $block
     */
    public function __construct(\Tigren\Bannermanager\Model\Block $block)
    {
        $this->block = $block;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options[] = ['label' => '', 'value' => ''];
        $availableOptions = $this->block->getAvailableStatuses();
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }
}
