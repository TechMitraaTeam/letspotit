define([
    'jquery',
    'mage/translate',
    'jquery/ui',
    'mage/validation/validation',
    'tigren/ajaxsuite'
], function ($) {
    'use strict';

    $.widget('tigren.ajaxWishlist', $.tigren.ajaxSuite, {
        options: {
            ajaxWishlist: {
                enabled: null,
                ajaxWishlistUrl: null,
                wishlistBtnSelector: '[data-action="add-to-wishlist"]',
                wishlistWrapperSelector: '#mb-ajaxwishlist-wrapper',
                btnCloseSelector: '#ajaxwishlist_btn_close_popup',
                btnCancelSelector: '#ajaxwishlist_btn_cancel',
                btnToLoginSelector: '#ajaxwishlist_btn_to_login',
                loginUrl: null
            }
        },

        _bind: function () {
            if (this.options.ajaxSuite.enabled == true && this.options.ajaxWishlist.enabled == true) {
                this.initElements();
                this.initEvents();
            }
        },
        
        initElements: function () {
            this.options.popupWrapper = $(this.options.popupWrapperSelector);
            this.options.popup = $(this.options.popupSelector);
            this.options.popupBlank = $(this.options.popupBlankSelector);
            this.options.close = $(this.options.closePopupButtonSelector);
            if (!this.options.wishlistWrapper) {
                this.options.wishlistWrapper = $('<div />', {
                    'id': 'mb-ajaxwishlist-wrapper'
                }).appendTo(this.options.popup);
            }
        },
        
        initEvents: function () {
            var self = this,
                loginUrl = self.options.ajaxWishlist.loginUrl;
            $('body').on('click', self.options.ajaxWishlist.btnToLoginSelector, function (e) {
                if(self.options.popupWrapper.find('#mb-ajaxlogin-wrapper').find('.mb-login-popup').length > 0) {
                    self.options.popupWrapper.find('#mb-ajaxlogin-wrapper').children().hide();
                    self.options.popupWrapper.find('#mb-ajaxlogin-wrapper').find('.mb-login-popup').show();
                    self.showElement('#mb-ajaxlogin-wrapper');
                } else {
                    window.location.href = loginUrl;
                }
            });
            $('body').on('click', self.options.ajaxWishlist.btnCloseSelector, function (e) {
                self.closePopup();
            });
            $('body').on('click', self.options.ajaxWishlist.btnCancelSelector, function (e) {
                self.closePopup();
            });
            
            $('body').on('click', self.options.ajaxWishlist.wishlistBtnSelector, function (e) {
                e.preventDefault();
                e.stopPropagation();
                var params = $(this).data('post').data;
                params['isWishlist'] = true;
                
                var colorId = null, sizeId = null;
                var selectedColor = $(this).closest('.product-item-details').find('.swatch-attribute.color .swatch-option.color.selected');
                if (selectedColor.length == 0) {
                    selectedColor = $(this).closest('.product-info-main').find('.swatch-attribute.color .swatch-option.color.selected');
                }
                if (selectedColor.length > 0) {
                    colorId = selectedColor.attr('option-id');
                }
                var selectedSize = $(this).closest('.product-item-details').find('.swatch-attribute.size .swatch-option.text.selected');
                if (selectedSize.length == 0) {
                    selectedSize = $(this).closest('.product-info-main').find('.swatch-attribute.size .swatch-option.text.selected');
                }
                if (selectedSize.length > 0) {
                    sizeId = selectedSize.attr('option-id');
                }
                self.closePopup();
                self.showWishlistPopup(params, colorId, sizeId);
            });
        },

        showWishlistPopup: function (params, colorId, sizeId) {
            var self = this;
            $.ajax({
                url: self.options.ajaxWishlist.ajaxWishlistUrl,
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
                        self.options.wishlistWrapper.html(res.html_popup);
                        self.makeColor();
                        self.showElement(self.options.ajaxWishlist.wishlistWrapperSelector, 'swatch');
                        if (colorId) {
                            var wishlistColorInterval = setInterval(function() {
                                if (self.options.wishlistWrapper.find('.swatch-option.color[option-id=\"'+colorId+'\"]').length > 0) {
                                    clearInterval(wishlistColorInterval);
                                    self.options.wishlistWrapper.find('.swatch-option.color[option-id=\"'+colorId+'\"]').click();
                                }
                            }, 500);
                        }
                        if (sizeId) {
                            var wishlistSizeInterval = setInterval(function() {
                                if (self.options.wishlistWrapper.find('.swatch-option.text[option-id=\"'+sizeId+'\"]').length > 0) {
                                    clearInterval(wishlistSizeInterval);
                                    self.options.wishlistWrapper.find('.swatch-option.text[option-id=\"'+sizeId+'\"]').click();
                                }
                            }, 500);
                        }
                        
                        self.options.wishlistWrapper.find('.product-add-form').css('clear', 'none');
                        self.options.wishlistWrapper.find('.product-add-form .product-options-wrapper').css({'float':'none', 'width':'100%'});
                        self.options.wishlistWrapper.find('.product-add-form .product-options-wrapper .product-options-bottom').css({'float':'none', 'width':'100%'});
                        self.options.wishlistWrapper.find('.product-add-form .product-options-wrapper .product-options-bottom .field.qty').css('display','block');
                    } else {
                        alert('No response from server');
                    }

                    self.options.poupForm = self.options.popup.find('form#product_addtocart_form');
                    if (self.options.poupForm) {
                        self.options.poupForm.mage('validation', {
                            submitHandler: function (form) {
                                self.addToWishlist($(form));
                                return false;
                            }
                        });
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

        addToWishlist: function (form) {
            var self = this;
            self.closePopup();
            
            var data = form.serialize();
            data += '&isWishlistSubmit=true';
            var sizeId = self.options.wishlistWrapper.find('.swatch-attribute.size .swatch-option.text.selected').attr('option-id');
            if (sizeId) {
               var sizeLabel = self.options.wishlistWrapper.find('.swatch-attribute.size .swatch-option.text.selected').text();
               data+= '&size=' + sizeId + '&sizeLabel=' + sizeLabel;
            }
            var colorId = self.options.wishlistWrapper.find('.swatch-attribute.color .swatch-option.color.selected').attr('option-id');
            if (colorId) {
                var colorLabel = self.options.wishlistWrapper.find('.swatch-attribute.color .swatch-option.color.selected').attr('option-label');
                data+= '&color=' + colorId + '&colorLabel='+ colorLabel;
            }
            $.ajax({
                url: form.attr('action'),
                data: data,
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
                        self.options.wishlistWrapper.html(res.html_popup);
                        self.makeColor();
                        self.showElement(self.options.ajaxWishlist.wishlistWrapperSelector, 'image');
                        self.autoClosePopup(self.options.wishlistWrapper);
                    } else {
                        // Nothing
                        alert('addToWishlist: No response from server');
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
        
        showElement: function (elmSelector, afterloadElm) {
            var self = this;
            afterloadElm = afterloadElm || false;
            self.options.popup.children().hide();
            self.options.popup.children(elmSelector).show();
            if (afterloadElm) {
                if (afterloadElm == 'swatch') {
                    if (self.options.wishlistWrapper.find('.swatch-opt-conf').length > 0) {
                        var wishlistCenterInterval = setInterval(function() {
                            if (self.options.wishlistWrapper.find('.swatch-attribute.color').length > 0 && self.options.wishlistWrapper.find('.swatch-attribute.size').length > 0) {
                                clearInterval(wishlistCenterInterval);
                                self.animationPopup();
                            }
                        }, 500);
                    } else {
                        self.animationPopup();
                    }
                } else {
                    var wishlistCenterInterval = setInterval(function() {
                        if (self.options.wishlistWrapper.find('.mb-ajaxsuite-popup-border .photo.image').length > 0) {
                            clearInterval(wishlistCenterInterval);
                            self.animationPopup();
                        }
                    }, 500);
                }
            } else {
                self.animationPopup();
            }
        },
    });

    return $.tigren.ajaxWishlist;
});