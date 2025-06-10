//  SHOW MENU

let line = document.querySelector('.navbar .ri-menu-line');
let menu = document.querySelector('.navbar ul');

line.addEventListener('click',()=>{
    menu.classList.toggle('showmenu');
});

window.addEventListener('scroll',() =>{
    let nav = document.querySelector('.navbar');
    if(scrollY >50){
        nav.classList.add('navbarSticky')
    }
    else{
        nav.classList.remove('navbarSticky')
    };
});


// BUTTON
  document.getElementById('myButton').addEventListener('click', function () {
    window.location.href = 'https://youtu.be/FPALstZU7fI?si=i_tNPEYZpE1yItQh';
  });
  document.getElementById('myButtonn').addEventListener('click', function () {
    window.location.href = '../pages/contact.php';
  });
  document.getElementById('myButtonnn').addEventListener('click', function () {
    window.location.href = 'halajakan.php';
  });
document.getElementById('myButonnnn').addEventListener('click', function () {
  window.location.href = 'portofolios.php';
  });


// Memilih elemen bintang rating
const starsProduk = document.querySelectorAll('#product-rating .star');
const starsPenjual = document.querySelectorAll('#seller-service-rating .star');
const starsJasaKirim = document.querySelectorAll('#delivery-speed-rating .star');

// Fungsi untuk mengatur bintang yang dipilih
function handleStarRating(stars) {
    stars.forEach((star, index) => {
        star.addEventListener('click', () => {
            // Reset semua bintang
            stars.forEach(s => s.classList.remove('selected'));
            // Tambah class "selected" pada bintang yang dipilih dan sebelumnya
            for (let i = 0; i <= index; i++) {
                stars[i].classList.add('selected');
            }
        });
    });
}





document.addEventListener("DOMContentLoaded", function () {
  // Function to handle star rating
  function handleStarRating(starContainerId) {
    const starsContainer = document.getElementById(starContainerId);
    const stars = starsContainer.querySelectorAll(".star");

    stars.forEach((star) => {
      // Event listener for hover effect
      star.addEventListener("mouseover", () => {
        const value = parseInt(star.getAttribute("data-value"));
        highlightStars(stars, value);
      });

      // Event listener for resetting hover effect
      starsContainer.addEventListener("mouseleave", () => {
        const selectedValue = starsContainer.getAttribute("data-selected-value");
        highlightStars(stars, parseInt(selectedValue));
      });

      // Event listener for clicking a star
      star.addEventListener("click", () => {
        const value = parseInt(star.getAttribute("data-value"));
        starsContainer.setAttribute("data-selected-value", value);
        highlightStars(stars, value);
      });
    });
  }

  // Function to highlight stars based on a given value
  function highlightStars(stars, value) {
    stars.forEach((star) => {
      const starValue = parseInt(star.getAttribute("data-value"));
      if (starValue <= value) {
        star.classList.add("selected");
      } else {
        star.classList.remove("selected");
      }
    });
  }

  // Initialize star rating for all rating sections
  handleStarRating("product-rating");
  handleStarRating("seller-service-rating");
  handleStarRating("delivery-speed-rating");
});


document.addEventListener("DOMContentLoaded", () => {
  console.log("Halaman Pusat Bantuan telah dimuat.");
});




//SHOW HIDE PW
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const toggleIcon = document.querySelector('.toggle-password ion-icon');

    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.setAttribute('name', 'eye');
    } else {
        passwordInput.type = 'password';
        toggleIcon.setAttribute('name', 'eye-off');
    }
}


// profile
function toggleDropdown() {
  var dropdown = document.getElementById("profileDropdown");
  dropdown.style.display = dropdown.style.display === "block" ? "none" : "block";
}

