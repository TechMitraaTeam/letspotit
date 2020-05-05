define([
    'jquery',
    'mage/translate',
    'jquery/ui',
    'mage/validation/validation',
    'tigren/ajaxsuite'
], function ($) { 
    'use strict';
    $.widget('tigren.ajaxLogin', $.tigren.ajaxSuite, {
        options: {
            ajaxLogin: {
                ajaxGetPopupUrl: null,
                ajaxLoginUrl: null,
                ajaxSocialLoginUrl: null,
                ajaxRegisterUrl: null,
                ajaxForgotPasswordUrl: null,
                ajaxLogoutUrl: null,
                enabled: null,
                urlRedirect: null,
                slideAnimation: null,
                socialLoginEnable: null,
                facebookAppId: null,
                ggPlusClientId: null,
                ajaxTwitterUrl: null,
                loginWrapperSelector: '#mb-ajaxlogin-wrapper',
                loginElmSelector: 'a[href*="customer/account/login/"]',
                forgotElmSelector: 'a[href*="customer/account/forgotpassword/"]',
                registerElmSelector: 'a[href*="customer/account/create/"]',
                logoutElmSelector: 'a[href*="customer/account/logout/"]',
                btnCloseSelector: '#ajaxlogin_btn_close_popup',
                btnToRegisterSelector: '#ajaxlogin_btn_to_register',
                btnToLoginSelector: '#ajaxlogin_btn_to_login',
                baseUrl: ''
            }
        },

        _bind: function () {
            if (this.options.ajaxSuite.enabled == true && this.options.ajaxLogin.enabled == true) {
                this.initElements();
                this.makeColor();
                this.initEvents();
            }
        },

        initElements: function () {
            this.options.popupWrapper = $(this.options.popupWrapperSelector);
            this.options.popup = $(this.options.popupSelector);
            this.options.popupBlank = $(this.options.popupBlankSelector);
            this.options.close = $(this.options.closePopupButtonSelector);
            this.options.loginWrapper = $(this.options.ajaxLogin.loginWrapperSelector);
            this.options.popupMessage = $('.mb-message-popup');
        },

        initEvents: function () {
            var self = this;

            //For social login
            if (self.options.ajaxLogin.socialLoginEnable) {
                // facebook
                if (self.options.ajaxLogin.facebookAppId) {
                    (function (d, s, id) {
                        var js, fjs = d.getElementsByTagName(s)[0];
                        if (d.getElementById(id)) {
                            return;
                        }
                        js = d.createElement(s);
                        js.id = id;
                        js.src = "//connect.facebook.net/en_US/sdk.js";
                        fjs.parentNode.insertBefore(js, fjs);
                    }(document, 'script', 'facebook-jssdk'));
                    window.fbAsyncInit = function () {
                        var appId = self.options.ajaxLogin.facebookAppId;
                            FB.init({
                                appId: appId,
                                cookie: true,
                                xfbml: true,
                                version: 'v2.8'
                            });
                            $('body').on('click', '#facebook-login-btn', function (e) {
                                e.preventDefault();
                                if (appId) {
                                    self.fbLogin();
                                } else {
                                    alert('You must input your Facebook App Id in configuration before.');
                                }
                            });

                    };
                }

                // google plus
                if (self.options.ajaxLogin.ggPlusClientId) {
                    (function () {
                        var po = document.createElement('script');
                        po.type = 'text/javascript';
                        po.async = true;
    //                    po.src = 'https://apis.google.com/js/platform.js';
                        po.src = 'https://plus.google.com/js/client:platform.js';
                        var s = document.getElementsByTagName('script')[0];
                        s.parentNode.insertBefore(po, s);
                    })();
                    $('body').on('click', '#ggplus-login-btn', function (e) {
                        e.preventDefault();
                        self.ggPlusLogin();
                    });
                }
                
                // twitter
                if ($('#twitter-login-btn').length > 0) {
                    $('body').on('click', '#twitter-login-btn', function (e) {
                        e.preventDefault();
                        self.twitterLogin();
                    });
                }
            }

            $('body').on('click', self.options.ajaxLogin.loginElmSelector, function (e) {
                e.preventDefault();
                self.showSectionPopup('login');
            });
            $('body').on('click', self.options.ajaxLogin.registerElmSelector, function (e) {
                e.preventDefault();
                self.showSectionPopup('register');
            });
            $('body').on('click', self.options.ajaxLogin.forgotElmSelector, function (e) {
                e.preventDefault();
                self.showSectionPopup('forget');
            });
            
            $(self.options.ajaxLogin.logoutElmSelector).on('click' , function (e) {
                e.preventDefault();
                e.stopPropagation();
                self.ajaxLogoutPost();
            });

            $('body').on('click', self.options.ajaxLogin.btnCloseSelector, function (e) {
                self.closePopup();
            });
            $('body').on('click', self.options.ajaxLogin.btnToRegisterSelector, function (e) {
                e.preventDefault();
                self.showSectionPopup('register');
            });
            $('body').on('click', self.options.ajaxLogin.btnToLoginSelector, function (e) {
                e.preventDefault();
                self.showSectionPopup('login');
            });
            
            //Submit
            self.options.loginForm = self.options.loginWrapper.find('form#login-form');
            if (self.options.loginForm) {
                self.options.loginForm.mage('validation', {
                    radioCheckboxClosest: '.nested',
                    submitHandler: function (form) { 
                        self.closePopup();
                        self.ajaxLoginPost($(form).serializeArray());
                        return false;
                    }
                });
            }
            self.options.registerForm = self.options.loginWrapper.find('form#register-form');
            if (self.options.registerForm) {
                self.options.registerForm.mage('validation', {
                    radioCheckboxClosest: '.nested',
                    submitHandler: function (form) { 
                        self.closePopup();
                        self.ajaxRegistertPost($(form).serializeArray());
                        return false;
                    }
                });
            }
            self.options.forgetpasswordForm = self.options.loginWrapper.find('form#forgetpassword-form');
            if (self.options.forgetpasswordForm) {
                self.options.forgetpasswordForm.mage('validation', {
                    radioCheckboxClosest: '.nested',
                    submitHandler: function (form) {
                        self.closePopup();
                        self.ajaxForgotpasswordPost($(form).serializeArray());
                        return false;
                    }
                });
            }
        },

        ajaxLoginPost: function (formData) {
            var self = this;
            $.ajax({
                url: self.options.ajaxLogin.ajaxLoginUrl,
                data: formData,
                type: 'POST',
                datatype: 'json',
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
                        self.options.popupMessage.html(res.html_popup);
                        self.makeColor();
                        self.showSectionPopup('message');

                        if (res.error) {
                            self.options.popupMessage.find('.mb-error-message').find('p.message').html(res.error);
                        } else if (res.success) {
                            self.options.popupMessage.find('.mb-successful-message').find('p.message').html(res.success);
                            self.redirectAfterLogin();
                        }
                    }
                }
            });
        },

        redirectAfterLogin: function () {
            var baseUrl = this.options.ajaxLogin.baseUrl;
            var customerPageUrl = baseUrl + 'customer/account';
            var cartUrl = baseUrl + 'checkout/cart';
            var wishlistUrl = baseUrl + 'wishlist';
            if (this.options.ajaxLogin.urlRedirect == 0) {
                setTimeout(function(){location.reload();},1000);
            } else if (this.options.ajaxLogin.urlRedirect == 1) {
                setTimeout(function(){window.location.replace(customerPageUrl);},2000);
            } else if (this.options.ajaxLogin.urlRedirect == 2) {
                setTimeout(function(){window.location.replace(baseUrl);},2000);
            } else if (this.options.ajaxLogin.urlRedirect == 3) {
                setTimeout(function(){window.location.replace(cartUrl);},2000);
            } else if (this.options.ajaxLogin.urlRedirect == 4) {
                setTimeout(function(){window.location.replace(wishlistUrl);},2000);
            } else {
                setTimeout(function(){window.location.replace(customerPageUrl);},2000);
            }
        },

        fbLogin: function () {
            var self = this;
            FB.login(function (response) {
                if (response.status === 'connected') {
                    self.afterFacebookLogin();
                }
            }, {scope: 'public_profile, email'});
        },

        afterFacebookLogin: function () {
            var self = this;
            FB.api('/me', 'GET', {fields: 'id,name,email,first_name,last_name'}, function (response) {
                var formData = {};
                formData.firstname = response.first_name;
                formData.lastname = response.last_name;
                formData.email = response.email;
                formData.password = 'Azebiz' + response.id;
                formData.social_type = 'facebook';

                $.ajax({
                    url: self.options.ajaxLogin.ajaxSocialLoginUrl,
                    data: formData,
                    type: 'POST',
                    datatype: 'json',
                    beforeSend: function () {
                        if (self.isLoaderEnabled()) {
                            // do nothing
                            $('body').trigger(self.options.ajaxSuite.processStart);
                        }
                    },
                    success: function (res) {
                        if (self.isLoaderEnabled()) {
                            $('body').trigger(self.options.ajaxSuite.processStop);
                        }
                        if (res.html_popup) {
                            self.options.popupMessage.html(res.html_popup);
                            self.makeColor();
                            self.showSectionPopup('message');
                            if (res.error) {
                                self.options.popupMessage.find('.mb-error-message').find('p.message').html(res.error);
                            } else {
                                self.options.popupMessage.find('.mb-successful-message').find('p.message').html('You have logged in with Facebook successfully. Please wait ...');
                                self.redirectAfterLogin();
                            }
                        }
                    }
                });
            });
        },

        ggPlusLogin: function () {
            var self = this;
            var clientId = self.options.ajaxLogin.ggPlusClientId;
            if (clientId) {
                var params = {
                    clientid: clientId,
                    cookiepolicy: 'single_host_origin',
                    scope: 'email',
                    theme: 'dark',
                    callback: function (response) {
                        if (response['status']['signed_in'] && !response['_aa']) {
                            self.afterGgPlusLogin();
                        }
                    }
                };
                gapi.auth.signIn(params);
            } else {
                alert('You must input your Google+ Client Id in configuration before.');
            }
        },

        afterGgPlusLogin: function () {
            var self = this;
            gapi.client.load('plus', 'v1', function () {
                gapi.client.plus.people.get({userId: 'me'}).execute(function (response) {
                    if (response.emails) {
                        var email;
                        for (var i = 0; i < response.emails.length; i++) {
                            if (response.emails[i].type === 'account') {
                                email = response.emails[i].value;
                                var formData = {};
                                formData.firstname = response.name.givenName;
                                formData.lastname = response.name.familyName;
                                formData.email = email;
                                formData.password = 'Azebiz' + response.id;
                                formData.social_type = 'ggplus';

                                $.ajax({
                                    url: self.options.ajaxLogin.ajaxSocialLoginUrl,
                                    data: formData,
                                    type: 'POST',
                                    datatype: 'json',
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
                                            self.options.popupMessage.html(res.html_popup);
                                            self.makeColor();
                                            self.showSectionPopup('message');
                                            if (res.error) {
                                                self.options.popupMessage.find('.mb-error-message').find('p.message').html(res.error);
                                            } else {
                                                self.options.popupMessage.find('.mb-successful-message').find('p.message').html('You have logged in with Google+ successfully. Please wait ...');
                                                self.redirectAfterLogin();
                                            }
                                        }
                                    }
                                });
                            }
                        }
                    } else if (response.message) {
                        alert(response.message);
                    }
                });
            });
        },

        twitterLogin: function () {
            var self = this;

            $.ajax({
                url: self.options.ajaxLogin.ajaxTwitterUrl,
                data: {},
                type: 'POST',
                datatype: 'json',
                beforeSend: function () {
                    if (self.isLoaderEnabled()) {
                        $('body').trigger(self.options.ajaxSuite.processStart);
                    }
                },
                success: function (res) {
                    if (self.isLoaderEnabled()) {
                        $('body').trigger(self.options.ajaxSuite.processStop);
                    }

                    if (res.success) {
                        self.closePopup();
                        
                        var centerX = ($(window).width() - 500) / 2;
                        var myWindow = window.open(res.url, 'myTwitter', 'width=500,height=500,top=0,left='+centerX);
                    } else {
                        
                    }
                }
            });
        },

        ajaxRegistertPost: function (formData) {
            var self = this;
            $.ajax({
                url: self.options.ajaxLogin.ajaxRegisterUrl,
                data: formData,
                type: 'POST',
                datatype: 'json',
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
                        self.options.popupMessage.html(res.html_popup);
                        self.makeColor();
                        self.showSectionPopup('message');

                        if (res.error) {
                            self.options.popupMessage.find('.mb-error-message').find('p.message').html(res.error);
                        } else if (res.success) {
                            self.options.popupMessage.find('.mb-successful-message').find('p.message').html(res.success);
                            if (res.reload == true) {
                                self.redirectAfterLogin();
                            }
                        }
                    }
                }
            });
        },

        ajaxForgotpasswordPost: function (formData) {
            var self = this;
            $.ajax({
                url: self.options.ajaxLogin.ajaxForgotPasswordUrl,
                data: formData,
                type: 'POST',
                datatype: 'json',
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
                        self.options.popupMessage.html(res.html_popup);
                        self.makeColor();
                        self.showSectionPopup('message');

                        if (res.error) {
                            self.options.popupMessage.find('.mb-error-message').find('p.message').html(res.error);
                        } else if (res.success) {
                            self.options.popupMessage.find('.mb-successful-message').find('p.message').html(res.success);
                        }
                    }
                }
            });
        },
        
        ajaxLogoutPost: function() {
            var self = this;
            $.ajax({
                url: self.options.ajaxLogin.ajaxLogoutUrl,
                type: 'POST',
                data: {},
                datatype: 'json',
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
                        self.options.popupMessage.html(res.html_popup);
                        self.makeColor();
                        self.showSectionPopup('message');

                        if (res.error) {
                            self.options.popupMessage.find('.mb-error-message').find('p.message').html(res.error);
                        } else if (res.success) {
                            self.options.popupMessage.find('.mb-successful-message').find('p.message').html(res.success);
                            var baseUrl = self.options.ajaxLogin.baseUrl;
                            setTimeout(function(){window.location.replace(baseUrl);},2000);
                        }
                    }
                }
            });
        },

        showSectionPopup: function (section) {
            var self = this;
            var sectionSelector = '';
            switch (section) {
                case 'login':
                    sectionSelector = '.mb-login-popup';
                    break;
                case 'register':
                    sectionSelector = '.mb-register-popup';
                    break;
                case 'forget':
                    sectionSelector = '.mb-forgetpassword-popup';
                    break;
                case 'message':
                    sectionSelector = '.mb-message-popup';
                    break;
                default:
                    sectionSelector = '';
                    break;
            }
            if (sectionSelector) {
                var section = this.options.loginWrapper.children(sectionSelector);
                if (section.length) {
                    var wrapperDisplay = this.options.popupWrapper.css('display');
                    if (wrapperDisplay == 'none') {                     //Show popup
                        this.options.loginWrapper.children().hide();
                        section.show();
                        this.showElement(this.options.ajaxLogin.loginWrapperSelector);
                        setTimeout(function() {self.animateCenter(500);}, 1000);
                    } else {                                            //Switch between sections
                        this.options.loginWrapper.children().hide();
                        this.animationSlide(section);
                        setTimeout(function() {self.animateCenter(500);}, 1000);
                    }
                }
            }
            self.makeColor();
        },

    });

    return $.tigren.ajaxLogin;
});