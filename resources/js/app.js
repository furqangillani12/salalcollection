import "./bootstrap";
import Alpine from "alpinejs";
window.Alpine = Alpine;
Alpine.start();

document.addEventListener("DOMContentLoaded", function () {
    // ── BAIL OUT ON POS PAGE — blade has its own full JS ──────────
    if (document.querySelector(".pos-root")) return;
    // ─────────────────────────────────────────────────────────────

    const cart = [];
    let currentPaymentMethod = "";
    let taxRate = window.taxRate || 0;

    const taxInput = document.getElementById("custom_tax");
    if (taxInput) {
        taxInput.addEventListener("input", function () {
            taxRate = parseFloat(this.value) || 0;
            updateCartDisplay();
        });
    }

    const productItems = document.querySelectorAll(".product-item");
    const productSearch = document.getElementById("productSearch");
    const searchButton = document.querySelector("#productSearch + button");
    const cartItemsContainer = document.querySelector(".cart-items");
    const emptyCartMessage = document.querySelector(".empty-cart-message");
    const subtotalEl = document.querySelector(".subtotal");
    const taxEl = document.querySelector(".tax");
    const totalEl = document.querySelector(".total");
    const checkoutBtn = document.querySelector(".checkout-btn");
    const clearCartBtn = document.querySelector(".clear-cart-btn");
    const customerSelect = document.querySelector(".customer-select");
    const customerTypeSelect = document.querySelector(".customer-type-select");
    const categoryTabs = document.querySelectorAll(".category-tab");

    // Discount
    const discountInput = document.getElementById("discount");
    let discountValue = 0;
    if (discountInput) {
        discountInput.addEventListener("input", function () {
            discountValue = parseFloat(this.value) || 0;
            updateCartDisplay();
        });
    }

    // Delivery charges
    const deliveryChargesInput = document.getElementById("delivery_charges");
    let deliveryCharges = 0;
    if (deliveryChargesInput) {
        deliveryChargesInput.addEventListener("input", function () {
            deliveryCharges = parseFloat(this.value) || 0;
            updateCartDisplay();
        });
    }

    /* -------------------------------
       AUTO PRICE MODE FROM CUSTOMER
    --------------------------------*/
    if (customerSelect && customerTypeSelect) {
        customerSelect.addEventListener("change", function () {
            const option = this.options[this.selectedIndex];
            if (!option) return;

            const type = option.dataset.type || "walkin";

            customerTypeSelect.value = type;
            customerTypeSelect.dispatchEvent(new Event("change"));

            console.log("Customer Type Selected:", type);
        });
    }

    /* -------------------------------
       SEARCH
    --------------------------------*/
    if (productSearch) {
        const performSearch = () => {
            const term = productSearch.value.toLowerCase();
            productItems.forEach((item) => {
                const name = item.dataset.name.toLowerCase();
                const barcode = item.dataset.barcode?.toLowerCase() || "";
                item.style.display =
                    name.includes(term) || barcode.includes(term)
                        ? "block"
                        : "none";
            });
        };
        productSearch.addEventListener("input", performSearch);
        if (searchButton) {
            searchButton.addEventListener("click", () => {
                productSearch.value = "";
                performSearch();
            });
        }
    }

    /* -------------------------------
       CATEGORY FILTER
    --------------------------------*/
    categoryTabs.forEach((tab) => {
        tab.addEventListener("click", function () {
            const cat = this.dataset.category;
            productItems.forEach((item) => {
                item.style.display =
                    cat === "all" || item.dataset.categoryId === cat
                        ? "block"
                        : "none";
            });
        });
    });

    /* -------------------------------
       PRICE CHANGE
    --------------------------------*/
    if (customerTypeSelect) {
        customerTypeSelect.addEventListener("change", function () {
            const type = this.value;
            productItems.forEach((item) => {
                let price = parseFloat(item.dataset.salePrice);
                if (type === "reseller")
                    price = parseFloat(item.dataset.resalePrice);
                else if (type === "wholesale")
                    price = parseFloat(item.dataset.wholesalePrice);

                item.dataset.price = price;
                const priceText = item.querySelector(".price-text");
                if (priceText)
                    priceText.textContent = `Rs. ${price.toFixed(2)}`;
            });

            cart.forEach((c) => {
                const el = [...productItems].find(
                    (i) => i.dataset.id == c.product_id,
                );
                if (el) {
                    c.unit_price = parseFloat(el.dataset.price);
                    c.total_price = c.unit_price * c.quantity;
                }
            });
            updateCartDisplay();
        });
    }

    /* -------------------------------
       ADD PRODUCT
    --------------------------------*/
    productItems.forEach((item) => {
        item.addEventListener("click", function () {
            const id = this.dataset.id;
            const name = this.dataset.name;
            const price = parseFloat(this.dataset.price);

            const existing = cart.find((i) => i.product_id == id);
            if (existing) {
                existing.quantity++;
                existing.total_price = existing.quantity * price;
            } else {
                cart.push({
                    product_id: parseInt(id),
                    product_name: name,
                    quantity: 1,
                    unit_price: price,
                    total_price: price,
                    weight: parseFloat(this.dataset.weight) || 0,
                });
            }
            updateCartDisplay();
        });
    });

    if (clearCartBtn) {
        clearCartBtn.addEventListener("click", () => {
            if (cart.length && confirm("Clear cart?")) {
                cart.length = 0;
                updateCartDisplay();
            }
        });
    }

    /* -------------------------------
       CART UI
    --------------------------------*/
    function updateCartDisplay() {
        if (!cartItemsContainer) return;
        cartItemsContainer.innerHTML = "";
        if (!cart.length) {
            if (emptyCartMessage) emptyCartMessage.classList.remove("hidden");
            cartItemsContainer.classList.add("hidden");
            if (subtotalEl) subtotalEl.textContent = "Rs. 0.00";
            if (taxEl) taxEl.textContent = "Rs. 0.00";
            if (totalEl) totalEl.textContent = "Rs. 0.00";
            updateCheckoutButton();
            return;
        }

        if (emptyCartMessage) emptyCartMessage.classList.add("hidden");
        cartItemsContainer.classList.remove("hidden");

        let subtotal = 0;
        let totalWeight = 0;
        cart.forEach((item, index) => {
            subtotal += item.total_price;
            totalWeight += (item.weight || 0) * item.quantity;
            const el = document.createElement("div");
            el.className =
                "flex justify-between items-center p-3 bg-gray-50 rounded mb-2";
            el.innerHTML = `
                <div>
                    <div class="font-medium">${item.product_name}</div>
                    <div class="flex items-center mt-2">
                        <button class="decrease-quantity px-2 py-1 border" data-index="${index}">-</button>
                        <span class="px-3">${item.quantity}</span>
                        <button class="increase-quantity px-2 py-1 border" data-index="${index}">+</button>
                        <button class="remove-item ml-3 text-red-500 text-sm" data-index="${index}">Remove</button>
                    </div>
                </div>
                <div>Rs. ${item.total_price.toFixed(2)}</div>
            `;
            cartItemsContainer.appendChild(el);
        });

        const tax = subtotal * (taxRate / 100);
        let total = subtotal + tax - discountValue + deliveryCharges;
        if (total < 0) total = 0;
        const weightEl = document.querySelector(".total-weight");
        if (weightEl) {
            weightEl.textContent = `${totalWeight.toFixed(2)} kg`;
        }

        if (subtotalEl) subtotalEl.textContent = `Rs. ${subtotal.toFixed(2)}`;
        if (taxEl) taxEl.textContent = `Rs. ${tax.toFixed(2)}`;
        if (totalEl) totalEl.textContent = `Rs. ${total.toFixed(2)}`;
        updateCheckoutButton();
    }

    /* -------------------------------
       QUANTITY + REMOVE HANDLERS
    --------------------------------*/
    document.addEventListener("click", function (e) {
        const i = e.target.dataset.index;
        if (e.target.classList.contains("increase-quantity")) {
            cart[i].quantity++;
            cart[i].total_price = cart[i].unit_price * cart[i].quantity;
            updateCartDisplay();
        }
        if (e.target.classList.contains("decrease-quantity")) {
            if (cart[i].quantity > 1) {
                cart[i].quantity--;
                cart[i].total_price = cart[i].unit_price * cart[i].quantity;
                updateCartDisplay();
            }
        }
        if (e.target.classList.contains("remove-item")) {
            cart.splice(i, 1);
            updateCartDisplay();
        }
    });

    /* -------------------------------
       DISPATCH LOGIC
    --------------------------------*/
    const dispatchSelect = document.getElementById("dispatch_method");
    const trackingField = document.getElementById("tracking_id_field");
    const deliveryChargesField = document.getElementById(
        "delivery_charges_field",
    );

    function toggleDispatchFields() {
        if (!dispatchSelect) return;

        const method = dispatchSelect.value;
        const needsTracking =
            method.includes("TCS") || method.includes("Pak Post");

        if (needsTracking) {
            if (trackingField) trackingField.classList.remove("hidden");
            if (deliveryChargesField)
                deliveryChargesField.classList.remove("hidden");
        } else {
            if (trackingField) trackingField.classList.add("hidden");
            if (deliveryChargesField)
                deliveryChargesField.classList.add("hidden");
            deliveryCharges = 0;
            if (deliveryChargesInput) deliveryChargesInput.value = 0;
            updateCartDisplay();
        }
    }

    if (dispatchSelect) {
        dispatchSelect.addEventListener("change", toggleDispatchFields);
        toggleDispatchFields();
    }

    /* -------------------------------
       CHECKOUT
    --------------------------------*/
    if (checkoutBtn) {
        checkoutBtn.addEventListener("click", async function () {
            if (!cart.length) {
                alert("Please add items to cart");
                return;
            }

            const paymentMethod =
                document.getElementById("payment_method")?.value || "cash";
            const customerId = document.getElementById("customerSelect")?.value;

            if (paymentMethod === "credit") {
                if (!customerId) {
                    alert("Please select a customer for credit purchase");
                    return;
                }

                const subtotal = cart.reduce(
                    (sum, item) => sum + item.total_price,
                    0,
                );
                const tax = subtotal * (taxRate / 100);
                const total = subtotal + tax - discountValue + deliveryCharges;

                try {
                    const checkResponse = await fetch(
                        `/admin/customers/${customerId}/check-credit`,
                        {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": document.querySelector(
                                    'meta[name="csrf-token"]',
                                ).content,
                            },
                            body: JSON.stringify({ amount: total }),
                        },
                    );

                    const checkData = await checkResponse.json();

                    if (!checkData.success) {
                        alert(
                            checkData.message +
                                `\nAvailable Credit: Rs. ${checkData.available_credit?.toFixed(2) || "0.00"}`,
                        );
                        return;
                    }

                    if (
                        !confirm(
                            `Process credit sale of Rs. ${total.toFixed(2)}?\n\nThis amount will be added to customer's ledger.`,
                        )
                    ) {
                        return;
                    }
                } catch (error) {
                    console.error("Credit check error:", error);
                    alert("Error checking credit limit");
                    return;
                }
            }

            try {
                const posRoute =
                    document.querySelector("[data-pos-route]")?.dataset
                        .posRoute;
                if (!posRoute) {
                    alert("POS route not found");
                    return;
                }

                checkoutBtn.disabled = true;
                checkoutBtn.textContent = "Processing...";

                const response = await fetch(posRoute, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document.querySelector(
                            'meta[name="csrf-token"]',
                        ).content,
                        Accept: "application/json",
                    },
                    body: JSON.stringify({
                        customer_id: customerId || null,
                        customer_type: customerTypeSelect
                            ? customerTypeSelect.value
                            : "walkin",
                        items: cart.map((item) => ({
                            product_id: item.product_id,
                            quantity: item.quantity,
                        })),
                        payment_method: paymentMethod,
                        dispatch_method:
                            document.getElementById("dispatch_method")?.value ||
                            null,
                        tracking_id:
                            document.getElementById("tracking_id")?.value ||
                            null,
                        delivery_charges: deliveryCharges,
                        tax_rate: taxRate,
                        discount: discountValue,
                    }),
                });

                const data = await response.json();

                if (!response.ok || !data.success) {
                    throw new Error(data.message || "Checkout failed");
                }

                alert("Order processed successfully!");
                cart.length = 0;
                updateCartDisplay();

                if (data.receipt_url) {
                    window.open(data.receipt_url, "_blank");
                }
            } catch (err) {
                console.error(err);
                alert("Error: " + err.message);
            } finally {
                checkoutBtn.disabled = false;
                checkoutBtn.textContent = "Process Payment";
            }
        });
    }

    function updateCheckoutButton() {
        if (!checkoutBtn) return;
        checkoutBtn.disabled = cart.length === 0;
        checkoutBtn.classList.toggle("opacity-50", checkoutBtn.disabled);
    }
});
