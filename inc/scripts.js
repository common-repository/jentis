(function (sCDN, sCDNProject, sCDNWorkspace, sCDNVers) {
    if (
        window.localStorage !== null &&
        typeof window.localStorage === 'object' &&
        typeof window.localStorage.getItem === 'function' &&
        window.sessionStorage !== null &&
        typeof window.sessionStorage === 'object' &&
        typeof window.sessionStorage.getItem === 'function'
    ) {
        sCDNVers =
            window.sessionStorage.getItem('jts_preview_version') ||
            window.localStorage.getItem('jts_preview_version') ||
            sCDNVers;
    }
    window.jentis = window.jentis || {};
    window.jentis.config = window.jentis.config || {};
    window.jentis.config.frontend = window.jentis.config.frontend || {};
    window.jentis.config.frontend.cdnhost =
        sCDN + '/get/' + sCDNWorkspace + '/web/' + sCDNVers + '/';
    window.jentis.config.frontend.vers = sCDNVers;
    window.jentis.config.frontend.env = sCDNWorkspace;
    window.jentis.config.frontend.project = sCDNProject;
    window._jts = window._jts || [];
    var f = document.getElementsByTagName('script')[0];
    var j = document.createElement('script');
    j.async = true;
    j.src = window.jentis.config.frontend.cdnhost + jts_data.jts_hash + '.js';
    f.parentNode.insertBefore(j, f);
})(jts_data.jts_tracking_domain, jts_data.jts_container_id, 'live', '_');

class JtsTracking {
    singpleProductAddToCartEventBind(productData) {
        let single_btn = document.querySelectorAll(
            'button[class*="btn-buy-shop"],button[class*="single_add_to_cart_button"], button[class*="add_to_cart"]'
        );
        if (single_btn.length > 0) {
            single_btn[0].addEventListener("click", () => {
                productData.quantity = parseInt(
                    document.querySelector('[name="quantity"]').value
                );
                productData.variant = document.querySelectorAll(
                    '[name="woocommerce-product-attributes-item__value"]'
                ).values;

                this.addToCartClick(productData);
            });
        }
    }

    removeFromCartEventBind() {
        document.body.addEventListener("click", async (e) => {
            if (e.target.matches(".remove, .remove_from_cart_button")) {
                const productData = await this.getProductDataById(
                    e.target.dataset.product_id
                );

                this.removeFromCart(productData[0]);
            }
        });
    }

    ajaxAddToCartClickEvenetBind() {
        document.body.addEventListener("click", async (e) => {

            if (e.target.matches(".ajax_add_to_cart")) {
                const productData = await this.getProductDataById(
                    e.target.dataset.product_id
                );
                productData[0].quantity = 1;
                this.addToCartClick(productData[0]);
            }
        });
    }

    async productListViewBind(viewName) {
        const product_items = document.querySelectorAll("[data-product_id]");

        if (product_items.length > 0) {
            let productIds = "";
            product_items.forEach((element, key) => {
                if (!isMiniCartProducts(element)) {
                    productIds += `${element.dataset.product_id}${
                        key === product_items.length - 1 ? "" : ","
                    }`;
                }
            });

            if (productIds !== "") {
                const productData = await this.getProductDataById(productIds);

                this.productListView(productData, viewName);
            }
        }
    }

    async cartViewEventBind(cartData) {
        this.cartViewed(cartData);
    }

    async productListClickBind(pageName) {
        document.body.addEventListener("click", async (e) => {
            let pid, name, position;
            if (e.target.matches(".woocommerce-LoopProduct-link, .woocommerce-loop-product__link, .attachment-woocommerce_thumbnail")) {
                const prodcutClassList = e.target
                    .closest(".product")
                    .className.split(" ");

                if (prodcutClassList.some((pcl) => ["product", "type-product"].includes(pcl))) {
                    pid = parseInt(
                        prodcutClassList.findProductIdFromList(/post-(\d+$)/).length ?
                            prodcutClassList
                                .findProductIdFromList(/post-(\d+$)/)[0]
                                .split("-")[1]
                            : 0
                    );

                    name = e.target
                        .closest(".product")
                        .querySelectorAll(".woocommerce-loop-product__title").length ?
                        e.target
                            .closest(".product")
                            .querySelectorAll(".woocommerce-loop-product__title")[0]
                            .textContent
                        : "";

                    position = document.querySelectorAll(".product").length ?
                        [...document.querySelectorAll(".product")].indexOf(
                            e.target.closest(".product")
                        ) + 1
                        : 0;

                    const productData = {
                        name: name,
                        id: pid,
                        position: position,
                    };

                    this.productListClick(productData, pageName);
                }
            }
        });
    }

