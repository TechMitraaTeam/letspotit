define([
    'jquery',
    'mage/translate',
    'jquery/ui',
    'mage/validation/validation',
    'tigren/ajaxsuite',
    'mage/mage'
], function ($, $t) {
    'use strict';

    $.widget('tigren.ajaxToCart', $.tigren.ajaxSuite, {
        options: {
            ajaxCart: {
                miniCartElement: null,
                addToCartButton: null,
                popupForm: null,
                minicartSelector: '[data-block="minicart"]',
                messagesSelector: '[data-placeholder="messages"]',
                initConfig: {
                    'show_success_message': true,
                    'timerErrorMessage': 3000,
                    'addWishlistItemUrl': null
                },
                formKey: null,
                formKeyInputSelector: 'input[name="form_key"]',
                closePopupBtnSelector: 'button#ajaxcart_cancel',
                addToCartButtonSelector: 'button.tocart',
                cartWrapperSelector: '#mb-ajaxcart-wrapper',
                addToCartUrl: null,
                addToCartInWishlistUrl: null,
                wishlistAddToCartUrl: null,
                checkoutCartUrl: null,
                popupTTL: 10,
                addToCartButtonDisabledClass: 'disabled',
                addToCartButtonTextWhileAdding: $t('Adding...'),
                addToCartButtonTextAdded: $t('Added'),
                addToCartButtonTextDefault: $t('Add to Cart'),
                backgroundColor: '#ededed',
                headerBackgroundColor: '#400b8f',
                headerTextColor: '#fff',
                buttonTextColor: '#fff',
                leftButtonColor: '#006bb4',
                rightButtonColor: '#006bb4'
            }
        },

        _bind: function () {
            this.initElements();
            this.initEvents();
            this.options.ajaxCart.formKey = $(this.options.ajaxCart.formKeyInputSelector).val();
        },

        initElements: function () {
            this.options.popupWrapper = $(this.options.popupWrapperSelector);
            this.options.popup = $(this.options.popupSelector);
            this.options.popupBlank = $(this.options.popupBlankSelector);
            this.options.close = $(this.options.closePopupButtonSelector);
            if (!this.options.cartWrapper) {
                this.options.cartWrapper = $('<div />', {
                    'id': 'mb-ajaxcart-wrapper'
                }).appendTo(this.options.popup);
            }
        },

        initEvents: function () {
            var self = this;
            $('body').delegate(self.options.ajaxCart.addToCartButtonSelector, 'click', function (e) {
                var colorId = null, sizeId = null;
                var selectedColor = $(this).closest('.product-item-details').find('.swatch-attribute.color .swatch-option.color.selected');
                if (selectedColor.length > 0) {
                    colorId = selectedColor.attr('option-id');
                }
                var selectedSize = $(this).closest('.product-item-details').find('.swatch-attribute.size .swatch-option.text.selected');
                if (selectedSize.length > 0) {
                    sizeId = selectedSize.attr('option-id');
                }

                if ($(this).closest('.product-info-main').length) {             //In product details page
                    e.preventDefault();
                    var dataForm = $('form#product_addtocart_form');
                    var validate = dataForm.validation('isValid');
                    if (validate) {
                        var form = $(this).closest('form');
                        self.ajaxSubmit(form);
                    }
                    return;
                }
                if ($(this).data('post')) {
                    e.stopPropagation();
                    var actionUrl = $(this).data('post').action,
                        additionUrl = '',
                        isWishlist = false;
                    if (actionUrl.search('wishlist/index/cart') != -1) {        //In wishlist page
                        additionUrl = actionUrl.replace(self.options.ajaxCart.wishlistAddToCartUrl, "");
                        isWishlist = true;
                    } else if (actionUrl.search('checkout/cart/add') != -1) {
                        additionUrl = actionUrl.replace(self.options.ajaxCart.checkoutCartUrl, "");
                    }

                    var params = $(this).data('post').data;

                    if (params.ajaxcart_success) {
                        delete params.ajaxcart_success;
                    }
                    if (params.ajaxcart_error) {
                        delete params.ajaxcart_error;
                    }

                    params.form_key = self.options.ajaxCart.formKey;
                    self.showPopup(params, additionUrl, isWishlist, colorId, sizeId);
                } else {
                    var form = $(this).closest('form');
                    if (form.length) {
                        var actionUrl = form.attr('action'),
                            additionUrl = '';
                        if (actionUrl.search('checkout/cart/add') != -1) {
                            additionUrl = actionUrl.replace(self.options.ajaxCart.checkoutCartUrl, "");
                        } else if (actionUrl.search('options=cart') != -1) {
                            // do nothing
                        } else {
                            return;
                        }
                        e.preventDefault();
                        var params = form.serialize();
                    } else {
                        var productId = $(this).closest('li.product-item').find('div.price-box').data('product-id');
                        if (productId) {
                            e.stopImmediatePropagation();
                            var params = {product: productId, form_key: self.options.ajaxCart.formKey};
                        } else {
                            return;
                        }
                    }
                    self.showPopup(params, additionUrl, false, colorId, sizeId);
                }
            });

            $('body').on('click', self.options.ajaxCart.closePopupBtnSelector, function (event) {
                self.closePopup();
            });
        },

        showPopup: function (params, additionUrl, isWishlist, colorId, sizeId) {
            var self = this,
                actionUrl = '';

            additionUrl = additionUrl || '';
            isWishlist = isWishlist || false;

            if (isWishlist) {
                actionUrl = self.options.ajaxCart.addToCartInWishlistUrl;

            } else {
                actionUrl = self.options.ajaxCart.addToCartUrl;
            }

            $(self.options.ajaxCart.minicartSelector).trigger('contentLoading');

            $.ajax({
                url: actionUrl + additionUrl,
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
                        if (res.addto) {
                            self.options.cartWrapper.html(res.html_popup);
                            self.makeColor();
                            self.showElement(self.options.ajaxCart.cartWrapperSelector, 'image');
                            self.autoClosePopup(self.options.cartWrapper);

                            var errorMessageInterval = setInterval(function () {
                                var messageElm = $('.page.messages .message-error div[data-bind="html: message.text"]');
                                if (messageElm.length > 0 && messageElm.text() != '') {
                                    clearInterval(errorMessageInterval);
                                    self.options.cartWrapper.find('.error-message').text(messageElm.text());
                                }
                            }, 500);

                            if (isWishlist && res.item) {
                                self.deleteProductElement(res.item);
                            }

                        } else {
                            self.options.cartWrapper.html(res.html_popup);
                            self.makeColor();
                            self.showElement(self.options.ajaxCart.cartWrapperSelector, 'swatch');

                            if (colorId) {
                                var cartColorInterval = setInterval(function () {
                                    if (self.options.cartWrapper.find('.swatch-option.color[option-id=\"' + colorId + '\"]').length > 0) {
                                        clearInterval(cartColorInterval);
                                        self.options.cartWrapper.find('.swatch-option.color[option-id=\"' + colorId + '\"]').click();
                                    }
                                }, 500);
                            }
                            if (sizeId) {
                                var cartSizeInterval = setInterval(function () {
                                    if (self.options.cartWrapper.find('.swatch-option.text[option-id=\"' + sizeId + '\"]').length > 0) {
                                        clearInterval(cartSizeInterval);
                                        self.options.cartWrapper.find('.swatch-option.text[option-id=\"' + sizeId + '\"]').click();
                                    }
                                }, 500);
                            }

                            if (res.item) {
                                $('<input>').attr({
                                    type: 'hidden',
                                    name: 'item',
                                    value: res.item
                                }).appendTo(self.options.cartWrapper.find('form'));
                            }

                            self.options.ajaxCart.popupForm = self.options.cartWrapper.find('form#product_addtocart_form');
                            if (self.options.ajaxCart.popupForm) {
                                self.options.ajaxCart.popupForm.mage('validation', {
                                    radioCheckboxClosest: '.nested',
                                    submitHandler: function (form) {
                                        self.ajaxPopupSubmit($(form));
                                        return false;
                                    }
                                });
                            }

                            self.options.cartWrapper.find('.product-add-form').css('clear', 'none');
                            self.options.cartWrapper.find('.product-add-form .product-options-wrapper').css({
                                'float': 'none',
                                'width': '100%'
                            });
                            self.options.cartWrapper.find('.product-add-form .product-options-wrapper .product-options-bottom').css({
                                'float': 'none',
                                'width': '100%'
                            });
                            self.options.cartWrapper.find('.product-add-form .product-options-wrapper .product-options-bottom .field.qty').css('display', 'block');
                        }
                    } else {
                        if (res.backUrl) {
                            self.showMessagePopup(params, false, additionUrl, isWishlist);
                            return;
                        }
                        if (res.messages) {
                            $(self.options.ajaxCart.messagesSelector).html(res.messages);
                        }
                        if (res.minicart) {
                            $(self.options.ajaxCart.minicartSelector).replaceWith(res.minicart);
                            $(self.options.ajaxCart.minicartSelector).trigger('contentUpdated');
                        }
                        if (res.product && res.product.statusText) {
                            $(self.options.ajaxCart.productStatusSelector)
                                .removeClass('available')
                                .addClass('unavailable')
                                .find('span')
                                .html(res.product.statusText);
                        }

                        self.showMessagePopup(params, true, additionUrl, isWishlist);
                    }
                },
                error: function (response) {
                    // do anything
                }
            });
        },

        ajaxPopupSubmit: function (form) {
            var self = this,
                actionUrl = form.attr('action'),
                inputItemWishlist = form.find('input[name="item"]'),
                isWishlist = false,
                additionUrl = '';

            if (inputItemWishlist.length && inputItemWishlist.val()) {
                isWishlist = true;
                if (actionUrl.search('checkout/cart/add') != -1) {
                    additionUrl = actionUrl.replace(self.options.ajaxCart.checkoutCartUrl, "");
                }
            }

            self.closePopup();
            $(self.options.ajaxCart.minicartSelector).trigger('contentLoading');

            $.ajax({
                url: form.attr('action'),
                data: form.serialize(),
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

                    if (res.backUrl) {
                        self.showMessagePopup(form.serialize(), false, additionUrl, isWishlist);
                        return;
                    }
                    if (res.messages) {
                        $(self.options.ajaxCart.messagesSelector).html(res.messages);
                    }

                    if (res.minicart) {
                        $(self.options.ajaxCart.minicartSelector).replaceWith(res.minicart);
                        $(self.options.ajaxCart.minicartSelector).trigger('contentUpdated');
                    }
                    if (res.product && res.product.statusText) {
                        $(self.options.ajaxCart.productStatusSelector)
                            .removeClass('available')
                            .addClass('unavailable')
                            .find('span')
                            .html(res.product.statusText);
                    }

                    self.enableAddToCartButton(form);
                    self.showMessagePopup(form.serialize(), true, additionUrl, isWishlist);
                }
            });
        },

        //In product details page
        ajaxSubmit: function (form) {
            var self = this;
            $(self.options.ajaxCart.minicartSelector).trigger('contentLoading');
            self.disableAddToCartButton(form);

            $.ajax({
                url: form.attr('action'),
                data: form.serialize(),
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

                    if (res.messages) {
                        $(self.options.ajaxCart.messagesSelector).html(res.messages);
                    }
                    if (res.minicart) {
                        $(self.options.ajaxCart.minicartSelector).replaceWith(res.minicart);
                        $(self.options.ajaxCart.minicartSelector).trigger('contentUpdated');
                    }
                    if (res.product && res.product.statusText) {
                        $(self.options.ajaxCart.productStatusSelector)
                            .removeClass('available')
                            .addClass('unavailable')
                            .find('span')
                            .html(res.product.statusText);
                    }
                    self.enableAddToCartButton(form);
                    if (res.backUrl) {
                        self.showMessagePopup(form.serialize(), false);
                    } else {
                        self.showMessagePopup(form.serialize(), true);
                    }
                }
            });
        },

        disableAddToCartButton: function (form) {
            var addToCartButton = $(form).find(this.options.ajaxCart.addToCartButtonSelector);
            addToCartButton.addClass(this.options.ajaxCart.addToCartButtonDisabledClass);
            addToCartButton.attr('title', this.options.ajaxCart.addToCartButtonTextWhileAdding);
            addToCartButton.find('span').text(this.options.ajaxCart.addToCartButtonTextWhileAdding);
        },

        enableAddToCartButton: function (form) {
            var self = this,
                addToCartButton = $(form).find(this.options.ajaxCart.addToCartButtonSelector);
            addToCartButton.find('span').text(this.options.ajaxCart.addToCartButtonTextAdded);
            addToCartButton.attr('title', this.options.ajaxCart.addToCartButtonTextAdded);

            setTimeout(function () {
                addToCartButton.removeClass(self.options.ajaxCart.addToCartButtonDisabledClass);
                addToCartButton.find('span').text(self.options.ajaxCart.addToCartButtonTextDefault);
                addToCartButton.attr('title', self.options.ajaxCart.addToCartButtonTextDefault);
            }, 1000);
        },

        showMessagePopup: function (params, isSuccess, additionUrl, isWishlist) {
            var self = this,
                actionUrl = '';
            additionUrl = additionUrl || '';
            isWishlist = isWishlist || false;

            if (isWishlist) {
                actionUrl = self.options.ajaxCart.addToCartInWishlistUrl;
            } else {
                actionUrl = self.options.ajaxCart.addToCartUrl;
            }

            if (isSuccess) {
                if (typeof params == 'object') {
                    params.ajaxcart_success = 1;
                } else {
                    params += '&ajaxcart_success=1';
                }
            } else {
                if (typeof params == 'object') {
                    params.ajaxcart_error = 1;
                } else {
                    params += '&ajaxcart_error=1';
                }
            }

            if ($('.product-info-main').length > 0) {
                var productWrapper = $('.product-info-main');
            } else {
                var productWrapper = self.options.cartWrapper;
            }
            var sizeId = productWrapper.find('.swatch-attribute.size .swatch-option.text.selected').attr('option-id');
            var sizeLabel = productWrapper.find('.swatch-attribute.size .swatch-option.text.selected').text();
            var colorId = productWrapper.find('.swatch-attribute.color .swatch-option.color.selected').attr('option-id');
            var colorLabel = productWrapper.find('.swatch-attribute.color .swatch-option.color.selected').attr('option-label');
            if (typeof(params) === 'string') {
                params += '&size=' + sizeId + '&color=' + colorId + '&sizeLabel=' + sizeLabel + '&colorLabel=' + colorLabel;
            }
            $.ajax({
                url: actionUrl + additionUrl,
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

                        self.options.cartWrapper.html(res.html_popup);
                        self.makeColor();
                        self.showElement(self.options.ajaxCart.cartWrapperSelector, 'image');
                        self.autoClosePopup(self.options.cartWrapper);

                        var errorMessageInterval = setInterval(function () {
                            var messageElm = $('.page.messages .message-error div[data-bind="html: message.text"]');
                            if (messageElm.length > 0 && messageElm.text() != '') {
                                clearInterval(errorMessageInterval);
                                self.options.cartWrapper.find('.error-message').text(messageElm.text());
                            }
                        }, 500);

                        if (isWishlist && res.item) {
                            self.deleteProductElement(res.item);
                        }
                        return;

                    }
                },
                error: function (response) {
                    // do anything
                }
            });
        },

        deleteProductElement: function (item) {
            var productElementSelector = 'li.product-item#' + 'item_' + item;

            var productElement = $(productElementSelector);
            if (productElement.length) {
                productElement.fadeOut(500, function () {
                    $(this).remove();
                    return true;
                });
            }

            return false;
        },

        showElement: function (elmSelector, afterloadElm) {
            var self = this;
            afterloadElm = afterloadElm || false;
            self.options.popup.children().hide();
            self.options.popup.children(elmSelector).show();
            if (afterloadElm) {
                if (afterloadElm == 'swatch') {
                    if (self.options.cartWrapper.find('.swatch-opt').length > 0) {
                        var cartCenterInterval = setInterval(function () {
                                clearInterval(cartCenterInterval);
                                self.animationPopup();
                        }, 500);
                    } else {
                        self.animationPopup();
                    }
                } else {
                    self.animationPopup();
                }
            } else {
                self.animationPopup();
            }
        },
    });

    return $.tigren.ajaxToCart;
});