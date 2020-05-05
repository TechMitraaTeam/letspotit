define([
    'jquery',
    'mage/mage',
    'mage/cookies'
], function ($) {
    "use strict";

    $.mbPopup = function(element, options) {

        var defaults = {
            'popup_class' : 'mb-popup-custom-default',
            'popup_id' : 'mb-popup',
            'cookie_expires' : '86400',
            'content' : '',
            'border_radius' : '0',
            'popup_width' : 'auto',
            'animate' : 'fadein',
            'view_session': 'after_page_loads',
            'view_seconds' : '5',
            'auto_close' : '',
            'enabled_statistics': 0,
            'impression_type' : 'close_without_interaction'
        };

        var self = this;

        self.settings = {};

        var $element = $(element),
            element = element,
            overlay = null,
            popup = null,
            form = null,
            close = null;

        self.init = function() {
            self.settings = $.extend({}, defaults, options);
            _initPopupTrigger();
        };

        var _initPopupTrigger = function() {
                if ($.mage.cookies.get(self.settings.popup_id)) {
                    return;
                } else if (self.settings.cookieNoticesConfig && $.mage.cookies.get(self.settings.cookieNoticesConfig.cookieName)) {
                    return;
                } else {
                    var cookieExpires = new Date(new Date().getTime() + self.settings.cookie_expires * 1000);
                    $.mage.cookies.set(self.settings.popup_id, 1, {expires: cookieExpires});
                }

                switch(self.settings.view_session) {
                    case 'after_page_loads':
                        _doPopup();
                        break;
                    case 'after_x_seconds':
                        if (!parseInt(self.settings.view_seconds)) {
                            self.settings.view_seconds = 5;
                        }
                        setTimeout(
                            function(){_doPopup();}, self.settings.view_seconds * 1000
                        );
                        break;
                    case 'after_use_scroller':
                        $(document).one("scroll", function() {
                            _createElements();
                            _initEvents();
                        });
                        break;
                    default:
                        $(document).one("scroll", function() {
                            _doPopup();
                        });
                }
            },

            _doPopup = function() {
                _createElements();
                _initEvents();
            },

            _createElements = function() {
                if( $('body').find('.mb-popup-overlay').length == 0 ) {
                    self.overlay = $('<div />', {
                        'class' : 'mb-popup-overlay'
                    }).appendTo('body');
                } else {
                    self.overlay = $('body').find('.mb-popup-overlay');
                }

                if( self.overlay.find('.mb-popup-wrapper').length == 0 ) {
                    self.popup = $('<div />', {
                        'class' : 'mb-popup-wrapper ' + self.settings.popup_class
                    }).appendTo( $('body') );
                } else {
                    self.popup = $('body').find('.mb-popup-wrapper');
                }

                self.popup.css({
                    'width' : self.settings.popup_width,
                    'border-radius': self.settings.border_radius,
                    '-webkit-border-radius' : self.settings.border_radius,
                    '-moz-border-radius' : self.settings.border_radius
                });

                if( self.overlay.find('.close').length == 0 ) {
                    self.close = $('<a />', {
                        'class' : 'close'
                    }).appendTo( self.popup );
                } else {
                    self.close = self.overlay.find('.close');
                }
            },

            _initEvents = function() {
                $(document).on('touchstart click', '.mb-popup-overlay', function(e){
                    if( $( e.target).hasClass('close') || $( e.target ).parents( '.mb-popup-overlay' ).length == 0 ) {
                        _close();
                    }
                }).on('touchstart click', '.mb-popup-wrapper', function(e) {
                        if( !$( e.target).hasClass('close') ) {
                            _setImpressionType('click_inside_popup');
                        }
                    }).on('keyup', function(e) {
                            if (e.keyCode == 27) {
                                _close();
                            }
                        }).on('click', '.mb-popup-wrapper a.close', function () {
                            _close();
                        });

                $(window).on('resize', function(){
                    _center();
                });

                $('html').removeClass('mb-opened');

                _open();
            },

            _open = function() {
                _content();
                _initCss();
                _animate();
                self.overlay.css({ 'display': 'block', opacity: 0 }).animate({ opacity: 1 }, 100);
                $('html').addClass('mb-opened');

                if (parseInt(self.settings.auto_close)) {
                    setTimeout(
                        function(){
                            _close();
                        },
                        self.settings.auto_close * 1000
                    );
                }
            },

            _content = function() {
                if( self.settings.content != '' ) {
                    self.popup.html( self.settings.content );
                } else if( $element.data('container') ) {
                    self.popup.html( $($element.data('container')).html() );
                } else if( $element.data('content') ) {
                    self.popup.html( $element.data('content') );
                } else if( $element.attr('title') ) {
                    self.popup.html( $element.attr('title') );
                } else {
                    self.popup.html('');
                }

                //update <input id="" /> and <label for="">
                self.popup.find('form, input, label, a').each(function(){
                    if( typeof $(this).attr('id') != 'undefined' ) {
                        var id = $(this).attr('id') + '_mb-popup';
                        $(this).attr('id', id);
                    }

                    if( typeof $(this).attr('for') != 'undefined' ) {
                        var id = $(this).attr('for') + '_mb-popup';
                        $(this).attr('for', id);
                    }
                });

                if( self.overlay.find('.close').length == 0 ) {
                    self.close = $('<a />', {
                        'class' : 'close'
                    }).appendTo( self.popup );
                } else {
                    self.close = self.overlay.find('.close');
                }

                if( self.popup.find('form').length !== 0 ) {
                    self.form = self.popup.find('form');

                    self.form.mage('validation', {})
                        .find('input:text')
                        .attr('autocomplete', 'off');

                    self.form.on('submit', function() {
                        _setImpressionType('goal_completion');
                    });
                }

                // cookie compliance popup
                _initCookieCompliance();
            },

            _initCookieCompliance = function() {

                if( self.settings.cookieNoticesConfig && self.popup.find(self.settings.cookieNoticesConfig.cookieAllowButtonSelector).length !== 0 ) {
                    self.popup.find(self.settings.cookieNoticesConfig.cookieAllowButtonSelector).on('click', $.proxy(function() {
                        var cookieExpires = new Date(new Date().getTime() + self.settings.cookieNoticesConfig.cookieLifetime * 1000);
                        $.mage.cookies.set(self.settings.cookieNoticesConfig.cookieName, self.settings.cookieNoticesConfig.cookieValue, {expires: cookieExpires});

                        if ($.mage.cookies.get(self.settings.cookieNoticesConfig.cookieName)) {
                            window.location.reload();
                        } else {
                            window.location.href = self.settings.cookieNoticesConfig.noCookiesUrl;
                        }
                    }, this));
                }

            },

            _initCss = function() {

                self.popup.find('.mb-popup-border').css({
                    'overflow-x': 'hidden',
                    'overflow-y': 'auto',
                });

                self.popup.css({
                    'position': 'fixed'
                });

            },

            _center = function() {

                self.popup.find('.mb-popup-border').css({
                    'max-height': $(window).height() + 'px'
                });

                self.popup.css({
                    'top': Math.max(0, (($(window).height() - self.popup.outerHeight()) / 2) ) + 'px',
                    'left': Math.max(0, (($(window).width() - self.popup.outerWidth()) / 2) ) + 'px'
                });

            },

            _animate = function() {

                switch(self.settings.animate) {
                    case 'fadein':
                        _fadeInEffect();
                        break;
                    case 'slideup':
                        _slideUpEffect();
                        break;
                    case 'slidedown':
                        _slideDownEffect();
                        break;
                    case 'slidetoright':
                        _slideToRightEffect();
                        break;
                    case 'slidetoleft':
                        _slideToLeftEffect();
                        break;
                    default:
                        _fadeInEffect();
                }

            },

            _fadeInEffect = function() {

                self.popup.css({
                    'display' : 'none',
                    'top': Math.max(0, (($(window).height() - self.popup.outerHeight()) / 2) ) + 'px',
                    'left' : Math.max(0, (($(window).width() - self.popup.outerWidth()) / 2) ) + 'px',
                });

                self.popup.fadeIn('slow', function() {
                    $(this).find('.mb-popup-border').css({
                        'max-height': $(window).height() + 'px'
                    });
                });;

            },

            _slideToRightEffect = function() {

                self.popup.css({
                    'top': Math.max(0, (($(window).height() - self.popup.outerHeight()) / 2) ) + 'px'
                });

                self.popup.animate(
                    {
                        left : Math.max(0, (($(window).width() - self.popup.outerWidth()) / 2) ) + 'px',
                    },
                    1000,
                    function() {
                        $(this).find('.mb-popup-border').css({
                            'max-height': $(window).height() + 'px'
                        });
                    }
                );

            },

            _slideToLeftEffect = function() {

                self.popup.css({
                    'top': Math.max(0, (($(window).height() - self.popup.outerHeight()) / 2) ) + 'px',
                    'left' : Math.max(0, $(window).width() - self.popup.outerWidth()) + 'px',
                });

                self.popup.animate(
                    {
                        left : Math.max(0, (($(window).width() - self.popup.outerWidth()) / 2) ) + 'px',
                    },
                    1000,
                    function() {
                        $(this).find('.mb-popup-border').css({
                            'max-height': $(window).height() + 'px'
                        });
                    }
                );

            },

            _slideDownEffect = function() {

                self.popup.css({
                    'left': Math.max(0, (($(window).width() - self.popup.outerWidth()) / 2) ) + 'px',
                    'top' :  '0px',
                });

                self.popup.animate(
                    {
                        top :  Math.max(0, (($(window).height() - self.popup.outerHeight()) / 2) ) + 'px',
                    },
                    1000,
                    function() {
                        $(this).find('.mb-popup-border').css({
                            'max-height': $(window).height() + 'px'
                        });
                    }
                );

            },

            _slideUpEffect = function() {

                self.popup.css({
                    'left': Math.max(0, (($(window).width() - self.popup.outerWidth()) / 2) ) + 'px'
                });

                self.popup.animate(
                    {
                        top :  Math.max(0, (($(window).height() - self.popup.outerHeight()) / 2) ) + 'px',
                    },
                    1000,
                    function() {
                        $(this).find('.mb-popup-border').css({
                            'max-height': $(window).height() + 'px'
                        });
                    }
                );

            },

            _setImpressionType = function(type) {
                if (self.settings.impression_type !== 'goal_completion') {
                    self.settings.impression_type = type;
                }
            },

            _close = function() {
                self.overlay.css({ 'display': 'none', opacity: 1 }).animate({ opacity: 0 }, 500);
                $element.trigger('close.mb-popup');
                $('html').removeClass('mb-opened');
                _destroy();
            },

            _destroy = function() {
                if (self.settings.enabled_statistics) {
                    $.ajax({
                        url: self.settings.impression_url,
                        data: {
                            instance_id : self.settings.instance_id,
                            impression_type: self.settings.impression_type
                        },
                        type: 'post',
                        dataType: 'json',
                        success: function(res) {
                            // console.log('Logged impression!!!');
                        }
                    });
                }
                self.popup.remove();
                self.overlay.remove();

                //self.popup = self.overlay = null;
                $element.removeData('mb_popup');
            };

        self.init();
    };

    $.fn.mbPopup = function(options) {

        return this.each(function() {
            if (undefined === $(this).data('mb-popup')) {
                var mbPopup = new $.mbPopup(this, options);
                $(this).data('mb-popup', mbPopup);
            }
        });

    };
});
