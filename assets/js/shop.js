
/*======================================
        SHOP.JS
======================================*/

document.addEventListener("DOMContentLoaded", function () {

    console.log("Shop JS Loaded Successfully ✅");

/*======================================
        FILTER SIDEBAR TOGGLE
======================================*/

const filterTitles = document.querySelectorAll(".filter-title");

    filterTitles.forEach(function(title){

    title.addEventListener("click", function(){

        const content = this.nextElementSibling;
        const icon = this.querySelector(".toggle-icon");

        // Agar current filter already open hai
        if(content.classList.contains("active")){

            content.classList.remove("active");
            icon.textContent = "+";

            return;
        }

        // Baaki saare filters close karo
        filterTitles.forEach(function(otherTitle){

            const otherContent = otherTitle.nextElementSibling;
            const otherIcon = otherTitle.querySelector(".toggle-icon");

            if(otherContent){
                otherContent.classList.remove("active");
            }

            if(otherIcon){
                otherIcon.textContent = "+";
            }

        });

        // Current filter open karo
        content.classList.add("active");
        icon.textContent = "−";

    });

});

/*======================================
        WISHLIST DATABASE SYSTEM
======================================*/

const wishlistButtons =
document.querySelectorAll(".wishlist-btn");

wishlistButtons.forEach(function(button) {

    button.addEventListener("click", function(e) {

        e.preventDefault();

        const form = this.closest(".wishlist-form");

        if (!form) {
            return;
        }

        const productId =
            form.querySelector('input[name="product_id"]').value;

        const icon =
            this.querySelector("i");

        const formData =
            new FormData();

        formData.append("product_id", productId);

        formData.append("redirect", "shop");


        fetch("add-to-wishlist.php", {

            method: "POST",

            body: formData

        })

        .then(response => response.json())

        .then(data => {

            if (data.status === "login") {

                window.location.href = "login.php";

                return;

            }


            if (data.status === "success") {

                icon.classList.remove("fa-regular");

                icon.classList.add("fa-solid");

                icon.style.color = "#e63946";

                this.classList.add("active");


                const wishCounter =
                    document.getElementById("wishlistCount");

                if (wishCounter) {

                    wishCounter.textContent =
                        data.count;

                }

            }

        })

        .catch(error => {

            console.error(
                "Wishlist Error:",
                error
            );

        });

    });

});

/*======================================
            NAVBAR SEARCH
======================================*/

const searchToggle = document.querySelector(".search-toggle");
const searchInput = document.getElementById("searchInput");

if (searchToggle && searchInput) {

    searchToggle.addEventListener("click", function(e) {

        e.preventDefault();

        searchInput.scrollIntoView({
            behavior: "smooth",
            block: "center"
        });

        setTimeout(function() {
            searchInput.focus();
        }, 500);

    });

}


/*======================================
            SEARCH PRODUCTS
======================================*/

const productCards = document.querySelectorAll(".product-card");

if(searchInput){

    searchInput.addEventListener("keyup", function(){

        const searchValue = this.value.toLowerCase();

        productCards.forEach(function(card){

            const productName =
                card.dataset.name.toLowerCase();

            if(productName.includes(searchValue)){

                card.style.display = "block";

            }else{

                card.style.display = "none";

            }

        });

    });

}



    /*======================================
            SORT PRODUCTS
    ======================================*/

    const sortSelect = document.getElementById("sort");

    const productsGrid = document.querySelector(".products-grid");

    sortSelect.addEventListener("change", function(){

        const products = Array.from(document.querySelectorAll(".product-card"));

        if(this.value === "low"){

            products.sort(function(a,b){

                return Number(a.dataset.price) - Number(b.dataset.price);

            });

        }

        else if(this.value === "high"){

            products.sort(function(a,b){

                return Number(b.dataset.price) - Number(a.dataset.price);

            });

        }

        else if(this.value === "name"){

            products.sort(function(a,b){

                return a.dataset.name.localeCompare(b.dataset.name);

            });

        }

        else{

            products.sort(function(a,b){

                return 0;

            });

        }

        products.forEach(function(card){

            productsGrid.appendChild(card);

        });

    });


/*======================================
        QUICK VIEW MODAL
======================================*/

const modal = document.querySelector(".quick-view-modal");

const quickButtons = document.querySelectorAll(".quick-view-btn");

const closeModal = document.querySelector(".close-modal");

const quickImage = document.getElementById("quickImage");

const quickTitle = document.getElementById("quickTitle");

const quickCategory = document.getElementById("quickCategory");

const quickPrice = document.getElementById("quickPrice");

const quickCartButton =
document.querySelector(".quick-view-modal .modal-cart-btn");


quickButtons.forEach(function(button){
    

    button.addEventListener("click", function(){

        const card = this.closest(".product-card");

        const productId =
        card.dataset.productId ||
        card.querySelector(".cart-btn")?.dataset.productId;

        const image =
        card.querySelector("img").src;

        const title =
        card.dataset.name;

        const category =
        card.dataset.category;

        const price =
        card.dataset.price;


        quickImage.src = image;

        quickTitle.textContent = title;

        quickCategory.textContent = category;

        quickPrice.textContent =
        "₹" + Number(price).toLocaleString();


        /*======================================
                QUICK VIEW ADD TO CART
        ======================================*/

        if(quickCartButton){

            quickCartButton.dataset.productId =
            productId || "";

        }


        modal.classList.add("active");

    });

});


/*======================================
            CLOSE MODAL
======================================*/

closeModal.addEventListener("click", function(){

    modal.classList.remove("active");

});


modal.addEventListener("click", function(e){

    if(e.target === modal){

        modal.classList.remove("active");

    }

});


document.addEventListener("keydown", function(e){

    if(e.key === "Escape"){

        modal.classList.remove("active");

    }

});


/*======================================
        QUICK VIEW ADD TO CART
======================================*/

if(quickCartButton){

    quickCartButton.addEventListener("click", function(){

        const productId =
        this.dataset.productId;

        if(!productId){

            alert("Product ID not found.");

            return;

        }


        const form =
        document.createElement("form");

        form.method = "POST";

        form.action = "add-to-cart.php";


        const input =
        document.createElement("input");

        input.type = "hidden";

        input.name = "product_id";

        input.value = productId;


        form.appendChild(input);

        document.body.appendChild(form);

        form.submit();

    });

}

/*======================================
        APPLY FILTERS
======================================*/

const applyFilter = document.querySelector(".apply-filter");

applyFilter.addEventListener("click", function(){

    const selectedPrice =
    document.querySelector(".price-filter:checked");

    const selectedCategories =
    document.querySelectorAll(".category-filter:checked");

    productCards.forEach(function(card){

        let showCard = true;

       /*============ PRICE ============*/

if(selectedPrice){

    const price = Number(card.dataset.price);

    const value = selectedPrice.value;


    if(value === "0-10000"){

        showCard = price <= 10000;

    }

    else if(value === "10000-25000"){

        showCard = price >= 10000 && price <= 25000;

    }

    else if(value === "25000-50000"){

        showCard = price >= 25000 && price <= 50000;

    }

    else if(value === "50000-100000"){

        showCard = price >= 50000 && price <= 100000;

    }

    else if(value === "100000+"){

        showCard = price > 100000;

    }

}

        /*============ CATEGORY ============*/

        if(showCard && selectedCategories.length>0){

            const gender =
            card.dataset.gender.toLowerCase();

            let matched = false;

            selectedCategories.forEach(function(item){

                if(item.value === gender){

                    matched = true;

                }

            });

            showCard = matched;

        }

        /*============ METAL ============*/

const selectedMetals =
document.querySelectorAll(".metal-filter:checked");

if(showCard && selectedMetals.length > 0){

    const metal =
   (card.dataset.metal || "").trim().toLowerCase();

    let metalMatched = false;

    selectedMetals.forEach(function(item){

        if(item.value.trim().toLowerCase() === metal){

            metalMatched = true;

        }

    });

    showCard = metalMatched;

}
       
        /*============ JEWELLERY TYPE ============*/

const selectedTypes =
document.querySelectorAll(".type-filter:checked");

if(showCard && selectedTypes.length > 0){

    const jewelleryType =
    (card.dataset.category || "").trim().toLowerCase();

    let typeMatched = false;

    selectedTypes.forEach(function(item){

        if(item.value.trim().toLowerCase() === jewelleryType){

            typeMatched = true;

        }

    });

    showCard = typeMatched;

}

        card.style.display = showCard ? "block" : "none";
    });
        const noProductsMessage =
document.querySelector(".no-products-message");

let visibleProducts = 0;

productCards.forEach(function(card){

    if(card.style.display !== "none"){
        visibleProducts++;
    }

});

if(noProductsMessage){

    noProductsMessage.style.display =
    visibleProducts === 0 ? "block" : "none";

}

    });

/*======================================
        CLEAR FILTERS
======================================*/

const clearFilter =
document.querySelector(".clear-filter");

clearFilter.addEventListener("click", function(){

    document.querySelectorAll(".price-filter")
    .forEach(function(item){

        item.checked = false;

    });

    document.querySelectorAll(".category-filter")
    .forEach(function(item){

        item.checked = false;

    });

    document.querySelectorAll(".metal-filter")
.forEach(function(item){

    item.checked = false;

});

document.querySelectorAll(".type-filter")
.forEach(function(item){

    item.checked = false;

});

    productCards.forEach(function(card){

        card.style.display = "block";

    });

    const noProductsMessage =
document.querySelector(".no-products-message");

if(noProductsMessage){

    noProductsMessage.style.display = "none";

}

});
 /*======================================
        ADD TO CART
======================================*/

const cartButtons =
document.querySelectorAll(".cart-btn");

cartButtons.forEach(function(button){

    button.addEventListener("click", function(){

        const productId = this.dataset.productId;

        if(!productId){
            alert("Product ID not found.");
            return;
        }

        const form = document.createElement("form");

        form.method = "POST";
        form.action = "add-to-cart.php";

        const input = document.createElement("input");

        input.type = "hidden";
        input.name = "product_id";
        input.value = productId;

        form.appendChild(input);

        document.body.appendChild(form);

        form.submit();

    });

});



/*======================================
        TOAST MESSAGE
======================================*/

function showToast(message){

    const toast =
    document.createElement("div");

    toast.className="toast-message";

    toast.innerHTML=message;

    document.body.appendChild(toast);

    setTimeout(function(){

        toast.classList.add("show");

    },100);

    setTimeout(function(){

        toast.classList.remove("show");

        setTimeout(function(){

            toast.remove();

        },300);

    },2200);

}
/*======================================
        LOCAL STORAGE
======================================*/

let cartCount =
Number(localStorage.getItem("cartCount")) || 0;

const cartCounter =
document.getElementById("cartCount");

cartCounter.textContent = cartCount;

/*======================================
        CART COUNTER
======================================*/

cartButtons.forEach(function(button){

    button.addEventListener("click", function(){

        cartCount++;

        cartCounter.textContent=cartCount;

        localStorage.setItem(
        "cartCount",
        cartCount);

    });

});

});


