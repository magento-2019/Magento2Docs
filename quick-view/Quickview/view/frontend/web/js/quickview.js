(function (factory) {
    if (typeof define === "function" && define.amd) {
        define([
            "jquery",
            'mage/translate',
            'Magento_Catalog/product/view/validation',
            'MageArray_Quickview/js/colorbox/jquery.colorbox-min',
			
        ], factory);
    } else {
        factory(jQuery);
    }
}(function ($, $t) {
    "use strict";

    $.widget('ma.QuickView', {
        options: {
            baseUrl: '/',
            showPopupTitle: true,
            popupTitle: $t('Quick View'),
            currentText: $t('Product {current} of {total}'),
            previousText: $t('Preview'),
            nextText: $t('Next'),
            closeText: $t('Close'),
            transition: "fade",
            speed: "300",
            maxWidth: "1260",
            initialWidth: "75",
            initialHeight: "75",
            itemClass: '.product-item',
            btnLabel: $t('Quick View'),
            btnContainer: '.product-item-info',
            handlerClassName: 'ma-quick-view-button',
            additionClass: '',
            addToCartButtonSelector: '.action.tocart',
            addToCartButtonDisabledClass: 'disabled',
            addToCartButtonTextWhileAdding: $t('Adding...'),
            addToCartButtonTextAdded: $t('Added'),
            addToCartButtonTextDefault: $t('Add to Cart'),
            addToCartStatusSelector: 'ma-add-cart-status',
            minicartSelector: '[data-block="minicart"]'

        },
        _create: function () {
		
            //we don't apply quick view functions in product detail page.
            if (!$('body').hasClass('catalog-product-view')) {
                //init quick view buttons
                this.addQuickView(this.options);
                //bind events for quick view popup with colorbox
                this.quickViewPopup(this.options);
            }
        },
        addQuickView: function (config) {
			
	
            $(config.itemClass).each(function () {
				
			
                if (!$(this).find('.' + config.handlerClassName).length) {
					
                    var groupName = $(this).parent().attr('class').replace(' ', '-');
				
                    var productId = $(this).find('.price-box').data('product-id');
					
                    if (productId != 'undefined') {
                        var url = config.baseUrl + 'quickview/index/view/id/' + productId;
                        var btnQuickView = '<div class="ma-quick-view-btn-container">';
                        btnQuickView += '<a rel="' + groupName + '" class="' + config.handlerClassName + '" href="' + url + '"';
                        if (config.showPopupTitle) {
                            btnQuickView += ' title="' + config.popupTitle + '"';
                        }
                        btnQuickView += ' >';
                        btnQuickView += '<span>' + config.btnLabel + '</span></a>';
                        btnQuickView += '</div>';
                        $(this).find(config.btnContainer).prepend(btnQuickView);
                    }
                }
            });
        },
		quickViewPopup: function (config) {
			
            var self = this;
            $('.' + config.handlerClassName).each(function (i, el) {
                $(this).colorbox({
                    className: config.additionClass,
                    maxWidth: config.maxWidth,
                    initialWidth: config.initialWidth,
                    initialHeight: config.initialHeight,
                    current: config.currentText,
                    previous: config.previousText,
                    next: config.nextText,
                    close: config.closeText,
                    transition: config.transition,
                    speed: config.speed,
                    onLoad: function () {
                        $('#cboxClose').hide();

                        //off navigation when loading if exists
                        if ($('#cboxNavigation').length) {
                            $('#cboxNavigation').hide();
                        }

                    
                        return false;
                    },
                    onComplete: function () {
                        //reposition some controls
                        self._repositionElements();

                        //if configurable product
                       self._configurableProcess();

                        //ajax add cart process
                        self.QuickAjaxAddtoCart();

                        //trigger content updated
                        $('#cboxContent').trigger('contentUpdated');
						$('#cboxContent').find('#bundle-slide').trigger('click');
						setTimeout($(this).colorbox.resize,2000);
                    },
                    onClosed: function () {
                        $('#colorbox').removeClass('colorbox');
                    }
                });
				

            });
        },
        _repositionElements: function () {
            $('#cboxLoadedContent').css({'height': 'auto'});
            $('#colorbox').addClass('colorbox');
            if (!$('#cboxNavigation').length) {
			
                $('#cboxContent').append('<div id="cboxNavigation"></div>');
				
                $('#cboxNext').appendTo('#cboxNavigation');
				
                $('#cboxPrevious').appendTo('#cboxNavigation');
					
                $('#cboxCurrent').appendTo('#cboxNavigation');
						
            } else {
                if ($('#cboxNavigation #btnGotoProduct').length) {
                    $('#cboxNavigation #btnGotoProduct').remove();
                }
                //show navigation again
                if ($('#cboxNavigation').length) {
                    $('#cboxNavigation').show();
                }
            }

            //move button go to product to navigation container
            if ($('#cboxContent #btnGotoProduct').length) {
                $('#cboxContent #btnGotoProduct').appendTo('#cboxNavigation');
            }

            //add class loading to tab contents
            $('#quick-view-content').removeClass('loading').show();

            $('#cboxClose').appendTo('#cboxWrapper').show();
        },
        _configurableProcess: function () {
            //for configurable product
			if ($('#cboxContent').find('.swatch-opt').length) {
                $('#cboxContent').find('.field.configurable').hide();
                setTimeout(function () {
                    $('#cboxContent').find('.swatch-opt').find('.swatch-option').each(function () {
                        var $elm = $(this);
                        $elm.on('click', function () {
                            $('#cboxContent').find('#product-addtocart-button').prop('disabled', true);
                            $('#cboxContent').find('#product-addtocart-button').prop('disabled', false);
                        });
                    });
                }, 200);
            }
			$(".data.switch").on('click', function (){
				
				setTimeout($(this).colorbox.resize,100);
			});
			
			//for downloadable product
			 if ($('#cboxContent').find('#downloadable-links-list').length) {
			
                //hide qty filed
                $('.box-tocart .field.qty').hide();
            }
        },
        
        QuickAjaxAddtoCart: function () {
			console.log("azdssa");  
            var self = this;
            if ($('#product_addtocart_form').length) {
                $('#product_addtocart_form').mage('validation', {
                    radioCheckboxClosest: '.nested',
                    submitHandler: function (form) {
                        self.submitForm($(form));
                        return false;
                    }
                });
            }
        },
        submitForm: function (form) {
			
            var self = this;
            if (form.has('input[type="file"]').length && form.find('input[type="file"]').val() !== '') {
                self.element.off('submit');
                form.submit();
            } else {
              
            var url = form.attr('action');
            url = url.replace("checkout/cart", "quickview/cart");
            $.ajax({
                url: url,
                data: form.serialize(),
                type: 'post',
                dataType: 'json',
                beforeSend: function () {
                    self.beforeAddToCart(form);
                },
                success: function (response) {
						
                    if (response.messages) {
                        if (!$('#' + self.options.addToCartStatusSelector).length) {
                            $('.colorbox .page-title-wrapper').prepend('<div id="' + self.options.addToCartStatusSelector + '">&nbsp;</div>');
                        }
                        $('#' + self.options.addToCartStatusSelector).fadeOut(100, function () {
                            var msg = '<div class="message ' + ((response.status) ? 'success' : 'error') + '"><span>' + response.messages + '</span></div>';
                            $('#' + self.options.addToCartStatusSelector).html(msg).fadeIn(200);
                        });
                    }

                    if (response.minicart) {
                        $(self.options.minicartSelector).replaceWith(response.minicart);
                        $(self.options.minicartSelector).trigger('contentUpdated');
                    }

                    self.afterAddToCart(form);
                }
            });
            }
        },
      
        beforeAddToCart: function (form) {
            var self = this;
            $(self.options.minicartSelector).trigger('contentLoading');

            var addToCartButton = $(form).find(self.options.addToCartButtonSelector);
            addToCartButton.addClass(self.options.addToCartButtonDisabledClass);
            addToCartButton.attr('title', self.options.addToCartButtonTextWhileAdding);
            addToCartButton.find('span').text(self.options.addToCartButtonTextWhileAdding);
        },
        afterAddToCart: function (form) {
            var self = this,
                addToCartButton = $(form).find(this.options.addToCartButtonSelector);

            addToCartButton.find('span').text(this.options.addToCartButtonTextAdded);
            addToCartButton.attr('title', this.options.addToCartButtonTextAdded);

            setTimeout(function () {
                addToCartButton.removeClass(self.options.addToCartButtonDisabledClass);
                addToCartButton.find('span').text(self.options.addToCartButtonTextDefault);
                addToCartButton.attr('title', self.options.addToCartButtonTextDefault);
            }, 1000);

            setTimeout(function () {
                $('#' + self.options.addToCartStatusSelector).fadeOut('slow');
            }, 5000);
        }

    });

    return $.ma.QuickView;
}));