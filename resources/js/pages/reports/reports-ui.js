export function initReportsUI() {
    const sectionList = document.getElementById('reports-section-list');
    const addSectionButton = document.getElementById('reports-add-section-btn');
    let dragSource = null;
    let customSectionCount = 1;

    const renumberSections = () => {
        const items = Array.from(sectionList?.querySelectorAll('.reports-section-item') || []);
        items.forEach((item, index) => {
            const badge = item.querySelector('.reports-section-number');
            if (badge) {
                badge.textContent = String(index + 1);
            }
        });
    };

    const bindDragEvents = (item) => {
        item.addEventListener('dragstart', () => {
            dragSource = item;
            item.classList.add('is-dragging');
        });

        item.addEventListener('dragend', () => {
            item.classList.remove('is-dragging');
            dragSource = null;
            renumberSections();
        });

        item.addEventListener('dragover', (event) => {
            event.preventDefault();
        });

        item.addEventListener('drop', (event) => {
            event.preventDefault();
            if (!dragSource || dragSource === item || !sectionList) return;

            const listItems = Array.from(sectionList.querySelectorAll('.reports-section-item'));
            const sourceIndex = listItems.indexOf(dragSource);
            const targetIndex = listItems.indexOf(item);

            if (sourceIndex < targetIndex) {
                item.insertAdjacentElement('afterend', dragSource);
            } else {
                item.insertAdjacentElement('beforebegin', dragSource);
            }

            renumberSections();
        });
    };

    Array.from(sectionList?.querySelectorAll('.reports-section-item') || []).forEach(bindDragEvents);

    addSectionButton?.addEventListener('click', () => {
        if (!sectionList) return;

        const item = document.createElement('article');
        item.className = 'reports-section-item';
        item.draggable = true;
        item.innerHTML = `
            <span class="reports-section-number">0</span>
            <label class="reports-section-toggle">
                <input type="checkbox" checked>
                <span>Custom Section ${customSectionCount}</span>
            </label>
            <button type="button" class="reports-drag-handle" aria-label="Drag section">⋮⋮</button>
        `;

        customSectionCount += 1;
        sectionList.appendChild(item);
        bindDragEvents(item);
        renumberSections();
    });

    renumberSections();
}
