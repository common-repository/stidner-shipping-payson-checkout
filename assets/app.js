jQuery(document).ready(function ($) {
    localStorage.setItem('__stidner_cart_updated', '0');

    $(function () {
        $( '.validate-required' ).each(function() {
            var tc = $(this).find('input:checkbox:first');
            tcId = tc.attr('id');
            if (localStorage.getItem(tcId) === 'true') {
                tc.prop('checked', true);
            }
            tc.bind('change', function() {
                localStorage.setItem(tcId, $( this ).prop('checked'));
                validate_field();
            });
        });
        
        validate_field = function() {
            validated = true;
            $( '.validate-required' ).each(function() {
                $parent = $( this );
                var $tc = $(this).find('input:checkbox:first');
                if ( 'checkbox' === $tc.attr( 'type' ) && ! $tc.is( ':checked' ) ) {
                    $parent.removeClass( 'woocommerce-validated' ).addClass( 'woocommerce-invalid woocommerce-invalid-required-field' );
                    validated = false;
                } else {
                    $parent.removeClass( 'woocommerce-invalid woocommerce-invalid-required-field woocommerce-invalid-email' ).addClass( 'woocommerce-validated' );
                }
            });
            
            if ( validated ) {
                $('.hide-stidner-payson-iframe-container').removeClass( 'hide-stidner-payson-iframe-container' );
            } else {
                $('.stidner-payson-iframe-container').addClass( 'hide-stidner-payson-iframe-container' );
            }
        };
        
        validate_field();

        __payson_iframe_lock = function () {
            var iframe = document.getElementById('paysonIframe');
            iframe.contentWindow.postMessage(
                'lock',
                '*'
            );
        };
        __payson_iframe_unlock = function () {
            var iframe = document.getElementById('paysonIframe');

            iframe.contentWindow.postMessage(
                'updatePage',
                '*'
            );
        };
        StidnerIntegration = function () {
            this.iframe = document.getElementById("__stidner_shipping_iframe");
            window.addEventListener(
                "message",
                function (messageEvent) {
                    if (messageEvent.data.action == 'order_updated') {
                        var action = null;

                        if (messageEvent.data.attributes.changes.indexOf('shipping_price') !== -1) {
                            action = stidner.shipping_option_updated;
                        } else if (messageEvent.data.attributes.changes.indexOf('recipient_address') !== -1) {
                            action = stidner.stidner_widget_address_updated;
                        }

                        if (action !== null) {
                            __payson_iframe_lock();
                            $.get(stidner.ajax_url, {action: action})
                                .done(function (data) {
                                    //console.log(data);
                                    if (data.data !== undefined && data.data.payson_order_replaced) {
                                        var iframe = document.getElementById("paysonIframe");
                                        var src = iframe.src;
                                        if (src.match(data.data.old_payson_order, data.data.new_payson_order)) {
                                            iframe.src = src.replace(data.data.old_payson_order, data.data.new_payson_order);
                                        } else {
                                            location.reload();
                                        }
                                    } else {
                                        if (data.data === undefined) {
                                            console.log(JSON.stringify(data));
                                        }
                                        __payson_iframe_unlock();
                                    }
                                })
                                .fail(function (data) {
                                    console.log('Failed: ' + data);
                                    __payson_iframe_unlock();
                                });
                        }
                    }
                },
                true
            );
        };
        $StidnerIntegration = new StidnerIntegration();
        $(".woocommerce-cart-form").on("submit", function () {
            __stidner_shipping_iframe_lock();
            __payson_iframe_lock();
        });
        $(document.body).on(
            'updated_cart_totals', function () {
                localStorage.setItem('__stidner_cart_updated', '1');
                __stidner_shipping_iframe_unlock();
                $.get(stidner.ajax_url, {action: 'stidner_cart_updated'})
                    .done(function (data) {
                        console.log('TODO: ' + data);
                        //todo replace payson order
                        if (data != '') {
                            $("#paysonContainer").replaceWith(data);
                        } else {
                            console.log('Data failed, reloading');
                            location.reload();
                        }
                    })
                    .fail(function (data) {
                        console.log('Failed: ' + data);
                        __payson_iframe_unlock();
                    });
            }
        );
        $(window).on('storage', function (e) {
            var storageEvent = e.originalEvent;
            if ((storageEvent.key == '__stidner_cart_updated') && (storageEvent.oldValue == '0') && (storageEvent.newValue == '1')) {
                localStorage.setItem('__stidner_cart_updated', '0');
                location.reload();
            }
        });
        document.addEventListener("PaysonEmbeddedAddressChanged", function (evt) {
            __stidner_shipping_iframe_lock();
            __payson_iframe_lock();
            $.post(stidner.ajax_url, {action: stidner.address_updated, data: evt.detail})
                .done(function () {
                    __stidner_shipping_iframe_unlock();
                    __payson_iframe_unlock();
                })
                .fail(function (data) {
                    console.log('Failed: ' + data);
                    //todo show some error message
                    __stidner_shipping_iframe_unlock();
                    __payson_iframe_unlock();
                });
        });
    });

});