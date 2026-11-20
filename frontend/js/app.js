/*
 * Small UI behaviour for the static Milestone 1 frontend.
 * No backend calls yet. AJAX calls to the REST API are added in Milestone 4,
 * and moved into separate service files in Milestone 5.
 */
document.addEventListener('view:loaded', function (event) {
  const page = event.detail.name;

  // Product page: clicking a size button marks it as selected
  if (page === 'product') {
    document.querySelectorAll('.tc-size').forEach(function (btn) {
      btn.addEventListener('click', function () {
        document.querySelectorAll('.tc-size').forEach(function (b) { b.classList.remove('active'); });
        btn.classList.add('active');
      });
    });
  }

  // Static forms: stop the browser from reloading the page on submit
  document.querySelectorAll('#app form').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      e.preventDefault();
    });
  });
});