    productViewBind(productData) {
        productData.quantity = 1;
        productData.position = 1;
        this.productView(productData);
    }

    async searchBind(searchQuery) {
        const product_items = document.querySelectorAll("[data-product_id]");
        if (product_items.length > 0) {
            let productIds = "";
            product_items.forEach((element, key) => {
                if (!isMiniCartProducts(element)) {
                    productIds += `${element.dataset.product_id}${
                        key === product_items.length - 1 ? '' : ','
                    }`;
                }
            });

            if (productIds !== '') {
                const productData = await this.getProductDataById(productIds);
                this.searched(productData, searchQuery);
            } else {
                const productData = [];
                this.searched(productData, searchQuery);
            }
        }
    }

    checkoutPageBind(checkoutData) {
        this.checkoutView(checkoutData);
    }

    orderPageBind(orderData) {
        this.orderView(orderData);
    }

    pageView() {
        _jts.push({
            track: 'pageview',
        }, true);


    }

    addToCartClick(productData) {


        _jts.push({
            track: 'product',
            type: 'addtocart',
            id: productData.id,
            name: productData.name,
            brutto: productData.brutto,
            netto: productData.netto,
            affiliation: 'jentis',
            quantity: productData.quantity,
            position: 1,
        });

        _jts.push({
            track: 'addtocart',
        }, true);


    }

    async removeFromCart(productData) {


        _jts.push({
            track: 'product',
            type: 'removefromcart',
            id: productData.id,
            name: productData.name,
            brutto: productData.brutto,
            netto: productData.netto,
            affiliation: 'jentis',
            position: productData.position,
        });

        _jts.push({
            track: 'removefromcart',
        }, true);


    }

    cartViewed(productData) {
        _jts.push({
            track: 'pageview',
        });

        if (productData.data.length > 0) {
            productData.data.forEach((element) => {
                _jts.push({
                    track: 'product',
                    type: 'cartview',
                    id: element.id,
                    name: element.name,
                    brutto: element.brutto,
                    netto: element.netto,
                    quantity: element.quantity
                });
            });

            _jts.push({
                track: 'cartview',
                brutto: productData.brutto,
                netto: productData.netto,
            });
        } else {
            _jts.push({
                track: 'cartview',
            });
        }

        _jts.push({
            track: 'submit',
        });
    }

    productListView(productData, name) {
        _jts.push({
            track: 'pageview',
        });


        if (productData.length > 0) {
            productData.forEach((element) => {
                _jts.push({
                    track: 'product',
                    type: 'productlist',
                    id: element.id,
                    name: element.name,
                    brutto: element.brutto,
                    netto: element.netto,
                    affiliation: 'jentis',
                    variant: element.variant,
                    position: element.position,
                    quantity: 1,
                });
            });

            _jts.push({
                track: 'productlist',
                name: name,
            });


        }

        _jts.push({
            track: 'submit',
        });
    }

    productListClick(productData, pageName) {


        _jts.push({
            track: 'product',
            type: 'productlistclick',
            id: productData.id,
            name: productData.name,
            position: productData.position,
        });

        _jts.push({
            track: 'productlistclick',
            name: pageName,
        }, true);
    }

    productView(productData) {


        _jts.push({
            track: 'pageview',
        });

        _jts.push({
            track: 'product',
            type: 'productview',
            id: productData.id,
            name: productData.name,
            brutto: productData.brutto,
            netto: productData.netto,
            affiliation: 'jentis',
            quantity: 1,
            position: productData.position,
        });

        _jts.push({
            track: 'productview',
        });

        _jts.push({
            track: 'submit',
        });


    }

