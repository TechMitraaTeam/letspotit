<?php
/**
 * @copyright Copyright (c) 2016 www.tigren.com
 */

namespace Tigren\Popup\Block\Widget\Custom;

class Newsletter extends \Tigren\Popup\Block\Widget\AbstractPopup
{
    /**
     * @return string
     */
    public function getNewsletterFormActionUrl()
    {
        return $this->getUrl('newsletter/subscriber/new', ['_secure' => true]);
    }
}