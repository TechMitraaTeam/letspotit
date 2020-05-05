define([
    'jquery',
    'mage/translate',
    'jquery/ui',
    'mage/validation/validation',
    'tigren/ajaxsuite'
], function ($) {
    'use strict';

    $.widget('tigren.ajaxCompare', $.tigren.ajaxSuite, {

        options: {
            ajaxCompare: {
                enabled: null,
                ajaxCompareUrl: null,
                compareBtnSelector: 'a.action.tocompare',
                compareWrapperSelector: '#mb-ajaxcompare-wrapper',
                btnCloseSelector: '#ajaxcompare_btn_close_popup',
            }
        },

        _bind: function () {
            if (this.options.ajaxSuite.enabled == true && this.options.ajaxCompare.enabled == true) {
                this.initElements();
                this.initEvents();
            }
        },
        
        initElements: function () {
            this.options.popupWrapper = $(this.options.popupWrapperSelector);
            this.options.popup = $(this.options.popupSelector);
            this.options.popupBlank = $(this.options.popupBlankSelector);
            this.options.close = $(this.options.btnCloseSelector);
            if (!this.options.compareWrapper) {
                this.options.compareWrapper = $('<div />', {
                    'id': 'mb-ajaxcompare-wrapper'
                }).appendTo(this.options.popup);
            }
        },
        
        initEvents: function () {
            var self = this;
            $('body').on('click', self.options.ajaxCompare.btnCloseSelector, function (e) {
                self.closePopup();
            });
                    
            $('body').on('click', this.options.ajaxCompare.compareBtnSelector, function (e) {
                e.preventDefault();
                e.stopPropagation();
                var params = $(this).data('post').data;
                params['isCompare'] = true;
                self.addCompare(params);
            });
        },

        addCompare: function (params) {
            var self = this;
            $.ajax({
                url: self.options.ajaxCompare.ajaxCompareUrl,
                data: params,
                type: 'post',
                dataType: 'json',
                beforeSend: function () {
                    if (self.isLoaderEnabled()) {
                        $('body').trigger(self.options.ajaxSuite.processStart);
                    }
                },
                success: function (res) {
                    if (self.isLoaderEnabled()) {
                        $('body').trigger(self.options.ajaxSuite.processStop);
                    }

                    if (res.html_popup) {
                        self.options.compareWrapper.html(res.html_popup);
                        self.makeColor();
                        setTimeout(function(){self.showElement(self.options.ajaxCompare.compareWrapperSelector)},1000);
                        self.autoClosePopup(self.options.compareWrapper);
                    } else {
                        alert('No response from server');
                    }
                },
                error: function (res) {
                    if (self.isLoaderEnabled()) {
                        $('body').trigger(self.options.ajaxSuite.processStop);
                    }
                    alert('Error in sending ajax request');
                }
            });
        },
    });

    return $.tigren.ajaxCompare;
});