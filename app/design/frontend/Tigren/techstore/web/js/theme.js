/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'mage/smart-keyboard-handler',
    'mage/mage',
    'mage/ie-class-fixer',
    'domReady!'
], function ($, keyboardHandler) {
    'use strict';

    if ($('body').hasClass('checkout-cart-index')) {
        if ($('#co-shipping-method-form .fieldset.rates').length > 0 && $('#co-shipping-method-form .fieldset.rates :checked').length === 0) {
            $('#block-shipping').on('collapsiblecreate', function () {
                $('#block-shipping').collapsible('forceActivate');
            });
        }
    }

    $('.cart-summary').mage('sticky', {
        container: '#maincontent'
    });
    $("#back-top").click(function() {
        $('html, body').animate({
            scrollTop: 0
        }, 500);
    });

    $(window).scroll(function () {
        if ($(this).scrollTop() > 270) {
            $("header.page-header").addClass("sticky");
            $('#back-top').addClass('fixed');
        } else {
            $("header.page-header").removeClass("sticky");
            $('#back-top').removeClass('fixed');
        }
    });

    if ($('body').hasClass('catalog-product-view')) {
        $('.quantity-plus').click(function () {
            $('.qty-default').val(Number($('.qty-default').val())+1);
        });

        $('.quantity-minus').click(function () {
            var value = Number($('.qty-default').val())-1;
            if(value > 0){
                $('.qty-default').val(value);
            }
        });
    }

    $('.panel.header > .header.links').clone().appendTo('#store\\.links');

    keyboardHandler.apply();
});
