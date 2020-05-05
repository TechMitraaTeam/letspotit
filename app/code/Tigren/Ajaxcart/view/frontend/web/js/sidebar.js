define([
    'jquery',
    'Magento_Customer/js/model/authentication-popup',
    'Magento_Customer/js/customer-data',
    'Magento_Ui/js/modal/alert',
    'Magento_Ui/js/modal/confirm',
    'mage/loader',
    'jquery/ui',
    'mage/decorate',
    'mage/collapsible',
    'mage/cookies',
    'Magento_Checkout/js/sidebar'
], function ($, authenticationPopup, customerData, alert, confirm, loader) {
    'use strict';
    $.widget('tigren.sidebar', $.mage.sidebar, {
        options: {
            ajaxLoader: null,
            minicartSelector: '.block-minicart'
        },

        _create: function () {
            this._super();
            this.options.ajaxLoader = $(this.options.minicartSelector).loader(window.ajaxCartLoaderOptions);
        },

        _removeItem: function (elem) {
            this.options.ajaxLoader.loader('show');
            this._super(elem);
        },

        _removeItemAfter: function (elem, response) {
            this.options.ajaxLoader.loader('hide');
            this._super(elem, response);
        }
    });

    return $.tigren.sidebar;
});