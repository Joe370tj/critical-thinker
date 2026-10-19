document.addEventListener('DOMContentLoaded', function () {
    // Mobile nav toggle
    var navToggle = document.getElementById('navToggle');
    var navLinks = document.getElementById('navLinks');
    if (navToggle && navLinks) {
        navToggle.addEventListener('click', function () {
            navLinks.classList.toggle('open');
        });
    }

    // Dropdown menus
    document.querySelectorAll('.dropdown').forEach(function (dd) {
        var btn = dd.querySelector('.dropdown-toggle');
        if (!btn) return;
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            // close others
            document.querySelectorAll('.dropdown.open').forEach(function (other) {
                if (other !== dd) other.classList.remove('open');
            });
            dd.classList.toggle('open');
        });
    });
    document.addEventListener('click', function () {
        document.querySelectorAll('.dropdown.open').forEach(function (dd) {
            dd.classList.remove('open');
        });
    });

    // Form required validation
    document.querySelectorAll('form').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            var valid = true;
            form.querySelectorAll('input[required], textarea[required], select[required]').forEach(function (input) {
                if (!String(input.value || '').trim()) {
                    valid = false;
                    input.style.borderColor = '#dc3545';
                } else {
                    input.style.borderColor = '';
                }
            });
            if (!valid) e.preventDefault();
        });
    });

    // Range score displays
    document.querySelectorAll('.score-input input[type=range]').forEach(function (range) {
        var label = range.parentElement.querySelector('.score-val');
        if (label) {
            label.textContent = range.value;
            range.addEventListener('input', function () {
                label.textContent = range.value;
            });
        }
    });
});
