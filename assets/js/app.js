(function () {
    var toggle = document.querySelector('.nav-toggle');
    var nav = document.querySelector('.site-nav');
    if (toggle && nav) {
        toggle.addEventListener('click', function () {
            nav.classList.toggle('is-open');
        });
    }

    document.querySelectorAll('[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!window.confirm(form.getAttribute('data-confirm'))) {
                event.preventDefault();
            }
        });
    });

    document.querySelectorAll('input[data-toggle-target]').forEach(function (input) {
        var target = document.querySelector(input.getAttribute('data-toggle-target'));
        if (!target) {
            return;
        }
        var sync = function () {
            target.style.display = input.checked ? 'none' : '';
        };
        input.addEventListener('change', sync);
        sync();
    });

    document.querySelectorAll('[data-copy]').forEach(function (button) {
        button.addEventListener('click', function () {
            var text = button.getAttribute('data-copy');
            if (navigator.clipboard) {
                navigator.clipboard.writeText(text);
                var old = button.textContent;
                button.textContent = 'Скопировано';
                setTimeout(function () { button.textContent = old; }, 1600);
            }
        });
    });
})();

// Подсказка для таблиц, которые не помещаются по ширине (телефоны)
document.querySelectorAll('.table-wrap').forEach(function (wrap) {
    if (wrap.scrollWidth > wrap.clientWidth + 4) {
        var hint = document.createElement('div');
        hint.className = 'table-hint';
        hint.textContent = 'Прокрутите таблицу вбок →';
        wrap.parentNode.insertBefore(hint, wrap.nextSibling);
    }
});

// Если хостинг не умеет красивые ссылки (mod_rewrite выключен),
// один раз проверяем /about и переключаемся на index.php?r=...
(function () {
    var base = document.documentElement.getAttribute('data-base') || '';
    var style = document.documentElement.getAttribute('data-style') || 'query';

    if (style !== 'pretty') {
        return;
    }
    if (location.pathname.indexOf('index.php') !== -1) {
        return;
    }
    try {
        if (sessionStorage.getItem('emvia_pretty_checked') === '1') {
            return;
        }
    } catch (e) {
        return;
    }

    function switchToQuery() {
        var path = location.pathname;
        if (base && path.indexOf(base) === 0) {
            path = path.slice(base.length);
        }
        if (!path) {
            path = '/';
        }
        var query = location.search ? '&' + location.search.replace(/^\?/, '') : '';
        location.replace(base + '/index.php?r=' + encodeURIComponent(path) + '&style=query' + query);
    }

    fetch(base + '/about', { headers: { 'X-Requested-With': 'fetch' } })
        .then(function (res) {
            try { sessionStorage.setItem('emvia_pretty_checked', '1'); } catch (e) {}
            if (res.status === 404) {
                switchToQuery();
            }
        })
        .catch(function () {});
})();
