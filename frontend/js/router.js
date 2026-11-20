/*
 * Simple hash-based SPA router.
 *
 * The URL hash decides which view is shown:
 *   #shop        -> views/shop.html
 *   #product/3   -> views/product.html  (the "3" is a parameter, used from Milestone 4)
 *
 * When the hash changes, the matching HTML file is fetched and placed inside <main id="app">.
 * The page never reloads, only the content of #app changes.
 */
const Router = (function () {
  // route name -> view file + page title
  const routes = {
    home:     { view: 'views/home.html',     title: 'Home' },
    shop:     { view: 'views/shop.html',     title: 'Shop' },
    product:  { view: 'views/product.html',  title: 'Product' },
    cart:     { view: 'views/cart.html',     title: 'Cart' },
    checkout: { view: 'views/checkout.html', title: 'Checkout' },
    login:    { view: 'views/login.html',    title: 'Log in' },
    register: { view: 'views/register.html', title: 'Register' },
    profile:  { view: 'views/profile.html',  title: 'Profile' },
    orders:   { view: 'views/orders.html',   title: 'My Orders' },
    admin:    { view: 'views/admin.html',    title: 'Admin' }
  };
  const notFound = { view: 'views/404.html', title: 'Not found' };

  // Views that were already loaded once are kept here so they are not fetched again
  const cache = {};

  // "#product/3" -> { name: "product", params: ["3"] }
  function parseHash() {
    const hash = window.location.hash.replace('#', '') || 'home';
    const parts = hash.split('/');
    return { name: parts[0], params: parts.slice(1) };
  }

  async function getView(file) {
    if (cache[file]) return cache[file];
    const response = await fetch(file);
    if (!response.ok) throw new Error('View not found: ' + file);
    cache[file] = await response.text();
    return cache[file];
  }

  function setActiveLink(name) {
    document.querySelectorAll('.navbar .nav-link').forEach(function (link) {
      link.classList.toggle('active', link.dataset.page === name);
    });
  }

  function closeMobileMenu() {
    const menu = document.getElementById('mainNav');
    if (menu.classList.contains('show')) {
      bootstrap.Collapse.getOrCreateInstance(menu).hide();
    }
  }

  async function navigate() {
    const { name, params } = parseHash();
    const route = routes[name] || notFound;
    const app = document.getElementById('app');

    try {
      app.innerHTML = await getView(route.view);
    } catch (e) {
      app.innerHTML = await getView(notFound.view);
    }

    document.title = route.title + ' | Tracksuit Couture';
    setActiveLink(name);
    closeMobileMenu();
    window.scrollTo(0, 0);

    // Let other scripts react to the new view (e.g. app.js)
    document.dispatchEvent(new CustomEvent('view:loaded', { detail: { name: name, params: params } }));
  }

  window.addEventListener('hashchange', navigate);
  document.addEventListener('DOMContentLoaded', navigate);

  return { navigate: navigate };
})();
