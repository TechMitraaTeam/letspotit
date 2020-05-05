<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Tigren\Bannermanager\Controller\Adminhtml\Banners;

class Banners extends \Magento\Widget\Controller\Adminhtml\Widget\Instance
{

    public function execute()
    {

        $selected = $this->getRequest()->getParam('selected', '');

        $chooser = $this->_view->getLayout()->createBlock(
            'Tigren\Bannermanager\Block\Adminhtml\Banner\Widget\Chooser'
        )->setName(
            $this->mathRandom->getUniqueHash('banners_grid_')
        )->setUseMassaction(
            true
        )
            ->setSelectedBanners(
                explode(',', $selected)
            );

        $serializer = $this->_view->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Grid\Serializer',
            '',
            [
                'data' => [
                    'grid_block' => $chooser,
                    'callback' => 'getSelectedBanners',
                    'input_element_name' => 'selected_banners',
                    'reload_param_name' => 'selected_banners',
                ]
            ]
        );
        $this->setBody($chooser->toHtml() . $serializer->toHtml());
    }
}