    searched(productData, searchQuery) {
        _jts.push({
            track: 'pageview',
        });


        if (productData.length > 0) {
            productData.forEach((element) => {
                _jts.push({
                    track: 'product',
                    type: 'search',
                    id: element.id,
                    name: element.name,
                    brutto: element.brutto,
                    netto: element.netto,
                });
            });

            _jts.push(
                {
                    track: 'search',
                    group: 'global site group',
                    term: searchQuery,
                    countresults: productData.length,
                });

        } else {
            _jts.push({
                track: 'search',
                group: 'global site search',
                term: searchQuery,
                countresults: 0,
            });
        }

        _jts.push({
            track: 'submit',
        });
    }

    checkoutView(checkoutData) {


        _jts.push({
            track: 'pageview',
        });

        if (checkoutData.items.length > 0) {
            checkoutData.items.forEach((element) => {
                _jts.push({
                    track: 'product',
                    type: 'checkout',
                    id: element.id,
                    name: element.name,
                    brutto: element.brutto,
                    quantity: element.quantity,
                });
            });

            _jts.push({
                track: 'checkout',
                step: 1,
                brutto: checkoutData.brutto
            });
        }

        _jts.push({
            track: 'submit',
        });

        (function ($) {
            'use strict';
            let checkout_form = jQuery("form.checkout");

            checkout_form.on("checkout_place_order", function (e) {
                const paytype = document.querySelectorAll("[name^='payment_method']:checked")[0].value;

                _jts.push({
                    track: 'checkoutoption',
                    key: 'paymentmethod',
                    value: paytype,
                }, true);


            });
        })(jQuery);
    }

    orderView(orderData) {

        _jts.push({
            track: 'pageview',
        });

        if (orderData.data.length > 0) {
            orderData.data.forEach((element) => {
                _jts.push({
                    track: 'product',
                    type: 'order',
                    id: element.id,
                    name: element.name,
                    brutto: element.brutto,
                    netto: element.netto,
                    affiliation: 'jentis',
                    variant: element.variant,
                    quantity: element.quantity,
                    position: element.position,
                });
            });

            if (orderData.coupons.length > 0) {
                orderData.coupons.forEach((element) => {

                    _jts.push(
                        {
                            track: 'order',
                            orderid: orderData.id,
                            brutto: orderData.orderBrutto,
                            netto: orderData.orderNetto,
                            zip: orderData.zip,
                            country: orderData.country,
                            city: orderData.city,
                            paytype: orderData.paytype,
                            vouchers: [
                                {
                                    value: element.value,
                                    name: element.name,
                                    type: element.type,
                                    appliedAmount: element.value,
                                    code: element.code,
                                },
                            ],
                        }
                    );
                });

            } else {
                _jts.push({
                    track: 'order',
                    orderid: orderData.id,
                    brutto: orderData.orderBrutto,
                    netto: orderData.orderNetto,
                    zip: orderData.zip,
                    country: orderData.country,
                    city: orderData.city,
                    paytype: orderData.paytype,
                    shipping: orderData.shipping,
                    tax: orderData.tax
                });
            }

        }

        _jts.push({
            track: 'submit',
        });
    }

    searchBindOnePage(productData, term) {


        _jts.push({
            track: 'product',
            type: 'search',
            id: productData.id,
            name: productData.name,
            brutto: productData.brutto,
            netto: productData.netto,
            affiliation: "jentis",
            quantity: 1,
        });

        _jts.push({
            track: 'search',
            group: 'global site group',
            term: term,
            countresults: 1,
        }, true);


    }

    getProductDataById(pId) {
        if (pId) {
            let formData = new FormData();
            formData.append("action", "get_product_data_by_product_id");
            formData.append("productId", pId);

            return fetch(jts_ajax.jts_ajaxurl, {
                method: "POST",
                body: formData,
            })
                .then((res) => res.json())
                .then((response) => {

                    return response;
                })
                .catch((error) => console.error("Error:", error));
        }
    }
}

Array.prototype.findProductIdFromList = function (regex) {
    const arr = this;
    const matches = arr.filter(function (e) {
        return regex.test(e);
    });
    return matches;
};

function isMiniCartProducts(element) {
    if (element.className.split(" ").some((pcl) => ["remove", "remove_from_cart_button"].includes(pcl))) {
        return true;
    }

    return false;
}
