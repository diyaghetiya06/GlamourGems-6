/*======================================
            HERO SLIDER
======================================*/

const heroSlider = new Swiper(".heroSlider", {

    loop: true,

    effect: "fade",

    speed: 1000,

    autoplay: {
        delay: 4000,
        disableOnInteraction: false,
        pauseOnMouseEnter: false
    },

    navigation: {
        nextEl: ".hero-next",
        prevEl: ".hero-prev"
    },

    pagination: {
        el: ".hero-pagination",
        clickable: true
    }

});
/*=====================================
    FEATURED COLLECTION
======================================*/

const featuredSlider = new Swiper(".featuredSlider", {

    effect: "coverflow",

    grabCursor: true,

    centeredSlides: true,

    loop: true,

    slidesPerView: "auto",

    speed: 1300,

    spaceBetween: 0,

    autoplay: {

        delay: 1800,

        disableOnInteraction: false,

        pauseOnMouseEnter: false,

        waitForTransition: false,

    },

    coverflowEffect: {

        rotate: 0,

        stretch: 0,

        depth: 250,

        modifier: 1.6,

        slideShadows: false,

        scale: 0.88,

    },

    navigation: {

        nextEl: ".feature-next",

        prevEl: ".feature-prev",

    },

    pagination: {

        el: ".feature-pagination",

        clickable: true,

    },

    breakpoints: {

        320: {

            slidesPerView: 1.2,

        },

        768: {

            slidesPerView: 2.2,

        },

        1024: {

            slidesPerView: "auto",

        }

    }

});
/*=====================================
        CUSTOMER REVIEWS
=====================================*/

const reviewSlider = new Swiper(".reviewSlider", {

    loop: true,

    speed: 800,

    spaceBetween: 30,

    grabCursor: true,

    autoplay: {

        delay: 3500,

        disableOnInteraction: false,

        pauseOnMouseEnter: true,

    },

    navigation: {

        nextEl: ".review-next",

        prevEl: ".review-prev",

    },

    pagination: {

        el: ".review-pagination",

        clickable: true,

    },

    breakpoints: {

        0: {

            slidesPerView: 1,

        },

        768: {

            slidesPerView: 2,

        },

        1200: {

            slidesPerView: 3,

        }

    }

});

/*=====================================
      INSTAGRAM REELS
=====================================*/

const reelCards = document.querySelectorAll(".reel-card");

reelCards.forEach((card) => {

    const video = card.querySelector("video");
    const playBtn = card.querySelector(".play-btn");

    // Initial Settings
    video.muted = true;
    video.loop = true;
    video.playsInline = true;

    // Desktop Hover
    card.addEventListener("mouseenter", () => {

        video.play();

        playBtn.style.opacity = "0";

        playBtn.style.transform = "scale(.7)";

    });

    card.addEventListener("mouseleave", () => {

        video.pause();

        video.currentTime = 0;

        playBtn.style.opacity = "1";

        playBtn.style.transform = "scale(1)";

    });

    // Mobile Click
    card.addEventListener("click", () => {

        if(video.paused){

            video.play();

            playBtn.style.opacity="0";

        }

        else{

            video.pause();

            playBtn.style.opacity="1";

        }

    });

});

/*=====================================
      EXPERIENCE COUNTER
=====================================*/

const counters = document.querySelectorAll(".counter");

const counterObserver = new IntersectionObserver((entries) => {

    entries.forEach(entry => {

        if (entry.isIntersecting) {

            const counter = entry.target;

            // Get database value
            const originalTarget = counter.dataset.target;

            // Remove + and commas for calculation
            const target = parseInt(
                originalTarget.replace(/[^0-9]/g, ""),
                10
            );

            let count = 0;

            const speed = Math.max(target / 100, 1);

            const updateCounter = () => {

                if (count < target) {

                    count += speed;

                    counter.innerText =
                        Math.ceil(count).toLocaleString("en-IN") + "+";

                    requestAnimationFrame(updateCounter);

                } else {

                    counter.innerText =
                        target.toLocaleString("en-IN") + "+";

                }

            };

            updateCounter();

            counterObserver.unobserve(counter);

        }

    });

}, {
    threshold: 0.5
});


counters.forEach(counter => {
    counterObserver.observe(counter);
});

/*======================================
        NEW ARRIVALS WISHLIST
======================================*/

const arrivalHearts =
document.querySelectorAll(".arrival-heart");

