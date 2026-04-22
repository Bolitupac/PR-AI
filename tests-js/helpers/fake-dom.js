class FakeClassList {
    constructor() {
        this.set = new Set();
    }

    add(...tokens) {
        tokens.filter(Boolean).forEach((token) => this.set.add(token));
    }

    remove(...tokens) {
        tokens.forEach((token) => this.set.delete(token));
    }

    contains(token) {
        return this.set.has(token);
    }

    toggle(token, force) {
        if (force === undefined) {
            if (this.contains(token)) {
                this.remove(token);
                return false;
            }
            this.add(token);
            return true;
        }

        if (force) this.add(token);
        else this.remove(token);
        return Boolean(force);
    }

    toString() {
        return Array.from(this.set).join(' ');
    }
}

class FakeElement {
    constructor(tagName, ownerDocument) {
        this.tagName = String(tagName || 'div').toUpperCase();
        this.ownerDocument = ownerDocument;
        this.children = [];
        this.parentElement = null;
        this.attributes = new Map();
        this.listeners = new Map();
        this.classList = new FakeClassList();
        this.dataset = {};
        this.style = {};
        this.textContent = '';
        this.scrollTop = 0;
        this.scrollHeight = 0;
        this.download = '';
        this.href = '';
        this.hidden = false;
        this.disabled = false;
        this.value = '';
    }

    set id(value) {
        this.setAttribute('id', value);
    }

    get id() {
        return this.getAttribute('id') || '';
    }

    set className(value) {
        this.classList = new FakeClassList();
        String(value || '')
            .split(/\s+/)
            .filter(Boolean)
            .forEach((token) => this.classList.add(token));
    }

    get className() {
        return this.classList.toString();
    }

    set innerHTML(value) {
        this._innerHTML = String(value || '');
        this.children = [];
        this.textContent = '';
    }

    get innerHTML() {
        return this._innerHTML || '';
    }

    appendChild(child) {
        if (!child) return child;
        child.parentElement = this;
        this.children.push(child);
        this._touchScroll();
        return child;
    }

    insertBefore(child, beforeNode) {
        if (!child) return child;
        child.parentElement = this;
        if (!beforeNode) {
            this.children.push(child);
        } else {
            const index = this.children.indexOf(beforeNode);
            if (index === -1) this.children.push(child);
            else this.children.splice(index, 0, child);
        }
        this._touchScroll();
        return child;
    }

    remove() {
        if (!this.parentElement) return;
        const siblings = this.parentElement.children;
        const index = siblings.indexOf(this);
        if (index !== -1) siblings.splice(index, 1);
        this.parentElement = null;
    }

    setAttribute(name, value) {
        const normalized = String(name);
        const stringValue = String(value);
        this.attributes.set(normalized, stringValue);
        if (normalized === 'id') {
            this.ownerDocument.elementsById.set(stringValue, this);
        }
        if (normalized === 'value') {
            this.value = stringValue;
        }
        if (normalized.startsWith('data-')) {
            const key = normalized
                .slice(5)
                .replace(/-([a-z])/g, (_match, letter) => letter.toUpperCase());
            this.dataset[key] = stringValue;
        }
    }

    getAttribute(name) {
        return this.attributes.get(String(name)) || null;
    }

    removeAttribute(name) {
        const normalized = String(name);
        this.attributes.delete(normalized);
        if (normalized.startsWith('data-')) {
            const key = normalized
                .slice(5)
                .replace(/-([a-z])/g, (_match, letter) => letter.toUpperCase());
            delete this.dataset[key];
        }
    }

    addEventListener(type, handler) {
        const key = String(type);
        if (!this.listeners.has(key)) this.listeners.set(key, []);
        this.listeners.get(key).push(handler);
    }

    dispatchEvent(event) {
        const handlers = this.listeners.get(event.type) || [];
        handlers.forEach((handler) => handler.call(this, event));
        return true;
    }

    click() {
        this.dispatchEvent({
            type: 'click',
            target: this,
            stopPropagation() {},
            preventDefault() {},
        });
    }

    querySelectorAll(selector) {
        const results = [];
        const match = (node) => {
            if (selector.startsWith('.')) {
                return node.classList.contains(selector.slice(1));
            }
            if (selector.startsWith('#')) {
                return node.id === selector.slice(1);
            }
            const attrMatch = selector.match(/^\[([^=]+)=\"([^\"]+)\"\]$/);
            if (attrMatch) {
                return node.getAttribute(attrMatch[1]) === attrMatch[2];
            }
            return node.tagName.toLowerCase() === selector.toLowerCase();
        };

        const visit = (node) => {
            node.children.forEach((child) => {
                if (match(child)) results.push(child);
                visit(child);
            });
        };

        visit(this);
        return results;
    }

    querySelector(selector) {
        return this.querySelectorAll(selector)[0] || null;
    }

    _touchScroll() {
        this.scrollHeight = this.children.length;
        this.scrollTop = this.scrollHeight;
    }
}

class FakeDocument {
    constructor() {
        this.listeners = new Map();
        this.elementsById = new Map();
        this.body = new FakeElement('body', this);
        this.documentElement = new FakeElement('html', this);
        this.meta = [];
    }

    createElement(tagName) {
        return new FakeElement(tagName, this);
    }

    getElementById(id) {
        return this.elementsById.get(String(id)) || null;
    }

    addEventListener(type, handler) {
        const key = String(type);
        if (!this.listeners.has(key)) this.listeners.set(key, []);
        this.listeners.get(key).push(handler);
    }

    dispatchEvent(event) {
        const handlers = this.listeners.get(event.type) || [];
        handlers.forEach((handler) => handler.call(this, event));
        return true;
    }

    querySelector(selector) {
        if (selector === 'meta[name="csrf-token"]') {
            return this.meta.find((item) => item.getAttribute('name') === 'csrf-token') || null;
        }
        return this.body.querySelector(selector);
    }

    querySelectorAll(selector) {
        return this.body.querySelectorAll(selector);
    }
}

export class FakeCustomEvent {
    constructor(type, init = {}) {
        this.type = type;
        this.detail = init.detail;
    }
}

export function createFakeDom() {
    const document = new FakeDocument();
    return { document, FakeElement };
}

export function appendElement(document, { tag = 'div', id = '', className = '', parent = null, attributes = {} } = {}) {
    const element = document.createElement(tag);
    if (id) element.id = id;
    if (className) element.className = className;
    Object.entries(attributes).forEach(([name, value]) => element.setAttribute(name, value));
    (parent || document.body).appendChild(element);
    if (String(tag).toLowerCase() === 'meta') {
        document.meta.push(element);
    }
    return element;
}
