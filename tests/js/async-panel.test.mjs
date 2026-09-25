import test from 'node:test';
import assert from 'node:assert/strict';
import { replaceMainContent, replacePanelPage, responseStaysInCurrentPanel, shouldUsePanelNavigation, syncCollapsibleGroup } from '../../resources/js/async-panel.js';

test('replaces only main contents and preserves its scroll position', () => {
    const currentMain = { innerHTML: '<p>Anterior</p>', scrollTop: 475 };
    const nextMain = { innerHTML: '<p>Actualizado</p>' };

    const returnedMain = replaceMainContent(currentMain, nextMain);

    assert.equal(returnedMain, currentMain);
    assert.equal(currentMain.innerHTML, '<p>Actualizado</p>');
    assert.equal(currentMain.scrollTop, 475);
});

test('partial replacement is limited to responses that stay on the same screen', () => {
    assert.equal(responseStaysInCurrentPanel('http://localhost/control', 'http://localhost/control'), true);
    assert.equal(responseStaysInCurrentPanel('http://localhost/control?ok=1', 'http://localhost/control'), true);
    assert.equal(responseStaysInCurrentPanel('http://localhost/login', 'http://localhost/control'), false);
    assert.equal(responseStaysInCurrentPanel('http://localhost/productos', 'http://localhost/productos/4/edit'), false);
});

test('panel navigation replaces main and leaves the scrolled sidebar node untouched', () => {
    const sidebar = { scrollTop: 420 };
    const currentMain = { innerHTML: '<p>Dashboard</p>', scrollTop: 180 };
    const nextMain = { innerHTML: '<p>Movimientos</p>' };

    replacePanelPage(currentMain, nextMain);

    assert.equal(currentMain.innerHTML, '<p>Movimientos</p>');
    assert.equal(currentMain.scrollTop, 0);
    assert.equal(sidebar.scrollTop, 420);
});

test('full-navigation links bypass panel replacement', () => {
    const link = { hasAttribute: (name) => name === 'data-full-navigation', target: '', href: 'http://localhost/reportes' };
    const event = { defaultPrevented: false, button: 0, metaKey: false, ctrlKey: false, shiftKey: false, altKey: false };
    assert.equal(shouldUsePanelNavigation(link,event), false);
});

test('binary report links bypass panel replacement', () => {
    const event = { defaultPrevented: false, button: 0, metaKey: false, ctrlKey: false, shiftKey: false, altKey: false };
    const link = (href) => ({ href, target: '', hasAttribute: () => false });

    assert.equal(shouldUsePanelNavigation(link('http://localhost/descartes-productos/reporte.pdf'), event), false);
    assert.equal(shouldUsePanelNavigation(link('http://localhost/reportes-comerciales/productos.csv'), event), false);
    assert.equal(shouldUsePanelNavigation(link('http://localhost/reportes-comerciales'), event), true);
});

test('panel navigation synchronizes the open sidebar group', () => {
    const classes = (initial = []) => {
        const values = new Set(initial);
        return {
            contains: value => values.has(value),
            toggle: (value, enabled) => enabled ? values.add(value) : values.delete(value),
        };
    };
    const currentGroup = { classList: classes(['collapse', 'show']) };
    const currentToggle = {
        classList: classes(['active-group']),
        setAttribute(name, value) { this[name] = value; },
    };
    const nextGroup = { classList: classes(['collapse']) };
    const nextToggle = { classList: classes([]) };

    syncCollapsibleGroup(currentGroup, currentToggle, nextGroup, nextToggle);

    assert.equal(currentGroup.classList.contains('show'), false);
    assert.equal(currentToggle.classList.contains('active-group'), false);
    assert.equal(currentToggle['aria-expanded'], 'false');
});