arrivalHearts.forEach(function(button) {

    button.addEventListener("click", function(e) {

        e.preventDefault();

        const newArrivalId =
            this.dataset.newArrivalId;

        const icon =
            this.querySelector("i");

        if (!newArrivalId) {

            console.error("New Arrival ID not found.");

            return;

        }


        /*======================================
                SEND TO DATABASE
        ======================================*/

        const formData =
            new FormData();

        formData.append(
            "new_arrival_id",
            newArrivalId
        );


        fetch(
            "add-new-arrival-wishlist.php",
            {
                method: "POST",
                body: formData
            }
        )

        .then(function(response) {

            return response.json();

        })

        .then(function(data) {

            /*======================================
                    LOGIN REQUIRED
            ======================================*/

            if (data.status === "login") {

                window.location.href =
                    "login.php";

                return;

            }


            /*======================================
                    SUCCESS
            ======================================*/

            if (data.status === "success") {


                /*======================================
                        ADD TO WISHLIST
                ======================================*/

                if (data.action === "added") {

                    icon.classList.remove(
                        "fa-regular"
                    );

                    icon.classList.add(
                        "fa-solid"
                    );

                    icon.style.color =
                        "#e63946";

                    button.classList.add(
                        "active"
                    );

                }


                /*======================================
                        REMOVE FROM WISHLIST
                ======================================*/

                if (data.action === "removed") {

                    icon.classList.remove(
                        "fa-solid"
                    );

                    icon.classList.add(
                        "fa-regular"
                    );

                    icon.style.color = "";

                    button.classList.remove(
                        "active"
                    );

                }


                /*======================================
                        UPDATE NAVBAR COUNT
                ======================================*/

                const wishlistCounter =
                    document.getElementById(
                        "wishlistCount"
                    );

                if (wishlistCounter) {

                    wishlistCounter.textContent =
                        data.count;

                }

            }

        })

        .catch(function(error) {

            console.error(
                "New Arrival Wishlist Error:",
                error
            );

        });

    });

});

/*======================================
            HOME SEARCH
======================================*/

document.addEventListener("click", function(e){

    const searchBtn =
    e.target.closest("#homeSearchBtn");

    if(!searchBtn){
        return;
    }

    e.preventDefault();


    /*======================================
            FIND NEW ARRIVALS
    ======================================*/

    const arrivalGrid =
    document.querySelector(".arrival-grid");

    if(!arrivalGrid){

        alert("New Arrivals section not found!");

        return;
    }


    /*======================================
            CREATE SEARCH BOX
    ======================================*/

    let searchBox =
    document.getElementById("homeSearchBox");


    if(!searchBox){

        searchBox =
        document.createElement("div");

        searchBox.id =
        "homeSearchBox";


        searchBox.style.width =
        "100%";

        searchBox.style.margin =
        "0 auto 30px";

        searchBox.style.display =
        "block";


        searchBox.innerHTML = `

            <div style="
                display:flex;
                align-items:center;
                gap:10px;
                max-width:700px;
                margin:0 auto;
            ">

                <input
                    type="text"
                    id="homeSearchInput"
                    placeholder="Search New Arrivals..."
                    autocomplete="off"
                    style="
                        flex:1;
                        padding:15px 20px;
                        border:1px solid #ddd;
                        border-radius:30px;
                        outline:none;
                        font-size:15px;
                    "
                >

                <button
                    type="button"
                    id="closeHomeSearch"
                    style="
                        width:45px;
                        height:45px;
                        border:none;
                        border-radius:50%;
                        cursor:pointer;
                        background:#234B43;
                        color:#fff;
                        font-size:18px;
                    "
                >
                    <i class="fa-solid fa-xmark"></i>
                </button>

            </div>

            <div
                id="homeSearchNotFound"
                style="
                    display:none;
                    text-align:center;
                    margin-top:15px;
                    font-size:16px;
                    color:#777;
                "
            >
                No New Arrivals Found
            </div>

        `;


        arrivalGrid.parentNode.insertBefore(
            searchBox,
            arrivalGrid
        );


        /*======================================
                SEARCH INPUT
        ======================================*/

        const input =
        document.getElementById(
            "homeSearchInput"
        );


        const notFound =
        document.getElementById(
            "homeSearchNotFound"
        );


        input.addEventListener(
            "input",
            function(){

                const value =
                this.value
                .trim()
                .toLowerCase();


                const cards =
                document.querySelectorAll(
                    ".arrival-card"
                );


                let found = 0;


                cards.forEach(function(card){

                    const title =
                    card.querySelector("h3");


                    if(!title){
                        return;
                    }


                    const name =
                    title.textContent
                    .trim()
                    .toLowerCase();


                    if(
                        value === "" ||
                        name.includes(value)
                    ){

                        card.style.display = "";
                        found++;

                    }else{

                        card.style.display = "none";

                    }

                });


                if(
                    value !== "" &&
                    found === 0
                ){

                    notFound.style.display =
                    "block";

                }else{

                    notFound.style.display =
                    "none";

                }

            }
        );


        /*======================================
                CLOSE SEARCH
        ======================================*/

        document
        .getElementById("closeHomeSearch")
        .addEventListener(
            "click",
            function(){

                input.value = "";

                notFound.style.display =
                "none";


                document
                .querySelectorAll(".arrival-card")
                .forEach(function(card){

                    card.style.display = "";

                });


                searchBox.style.display =
                "none";

            }
        );

    }


    /*======================================
            SHOW SEARCH BOX
    ======================================*/

    searchBox.style.display =
    "block";


    searchBox.scrollIntoView({
        behavior:"smooth",
        block:"center"
    });


    setTimeout(function(){

        const input =
        document.getElementById(
            "homeSearchInput"
        );

        if(input){
            input.focus();
        }

    },300);

});