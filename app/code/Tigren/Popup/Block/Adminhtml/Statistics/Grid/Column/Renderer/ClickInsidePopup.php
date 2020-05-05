<?php
/**
 * @copyright Copyright (c) 2016 www.tigren.com
 */

namespace Tigren\Popup\Block\Adminhtml\Statistics\Grid\Column\Renderer;

/**
 * Backup grid item renderer
 */
class ClickInsidePopup extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Text
{
    protected $_popupHelper;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Tigren\Popup\Helper\Data $popupHelper,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->_popupHelper = $popupHelper;
    }

    /**
     * Renders grid column
     *
     * @param \Magento\Framework\DataObject $row
     * @return mixed
     */
    public function _getValue(\Magento\Framework\DataObject $row)
    {
        return $this->_popupHelper->getPopupImpressions($row, 'click_inside_popup');
    }
}
