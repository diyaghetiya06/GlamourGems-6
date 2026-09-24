/*=========================================
        GLAMOUR GEMS - COLLECTIONS PAGE
==========================================*/


/*==============================
    COLLECTION BUTTON EFFECT
==============================*/

const collectionButtons = document.querySelectorAll(".collection-btn");

collectionButtons.forEach(button => {

    button.addEventListener("click", function(e){

        e.preventDefault();

        alert("Collection Page Coming Soon!");

    });

});


/*==============================
      CARD HOVER EFFECT
==============================*/

const collectionCards = document.querySelectorAll(".collection-card");

collectionCards.forEach(card => {

    card.addEventListener("mouseenter", () => {

        card.style.transition = ".4s ease";

    });

});


/*==============================
      IMAGE LOADING EFFECT
==============================*/

window.addEventListener("load", () => {

    const images = document.querySelectorAll(".collection-card img");

    images.forEach(img => {

        img.style.opacity = "0";

        setTimeout(() => {

            img.style.transition = "opacity .8s ease";

            img.style.opacity = "1";

        },200);

    });

});


/*==============================
      NEWSLETTER FORM
==============================*/

const newsletterForm = document.querySelector(".collection-newsletter-form");

if(newsletterForm){

newsletterForm.addEventListener("submit", function(e){

    e.preventDefault();

    const email = this.querySelector("input").value;

    if(email === ""){

        alert("Please enter your email.");

        return;

    }

    alert("Thank you for subscribing!");

    this.reset();

});

}


/*==============================
      PAGE LOADED
==============================*/

window.addEventListener("load", () => {

    console.log("Collections Page Loaded Successfully");

});

/*======================================
        COLLECTION SEARCH
======================================*/

const navIcons = document.querySelectorAll(".nav-icons a");

const searchIcon = navIcons[0];

const collectionSections =
    document.querySelectorAll(".collection-section");

const noResultMessage =
    document.querySelector(".collection-no-result");


if (searchIcon) {

    searchIcon.addEventListener("click", function(e) {

        e.preventDefault();

        let searchBox =
            document.querySelector(".collection-search-box");


        /*======================================
                CLOSE SEARCH
        ======================================*/

        if (searchBox) {

            searchBox.remove();

            collectionSections.forEach(function(section) {

                section.style.display = "";

                const cards =
                    section.querySelectorAll(".collection-product");

                cards.forEach(function(card) {
                    card.style.display = "";
                });

            });

            if (noResultMessage) {
                noResultMessage.style.display = "none";
            }

            return;
        }


        /*======================================
                CREATE SEARCH BOX
        ======================================*/

        searchBox = document.createElement("div");

        searchBox.className =
            "collection-search-box";

        searchBox.innerHTML = `

            <div class="collection-search-inner">

                <input
                    type="text"
                    id="collectionSearch"
                    placeholder="Search jewellery..."
                    autocomplete="off"
                >

                <button
                    type="button"
                    id="closeCollectionSearch"
                >
                    <i class="fa-solid fa-xmark"></i>
                </button>

            </div>

        `;


        /*======================================
            SEARCH BELOW BREADCRUMB
        ======================================*/

        const breadcrumb =
            document.querySelector(".collection-breadcrumb");

        if (breadcrumb) {

            breadcrumb.insertAdjacentElement(
                "afterend",
                searchBox
            );

        }


        const input =
            document.getElementById("collectionSearch");

        input.focus();


        /*======================================
            SEARCH PRODUCTS
        ======================================*/

        input.addEventListener("input", function() {

            const searchValue =
                this.value.toLowerCase().trim();

            let totalMatches = 0;


            if (noResultMessage) {
                noResultMessage.style.display = "none";
            }


            collectionSections.forEach(function(section) {

                const cards =
                    section.querySelectorAll(
                        ".collection-product"
                    );

                let matchingCards = 0;


                cards.forEach(function(card) {

                    const productName =
                        card.querySelector("h3")
                        .textContent
                        .toLowerCase();


                    if (
                        searchValue === "" ||
                        productName.includes(searchValue)
                    ) {

                        card.style.display = "";

                        if (searchValue !== "") {
                            matchingCards++;
                            totalMatches++;
                        }

                    } else {

                        card.style.display = "none";

                    }

                });


                /*==================================
                    SHOW / HIDE COLLECTION
                ==================================*/

                if (
                    searchValue === "" ||
                    matchingCards > 0
                ) {

                    section.style.display = "";

                } else {

                    section.style.display = "none";

                }

            });


            /*==================================
                NO JEWELLERY FOUND
            ==================================*/

            if (
                searchValue !== "" &&
                totalMatches === 0
            ) {

                if (noResultMessage) {
                    noResultMessage.style.display = "block";
                }

            }

        });


        /*======================================
                CLOSE SEARCH BUTTON
        ======================================*/

        document
        .getElementById("closeCollectionSearch")
        .addEventListener("click", function() {

            collectionSections.forEach(function(section) {

                section.style.display = "";

                const cards =
                    section.querySelectorAll(
                        ".collection-product"
                    );

                cards.forEach(function(card) {
                    card.style.display = "";
                });

            });


            if (noResultMessage) {
                noResultMessage.style.display = "none";
            }


            searchBox.remove();

        });

    });

}

/*======================================
        MATERIAL COLLECTION FILTER
======================================*/

window.addEventListener("load", function() {

    const hash = window.location.hash;

    if (!hash) {
        return;
    }

    const targetSection = document.querySelector(hash);

    if (!targetSection || !targetSection.classList.contains("collection-section")) {
        return;
    }

    collectionSections.forEach(function(section) {

        if (section === targetSection) {

            section.style.display = "";

        } else {

            section.style.display = "none";

        }

    });

    /* Scroll to selected collection */

    setTimeout(function() {

        targetSection.scrollIntoView({
            behavior: "smooth",
            block: "start"
        });

    }, 100);

});