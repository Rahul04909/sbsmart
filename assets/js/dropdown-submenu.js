(function () {
  // Polyfills for older browsers
  if (typeof Element !== 'undefined') {
    if (!Element.prototype.matches) {
      Element.prototype.matches = Element.prototype.msMatchesSelector || Element.prototype.webkitMatchesSelector || function (s) {
        var matches = (this.document || this.ownerDocument).querySelectorAll(s);
        var i = 0;
        while (matches[i] && matches[i] !== this) i++;
        return !!matches[i];
      };
    }

    if (!Element.prototype.closest) {
      Element.prototype.closest = function (s) {
        var el = this;
        while (el) {
          if (el.matches && el.matches(s)) return el;
          el = el.parentElement;
        }
        return null;
      };
    }
  }

  function forEachNode(list, cb) {
    return Array.prototype.forEach.call(list, cb);
  }

  // Toggle submenu on click
  document.addEventListener('click', function (e) {
    var toggle = e.target.closest('.dropdown-submenu > .dropdown-toggle, .dropdown-submenu > .dropdown-item.dropdown-toggle');
    if (!toggle) return;
    e.preventDefault();
    e.stopPropagation();

    var parent = toggle.parentElement;
    var submenu = parent.querySelector('.dropdown-menu');
    if (!submenu) return;

    var parentMenu = parent.closest('.dropdown-menu');
    if (parentMenu) {
      forEachNode(parentMenu.querySelectorAll('.dropdown-submenu'), function (s) {
        if (s !== parent) {
          s.classList.remove('show');
          var inn = s.querySelector('.dropdown-menu');
          if (inn) inn.classList.remove('show');
        }
      });
    }

    var isShown = submenu.classList.contains('show');
    if (isShown) {
      parent.classList.remove('show');
      submenu.classList.remove('show');
    } else {
      parent.classList.add('show');
      submenu.classList.add('show');
    }
  });

  // Close any open submenus when the parent dropdown hides
  document.addEventListener('DOMContentLoaded', function () {
    forEachNode(document.querySelectorAll('.dropdown'), function (drop) {
      drop.addEventListener('hide.bs.dropdown', function () {
        var menu = drop.querySelectorAll('.dropdown-submenu');
        forEachNode(menu, function (sm) {
          sm.classList.remove('show');
          var inner = sm.querySelector('.dropdown-menu');
          if (inner) inner.classList.remove('show');
        });
      });
    });
  });
})();
