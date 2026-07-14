import "./bootstrap";
import Alpine from "alpinejs";
window.Alpine = Alpine;
Alpine.start();

document.addEventListener("DOMContentLoaded", function () {
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
    const paymentMethodBtns = document.querySelectorAll(".payment-method-btn");
    const categoryTabs = document.querySelectorAll(".category-tab");

    const paymentSelect = document.getElementById("payment_method");
    if (paymentSelect) {
        paymentSelect.addEventListener("change", function () {
            currentPaymentMethod = this.value || "";
            updateCheckoutButton();
        });
    }

    // 🚀 Weight input
    const weightInput = document.getElementById("weight");
    let orderWeight = 0;
    if (weightInput) {
        weightInput.addEventListener("input", function () {
            orderWeight = parseFloat(this.value) || 0;
        });
    }

    // Discount input 🚀
    const discountInput = document.getElementById("discount");
    let discountValue = 0;
    if (discountInput) {
        discountInput.addEventListener("input", function () {
            discountValue = parseFloat(this.value) || 0;
            updateCartDisplay();
        });
    }

    // 🚀 Delivery charges input
    const deliveryChargesInput = document.getElementById("delivery_charges");
    let deliveryCharges = 0;
    if (deliveryChargesInput) {
        deliveryChargesInput.addEventListener("input", function () {
            deliveryCharges = parseFloat(this.value) || 0;
            updateCartDisplay();
        });
    }

    // 🔥 AUTO SELECT PRICE TYPE BASED ON CUSTOMER TYPE
    if (customerSelect && customerTypeSelect) {
        customerSelect.addEventListener("change", function () {
            const selectedOption = this.options[this.selectedIndex];
            let customerType = selectedOption.dataset.type;

            if (!customerType) return;

            // map DB value to dropdown value
            if (customerType === "customer") {
                customerType = "walkin";
            }

            customerTypeSelect.value = customerType;

            // trigger price change logic
            customerTypeSelect.dispatchEvent(new Event("change"));
        });
    }

    // Search
    if (productSearch) {
        const performSearch = () => {
            const term = productSearch.value.toLowerCase();
            productItems.forEach((item) => {
                const name = item.dataset.name.toLowerCase();
                const barcode = item.dataset.barcode
                    ? item.dataset.barcode.toLowerCase()
                    : "";
                if (name.includes(term) || barcode.includes(term)) {
                    item.style.display = "block";
                } else {
                    item.style.display = "none";
                }
            });
        };

        productSearch.addEventListener("input", performSearch);
        searchButton.addEventListener("click", function () {
            productSearch.value = "";
            performSearch();
        });
    }

    // Category filter
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

    // Update product prices on customer type change
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

            // Update cart prices
            cart.forEach((cartItem) => {
                const itemEl = Array.from(productItems).find(
                    (i) => i.dataset.id == cartItem.product_id,
                );
                if (itemEl) {
                    cartItem.unit_price = parseFloat(itemEl.dataset.price);
                    cartItem.total_price =
                        cartItem.unit_price * cartItem.quantity;
                }
            });

            updateCartDisplay();
        });
    }

    // Product click
    productItems.forEach((item) => {
        item.addEventListener("click", function () {
            const id = this.dataset.id;
            const name = this.dataset.name;
            const price = parseFloat(this.dataset.price);

            const existing = cart.find(
                (i) => i.product_id == id,
            );

            if (existing) {
                existing.quantity += 1;
                existing.total_price = existing.quantity * price;
                existing.unit_price = price;
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

    // Clear cart
    clearCartBtn.addEventListener("click", function () {
        if (cart.length > 0 && confirm("Clear cart?")) {
            cart.length = 0;
            updateCartDisplay();
        }
    });

    function updateCartDisplay() {
        cartItemsContainer.innerHTML = "";

        if (cart.length === 0) {
            emptyCartMessage.classList.remove("hidden");
            cartItemsContainer.classList.add("hidden");
            subtotalEl.textContent = "Rs. 0.00";
            taxEl.textContent = "Rs. 0.00";
            totalEl.textContent = "Rs. 0.00";
            return;
        }

        emptyCartMessage.classList.add("hidden");
        cartItemsContainer.classList.remove("hidden");

        let subtotal = 0;
        let totalWeight = 0;

        cart.forEach((item, index) => {
            subtotal += item.total_price;
            totalWeight += (item.weight || 0) * item.quantity;

            const itemEl = document.createElement("div");
            itemEl.className =
                "flex justify-between items-center p-3 bg-gray-50 rounded mb-2";

            itemEl.innerHTML = `
                <div>
                    <div class="font-medium">${item.product_name}</div>
                    <div class="flex items-center mt-2">
                        <button class="decrease-quantity px-2 py-1 border" data-index="${index}">-</button>
                        <span class="px-3">${item.quantity}</span>
                        <button class="increase-quantity px-2 py-1 border" data-index="${index}">+</button>
                        <button class="remove-item ml-2 text-red-500" data-index="${index}">Remove</button>
                    </div>
                </div>
                <div class="text-right">
                    <div>Rs. ${item.total_price.toFixed(2)}</div>
                    <div class="text-sm text-gray-500">Rs. ${item.unit_price.toFixed(2)}</div>
                </div>
            `;

            cartItemsContainer.appendChild(itemEl);
        });

        const tax = subtotal * (taxRate / 100);
        let total = subtotal + tax - discountValue + deliveryCharges;
        if (total < 0) total = 0;

        subtotalEl.textContent = `Rs. ${subtotal.toFixed(2)}`;
        taxEl.textContent = `Rs. ${tax.toFixed(2)}`;
        totalEl.textContent = `Rs. ${total.toFixed(2)}`;

        document.querySelector(".total-weight").textContent =
            `${totalWeight.toFixed(2)} kg`;
    }

    document.addEventListener("click", function (e) {
        if (e.target.classList.contains("increase-quantity")) {
            const index = e.target.dataset.index;
            cart[index].quantity++;
        } else if (e.target.classList.contains("decrease-quantity")) {
            const index = e.target.dataset.index;
            if (cart[index].quantity > 1) cart[index].quantity--;
        } else if (e.target.classList.contains("remove-item")) {
            const index = e.target.dataset.index;
            cart.splice(index, 1);
        } else {
            return;
        }

        cart[index]?.unit_price;
        cart[index] &&
            (cart[index].total_price =
                cart[index].unit_price * cart[index].quantity);
        updateCartDisplay();
    });
});
