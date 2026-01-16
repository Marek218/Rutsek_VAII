// public/js/theme.js
// - Theme toggle (light/dark) persisted in localStorage

(function () {
    'use strict';

    var STORAGE_KEY = 'lux.theme';
    var themeInitDone = false;

    function getPreferredTheme() {
        var saved;
        try {
            saved = window.localStorage ? localStorage.getItem(STORAGE_KEY) : null;
        } catch (e) {
            saved = null;
        }
        if (saved === 'light' || saved === 'dark') return saved;
        var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
        return prefersDark ? 'dark' : 'light';
    }

    function applyThemeToDom(theme) {
        var root = document.documentElement;
        if (theme === 'dark') {
            root.setAttribute('data-theme', 'dark');
        } else {
            root.removeAttribute('data-theme');
        }

        var labels = document.querySelectorAll('[data-theme-label]');
        for (var i = 0; i < labels.length; i++) {
            labels[i].textContent = theme === 'dark' ? 'Tmavý' : 'Svetlý';
        }
    }

    function setTheme(theme) {
        applyThemeToDom(theme);
        try {
            localStorage.setItem(STORAGE_KEY, theme);
        } catch (e) {
            // ignore
        }
    }

    function toggleTheme() {
        var current = document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
        var next = current === 'dark' ? 'light' : 'dark';
        setTheme(next);
    }

    function findClosest(el, selector) {
        if (el && el.nodeType === 3) el = el.parentElement;
        if (el && typeof el.closest === 'function') return el.closest(selector);
        while (el && el.nodeType === 1) {
            if (el.matches && el.matches(selector)) return el;
            el = el.parentElement;
        }
        return null;
    }

    function initThemeToggle() {
        if (themeInitDone) return;
        themeInitDone = true;

        setTheme(getPreferredTheme());

        document.addEventListener('click', function (e) {
            var btn = findClosest(e.target, '[data-theme-toggle]');
            if (!btn) return;
            e.preventDefault();
            toggleTheme();
        }, false);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initThemeToggle);
    } else {
        initThemeToggle();
    }
})();
