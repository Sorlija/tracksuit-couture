/*
 * Application bootstrap.
 *
 * spapp (js/lib/jquery.spapp.js) turns every <section> inside <main id="spapp"> into a page.
 * The URL hash decides which page is shown (#shop, #cart, ...). The HTML of a page is loaded
 * from views/ the first time it is opened, so navigation happens WITHOUT reloading the page.
 *
 * No backend calls yet. AJAX calls to the REST API are added in Milestone 4,
 * and moved into separate service files in Milestone 5.
 */
$(document).ready(function () {
  const DEFAULT_VIEW = "home";

  // Browser tab title for each view (section id -> title)
  const TITLES = {
    home: "Home", shop: "Shop", product: "Product", cart: "Cart", checkout: "Checkout",
    login: "Log in", register: "Register", profile: "Profile", orders: "My Orders",
    admin: "Admin", error_404: "Page not found"
  };

  const app = $.spapp({
    templateDir: "./views/",
    defaultView: DEFAULT_VIEW,
    pageNotFound: "error_404"
  });

  // Runs every time the URL hash changes (and once on first load)
  function onViewChange() {
    const view = location.hash.slice(1) || DEFAULT_VIEW;

    // 1. highlight the active link in the navbar
    $(".navbar .nav-link")
      .removeClass("active")
      .filter('[data-page="' + view + '"]')
      .addClass("active");

    // 2. page title in the browser tab
    document.title = (TITLES[view] || "Tracksuit Couture") + " | Tracksuit Couture";

    // 3. close the mobile menu after a link was clicked
    const menu = document.getElementById("mainNav");
    if (menu.classList.contains("show")) {
      bootstrap.Collapse.getOrCreateInstance(menu).hide();
    }

    // 4. sections are anchors, so the browser would jump down to them; start at the top instead
    window.scrollTo(0, 0);
  }

  window.addEventListener("hashchange", onViewChange);
  onViewChange();

  // ---------- Static page behaviour ----------
  // Event delegation ($(document).on): the views are loaded later by spapp,
  // so the handlers are attached to the document and work for elements added afterwards.

  // Product page: clicking a size button marks it as selected
  $(document).on("click", ".tc-size", function () {
    $(".tc-size").removeClass("active");
    $(this).addClass("active");
  });

  // Static forms: stop the browser from reloading the page on submit
  $(document).on("submit", "#spapp form", function (e) {
    e.preventDefault();
  });

  app.run();
});
