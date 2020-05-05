<?php
/**
 * @copyright Copyright (c) 2016 www.tigren.com
 */

namespace Tigren\Bannermanager\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class Thumbnail extends Column
{
    const NAME = 'banner_image';

    protected $bannermanagerHelper;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param \Tigren\Bannermanager\Helper\Data $bannermanagerHelper
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        \Tigren\Bannermanager\Helper\Data $bannermanagerHelper,
        array $components = [],
        array $data = []
    )
    {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->bannermanagerHelper = $bannermanagerHelper;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                $banner = new \Magento\Framework\DataObject($item);
                $item[$fieldName . '_src'] = $this->bannermanagerHelper->getImageUrl($banner->getBannerImage());
                $item[$fieldName . '_alt'] = $banner->getBannerImage();
                $item[$fieldName . '_link'] = $this->urlBuilder->getUrl(
                    'bannersmanager/banners/edit',
                    ['id' => $banner->getId()]
                );
                $item[$fieldName . '_orig_src'] = $this->bannermanagerHelper->getImageUrl($banner->getBannerImage());
            }
        }

        return $dataSource;
    }
}
