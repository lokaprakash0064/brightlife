/**
 * brightlife-nav.js
 * Custom navigation behavior for Brightlife Matrimony v2.
 *
 * DESIGN RULES:
 * - Phase 1: Bootstrap's data-toggle="collapse" handles the mobile navbar.
 *   This file is dormant in Phase 1 — no elements with .bl-mobile-menu-btn
 *   or .bl-nav-links exist yet in the templates.
 * - Phase 2+: When the navbar HTML is updated to the new structure,
 *   this script activates automatically (guarded by element existence checks).
 * - jQuery is NOT used here — vanilla JS only, no conflict with existing
 *   jQuery-dependent code (AJAX, DataTables, Bootstrap modals).
 * - This file must be loaded AFTER Bootstrap JS (which is after jQuery).
 */

(function () {
    'use strict';

    /* ------------------------------------------------------------------
       Phase 2+ Custom Mobile Menu Toggle
       Activates only when .bl-mobile-menu-btn and .bl-nav-links exist.
       Currently dormant — Bootstrap toggle handles Phase 1 mobile menu.
    ------------------------------------------------------------------ */

    var mobileMenuBtn = document.querySelector('.bl-mobile-menu-btn');
    var navLinks      = document.querySelector('.bl-nav-links');

    if (mobileMenuBtn && navLinks) {

        // Toggle menu open/close
        mobileMenuBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            var isOpen = navLinks.classList.toggle('active');
            mobileMenuBtn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');

            // Swap icon between bars and times
            var icon = mobileMenuBtn.querySelector('i');
            if (icon) {
                if (isOpen) {
                    icon.classList.replace('fa-bars', 'fa-times');
                } else {
                    icon.classList.replace('fa-times', 'fa-bars');
                }
            }
        });

        // Close menu when any nav link is clicked
        navLinks.querySelectorAll('a').forEach(function (link) {
            link.addEventListener('click', function () {
                navLinks.classList.remove('active');
                mobileMenuBtn.setAttribute('aria-expanded', 'false');
                var icon = mobileMenuBtn.querySelector('i');
                if (icon) {
                    icon.classList.replace('fa-times', 'fa-bars');
                }
            });
        });

        // Close menu when clicking outside the nav area
        document.addEventListener('click', function (e) {
            if (
                navLinks.classList.contains('active') &&
                !mobileMenuBtn.contains(e.target) &&
                !navLinks.contains(e.target)
            ) {
                navLinks.classList.remove('active');
                mobileMenuBtn.setAttribute('aria-expanded', 'false');
                var icon = mobileMenuBtn.querySelector('i');
                if (icon) {
                    icon.classList.replace('fa-times', 'fa-bars');
                }
            }
        });

        // Close menu on Escape key
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && navLinks.classList.contains('active')) {
                navLinks.classList.remove('active');
                mobileMenuBtn.setAttribute('aria-expanded', 'false');
                mobileMenuBtn.focus();
                var icon = mobileMenuBtn.querySelector('i');
                if (icon) {
                    icon.classList.replace('fa-times', 'fa-bars');
                }
            }
        });
    }


    /* ------------------------------------------------------------------
       Sticky Navbar Shadow Enhancement
       Adds a subtle shadow increase on scroll for any sticky `.navbar`.
       Works with existing Bootstrap navbar — purely visual, no JS conflict.
    ------------------------------------------------------------------ */

    var navbar = document.querySelector('.navbar.bg-pink');

    if (navbar) {
        var onScroll = function () {
            if (window.scrollY > 10) {
                navbar.style.boxShadow = '0 4px 20px rgba(0, 0, 0, 0.12)';
            } else {
                navbar.style.boxShadow = '0 4px 15px rgba(0, 0, 0, 0.08)';
            }
        };

        // Use passive listener — no layout impact, better scroll performance
        window.addEventListener('scroll', onScroll, { passive: true });
    }

})();
