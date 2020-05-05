<?php
/**
 * @copyright Copyright (c) 2016 www.tigren.com
 */

namespace Tigren\Bannermanager\Block\Adminhtml\Block\Edit\Tab;

use Magento\Backend\Block\Widget\Form;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;

class Main extends Generic implements TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @var \Magento\Customer\Api\GroupRepositoryInterface
     */
    protected $_groupRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $_searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\Convert\DataObject
     */
    protected $_objectConverter;

    protected $_bannermanagerHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Customer\Api\GroupRepositoryInterface $groupRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\Convert\DataObject $objectConverter
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Customer\Api\GroupRepositoryInterface $groupRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Convert\DataObject $objectConverter,
        \Magento\Store\Model\System\Store $systemStore,
        \Tigren\Bannermanager\Helper\Data $bannermanagerHelper,
        array $data = []
    )
    {
        $this->_systemStore = $systemStore;
        $this->_groupRepository = $groupRepository;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_objectConverter = $objectConverter;
        $this->_bannermanagerHelper = $bannermanagerHelper;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare content for tab
     *
     * @return \Magento\Framework\Phrase
     * @codeCoverageIgnore
     */
    public function getTabLabel()
    {
        return __('Block Information');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     * @codeCoverageIgnore
     */
    public function getTabTitle()
    {
        return __('Block Information');
    }

    /**
     * Returns status flag about this tab can be showed or not
     *
     * @return bool
     * @codeCoverageIgnore
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Returns status flag about this tab hidden or not
     *
     * @return bool
     * @codeCoverageIgnore
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * @return Form
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        /** @var \Tigren\Helpdesk\Model\Block $model */
        $model = $this->_coreRegistry->registry('bannermanager_block');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('block_');

        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('General Information'), 'class' => 'fieldset-wide']
        );

        if ($model->getId()) {
            $fieldset->addField('block_id', 'hidden', ['name' => 'block_id']);
        }

        $fieldset->addField(
            'block_title',
            'text',
            ['name' => 'block_title', 'label' => __('Block Title'), 'title' => __('Block Title'), 'required' => true]
        );

        /**
         * Check is single store mode
         */
        if (!$this->_storeManager->hasSingleStore()) {
            $field = $fieldset->addField(
                'select_stores',
                'multiselect',
                [
                    'label' => __('Store View'),
                    'required' => true,
                    'name' => 'stores[]',
                    'values' => $this->_systemStore->getStoreValuesForForm()
                ]
            );
            $renderer = $this->getLayout()->createBlock(
                'Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element'
            );
            $field->setRenderer($renderer);
            $model->setSelectStores($model->getStores());
        } else {
            $fieldset->addField(
                'select_stores',
                'hidden',
                ['name' => 'stores[]', 'value' => $this->_storeManager->getStore(true)->getId()]
            );
            $model->setSelectStores($this->_storeManager->getStore(true)->getId());
        }

        $customerGroups = $this->_groupRepository->getList($this->_searchCriteriaBuilder->create())->getItems();
        $fieldset->addField(
            'customer_group_ids',
            'multiselect',
            [
                'name' => 'customer_group_ids[]',
                'label' => __('Customer Groups'),
                'title' => __('Customer Groups'),
                'required' => true,
                'values' => $this->_objectConverter->toOptionArray($customerGroups, 'id', 'code')
            ]
        );

        $fieldset->addField(
            'block_position',
            'select',
            [
                'label' => __('Block Position'),
                'title' => __('Block Position'),
                'name' => 'block_position',
                'required' => true,
                'values' => $this->_bannermanagerHelper->getPositionOptions()
            ]
        );

        $fieldset->addField(
            'category_type',
            'select',
            [
                'label' => __('Category Type'),
                'title' => __('Category Type'),
                'name' => 'category_type',
                'required' => true,
                'values' => [
                    '1' => __('All Categories'),
                    '2' => __('All categories except below'),
                    '3' => __('Certain Categories')
                ],
                'after_element_html' => $this->_getAfterElementHtml()
            ]
        );

        $fieldset->addField(
            'category',
            'multiselect',
            [
                'label' => __('Categories'),
                'title' => __('Categories'),
                'name' => 'category',
                'container_id' => 'category_row_id',
                'values' => $this->_bannermanagerHelper->getCategoryOptions(),
            ]
        );

        $dateFormat = $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT);
        $timeFormat = $this->_localeDate->getTimeFormat(\IntlDateFormatter::SHORT);

        $style = 'color: #000;background-color: #fff; font-weight: bold; font-size: 13px;';

        $fieldset->addField(
            'from_date',
            'date',
            [
                'name' => 'from_date',
                'label' => __('From'),
                'title' => __('From'),
                'style' => $style,
                'date_format' => $dateFormat,
                'time_format' => $timeFormat,
                'note' => $this->_localeDate->getDateTimeFormat(\IntlDateFormatter::SHORT),
            ]
        );

        $fieldset->addField(
            'to_date',
            'date',
            [
                'name' => 'to_date',
                'label' => __('To'),
                'title' => __('To'),
                'style' => $style,
                'date_format' => $dateFormat,
                'time_format' => $timeFormat,
                'note' => $this->_localeDate->getDateTimeFormat(\IntlDateFormatter::SHORT)
            ]
        );

        $fieldset->addField(
            'display_type',
            'select',
            [
                'label' => __('Display Type'),
                'title' => __('Display Type'),
                'name' => 'display_type',
                'required' => true,
                'onchange' => 'hideShowMinMaxInput(this)',
                'values' => $this->_bannermanagerHelper->getDisplayTypeOptions()
            ]
        );
        $fieldset->addField(
            'min_images',
            'text',
            ['name' => 'min_images', 'label' => __('Min Images'), 'title' => __('Min Images'), 'required' => false]
        );

        $fieldset->addField(
            'max_images',
            'text',
            ['name' => 'max_images', 'label' => __('Max Images'), 'title' => __('Max Images'), 'required' => false]
        );

        $fieldset->addField(
            'is_active',
            'select',
            [
                'label' => __('Is Active'),
                'title' => __('Is Active'),
                'name' => 'is_active',
                'required' => true,
                'options' => ['1' => __('Yes'), '0' => __('No')]
            ]
        );

        $fieldset->addField(
            'sort_order',
            'text',
            ['name' => 'sort_order', 'label' => __('Sort Order'), 'title' => __('Sort Order'), 'required' => false]
        );

        if (!$model->getId()) {
            $model->setData('is_active', '1');
        }

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }


    protected function _getAfterElementHtml()
    {
        return <<<HTML
    <script>
        require(["prototype"], function(){
            //<![CDATA[
            document.observe("dom:loaded", function() {
                var categoryTypeElement = $("block_category_type");
                if(categoryTypeElement.getValue() == 1){
                    $("category_row_id").hide();
                }
                categoryTypeElement.on('change', function() {
                    if($(this).getValue() == 1){
                        $("category_row_id").hide();
                    }
                    else{
                        $("category_row_id").show();
                    }
                });
            });
            document.observe("dom:loaded",hideShowMinMaxInput);

            //]]>
        });

        function hideShowMinMaxInput(){
            if ($('block_display_type').value == 6) {
                $('block_min_images').up('.field-min_images').show();
                $('block_max_images').up('.field-max_images').show();
            } else {
                $('block_min_images').up('.field-min_images').hide();
                $('block_max_images').up('.field-max_images').hide();
            }
            return true;

        }
    </script>
HTML;
    }
}
