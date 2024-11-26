document.addEventListener("DOMContentLoaded", function () {
  const hamburgerMenu = document.getElementById("hamburger-menu");
  const mobileMenu = document.getElementById("mobile-menu");

  if (hamburgerMenu && mobileMenu) {
    hamburgerMenu.addEventListener("click", function () {
      // Toggle la classe hidden du menu mobile
      mobileMenu.classList.toggle("hidden");

      // Animation optionnelle (si vous voulez ajouter une transition)
      mobileMenu.classList.toggle("show-menu");
    });

    // Ferme le menu si on clique en dehors
    document.addEventListener("click", function (event) {
      const isClickInsideMenu = mobileMenu.contains(event.target);
      const isClickOnHamburger = hamburgerMenu.contains(event.target);

      if (
        !isClickInsideMenu &&
        !isClickOnHamburger &&
        !mobileMenu.classList.contains("hidden")
      ) {
        mobileMenu.classList.add("hidden");
      }
    });
  }
});
