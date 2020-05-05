<?php
/**
 * @copyright Copyright (c) 2016 www.tigren.com
 */

namespace Tigren\Popup\Block\Adminhtml\Widget;

class TextArea extends \Magento\Backend\Block\Widget\Form\Element
{
    /**
     * @var Factory
     */
    protected $_factoryElement;

    /**
     * @param Factory $factoryElement
     * @param CollectionFactory $factoryCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Data\Form\Element\Factory $factoryElement,
        $data = []
    )
    {
        $this->_factoryElement = $factoryElement;
        parent::__construct($context, $data);
    }

    /**
     * Prepare chooser element HTML
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element Form Element
     * @return \Magento\Framework\Data\Form\Element\AbstractElement
     */
    public function prepareElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $textArea = $this->_factoryElement->create('textarea', ['data' => $element->getData()])
            ->setId($element->getId())
            ->setForm($element->getForm())
            ->setClass('widget-option input-textarea admin__control-text');

        if ($element->getRequired()) {
            $textArea->addClass('required-entry');
        }

        $element->setData(
            'after_element_html',
            $this->_getAfterElementHtml() . $textArea->getElementHtml()
        );

        return $element;
    }

    /**
     * @return string
     */
    protected function _getAfterElementHtml()
    {
        $html = <<<HTML
    <style>
        .admin__field-control.control .control-value {
            display: none !important;
        }
    </style>
HTML;

        return $html;
    }
}
