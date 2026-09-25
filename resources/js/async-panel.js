export function responseStaysInCurrentPanel(responseUrl, currentUrl) {
    const response = new URL(responseUrl, currentUrl);
    const current = new URL(currentUrl);

    return response.origin === current.origin && response.pathname === current.pathname;
}

export function replaceMainContent(currentMain, nextMain) {
    const scrollTop = currentMain.scrollTop;
    currentMain.innerHTML = nextMain.innerHTML;
    currentMain.scrollTop = scrollTop;

    return currentMain;
}

export function replacePanelPage(currentMain, nextMain) {
    currentMain.innerHTML = nextMain.innerHTML;
    currentMain.scrollTop = 0;

    return currentMain;
}

let navigationController = null;

export function syncCollapsibleGroup(currentGroup, currentToggle, nextGroup, nextToggle) {
    const open = nextGroup.classList.contains('show');
    currentGroup.classList.toggle('show', open);
    currentToggle.classList.toggle('active-group', nextToggle.classList.contains('active-group'));
    currentToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
}

function syncSidebarState(nextDocument) {
    const currentLinks = document.querySelectorAll('#app-sidebar a.nav-link');
    const nextLinks = [...nextDocument.querySelectorAll('#app-sidebar a.nav-link')];

    currentLinks.forEach((link) => {
        const next = nextLinks.find((candidate) => candidate.href === link.href);
        link.classList.toggle('active', next?.classList.contains('active') ?? false);
        if (next?.classList.contains('active')) link.setAttribute('aria-current', 'page');
        else link.removeAttribute('aria-current');
    });

    document.querySelectorAll('#app-sidebar .sidebar-collapse').forEach((currentGroup) => {
        const nextGroup = nextDocument.getElementById(currentGroup.id);
        const currentToggle = currentGroup.previousElementSibling;
        const nextToggle = nextGroup?.previousElementSibling;

        if (nextGroup && currentToggle && nextToggle) {
            syncCollapsibleGroup(currentGroup, currentToggle, nextGroup, nextToggle);
        }
    });
}

async function navigatePanel(url, historyMode = 'push') {
    const destination = new URL(url, window.location.href);
    const main = document.querySelector('#app-main');
    if (!main || destination.origin !== window.location.origin) {
        window.location.assign(destination.href);
        return;
    }

    navigationController?.abort();
    const controller = new AbortController();
    navigationController = controller;
    main.setAttribute('aria-busy', 'true');
    try {
        const response = await fetch(destination.href, {
            credentials: 'same-origin',
            headers: { Accept: 'text/html', 'X-Panel-Navigation': 'partial' },
            signal: controller.signal,
        });
        const contentType = response.headers.get('content-type') || '';
        if (!response.ok || !contentType.includes('text/html')) throw new Error('No se pudo abrir esta sección.');

        const nextDocument = new DOMParser().parseFromString(await response.text(), 'text/html');
        const nextMain = nextDocument.querySelector('#app-main');
        if (!nextMain || !nextDocument.body.matches('[data-panel-navigation]')) {
            window.location.assign(response.url);
            return;
        }

        replacePanelPage(main, nextMain);
        syncSidebarState(nextDocument);
        document.title = nextDocument.title || document.title;
        if (historyMode === 'push') window.history.pushState({ panel: true }, '', response.url);
        else if (historyMode === 'replace') window.history.replaceState({ panel: true }, '', response.url);
        document.dispatchEvent(new CustomEvent('app:content-loaded'));
    } catch (error) {
        if (error.name === 'AbortError') return;
        showError(error.message || 'No se pudo abrir esta sección.');
    } finally {
        if (navigationController === controller) main.removeAttribute('aria-busy');
    }
}

async function submitAsyncAction(form) {
    const response = await fetch(form.action, {
        method: (form.method || 'POST').toUpperCase(),
        body: new FormData(form),
        credentials: 'same-origin',
        headers: {
            Accept: 'text/html',
            'X-Panel-Action': 'async',
        },
    });

    if (!responseStaysInCurrentPanel(response.url, window.location.href)) {
        window.location.assign(response.url);
        return;
    }

    const contentType = response.headers.get('content-type') || '';
    if (!contentType.includes('text/html')) throw new Error('El servidor no devolvió una vista válida.');

    const html = await response.text();
    const nextDocument = new DOMParser().parseFromString(html, 'text/html');
    const currentMain = document.querySelector('#app-main');
    const nextMain = nextDocument.querySelector('#app-main');

    if (!response.ok || !currentMain || !nextMain) throw new Error('No se pudo actualizar esta pantalla.');

    replaceMainContent(currentMain, nextMain);
    document.title = nextDocument.title || document.title;
    document.dispatchEvent(new CustomEvent('app:content-loaded'));
}

function showError(message) {
    const main = document.querySelector('#app-main');
    if (!main) return;

    const alert = document.createElement('div');
    alert.className = 'alert alert-danger';
    alert.setAttribute('role', 'alert');
    alert.textContent = message;
    main.prepend(alert);
}

export function shouldUsePanelNavigation(link, event) {
    const href = link.href || '';
    const isBinaryReport = /\.(?:pdf|csv|xlsx?)(?:$|[?#])/i.test(href)
        || /\/(?:pdf|excel)(?:\/|$|[?#])/i.test(href);

    return !link.hasAttribute('data-full-navigation') && !event.defaultPrevented && event.button === 0
        && !event.metaKey && !event.ctrlKey && !event.shiftKey && !event.altKey
        && !link.target && !link.hasAttribute('download') && !isBinaryReport;
}

export function installAsyncPanelActions() {
    window.history.replaceState({ panel: true }, '', window.location.href);

    document.addEventListener('click', (event) => {
        const link = event.target.closest('a[href]');
        if (!link || !document.body.matches('[data-panel-navigation]') || (!link.closest('#app-main') && !link.closest('#app-sidebar'))) return;
        if (!shouldUsePanelNavigation(link, event)) return;

        const url = new URL(link.href, window.location.href);
        if (url.origin !== window.location.origin || (url.pathname === window.location.pathname && url.search === window.location.search && url.hash)) return;

        event.preventDefault();
        navigatePanel(url.href);
    });

    document.addEventListener('submit', (event) => {
        const form = event.target;
        if (!form.matches('#app-main form') || form.matches('[data-async-action]') || (form.method || 'get').toLowerCase() !== 'get') return;
        event.preventDefault();
        const url = new URL(form.action || window.location.href, window.location.href);
        new FormData(form).forEach((value, key) => url.searchParams.set(key, value));
        navigatePanel(url.href);
    });

    window.addEventListener('popstate', () => navigatePanel(window.location.href, 'none'));

    document.addEventListener('submit', async (event) => {
        const form = event.target.closest('form[data-async-action]');
        if (!form) return;

        event.preventDefault();
        if (!form.reportValidity() || form.dataset.submitting === 'true') return;

        form.dataset.submitting = 'true';
        const buttons = [...form.querySelectorAll('button, input[type="submit"]')];
        buttons.forEach((button) => { button.disabled = true; });

        try {
            await submitAsyncAction(form);
        } catch (error) {
            showError(error.message || 'No se pudo completar la acción.');
        } finally {
            delete form.dataset.submitting;
            buttons.forEach((button) => { button.disabled = false; });
        }
    });
}
