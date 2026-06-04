const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

export async function fetchConversations() {
    try {
        const res = await fetch('/api/chat/conversations', {
            headers: {
                'Accept': 'application/json',
            },
        });
        if (!res.ok) return [];
        const data = await res.json();
        return data.conversations || [];
    } catch (err) {
        console.error('Failed to fetch chat conversations:', err);
        return [];
    }
}

export async function deleteConversation(id) {
    try {
        const res = await fetch(`/api/chat/conversations/${id}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
        });
        return res.ok;
    } catch (err) {
        console.error('Failed to delete conversation:', err);
        return false;
    }
}

export function renderSidebarHistory(conversations, activeId = null, onSelect = null) {
    const list = document.getElementById('sidebar-chat-history-list');
    if (!list) return;

    if (!Array.isArray(conversations) || conversations.length === 0) {
        list.innerHTML = `<li class="sidebar-label" style="font-size:11px; opacity:0.6; padding: 4px 6px;">No recent chats</li>`;
        return;
    }

    list.innerHTML = conversations.map((chat) => {
        const isActive = String(chat.id) === String(activeId);
        const url = `/auditor?conversation_id=${chat.id}`;
        
        return `
            <li class="sidebar-chat-item-wrapper" data-id="${chat.id}" style="position: relative; group;">
                <div class="sidebar-item-container" style="display: flex; align-items: center; justify-content: space-between; width: 100%;">
                    <a class="sidebar-item ${isActive ? 'is-active' : ''}" href="${url}" style="flex-grow: 1; padding-right: 32px;" data-chat-id="${chat.id}">
                        <span class="sidebar-icon" aria-hidden="true" style="flex-shrink:0;">
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                            </svg>
                        </span>
                        <span class="sidebar-label" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 140px;" title="${escapeHtml(chat.title)}">${escapeHtml(chat.title)}</span>
                    </a>
                    <button class="delete-chat-btn" data-id="${chat.id}" title="Delete chat" style="
                        position: absolute;
                        right: 8px;
                        background: none;
                        border: none;
                        color: var(--text-soft);
                        cursor: pointer;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        padding: 4px;
                        border-radius: 4px;
                        z-index: 10;
                    " aria-label="Delete chat">
                        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="3 6 5 6 21 6"></polyline>
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                        </svg>
                    </button>
                </div>
            </li>
        `;
    }).join('');

    // Attach listeners
    list.querySelectorAll('.delete-chat-btn').forEach((btn) => {
        btn.addEventListener('click', async (e) => {
            e.preventDefault();
            e.stopPropagation();
            if (confirm('Are you sure you want to delete this chat conversation?')) {
                const id = btn.dataset.id;
                const success = await deleteConversation(id);
                if (success) {
                    // Refresh
                    const updated = await fetchConversations();
                    renderSidebarHistory(updated, activeId, onSelect);
                    renderImportsHistory(updated);
                    
                    // If deleted the active one, redirect to clean auditor
                    if (String(id) === String(activeId)) {
                        window.location.href = '/auditor';
                    }
                }
            }
        });
    });

    if (onSelect) {
        list.querySelectorAll('a[data-chat-id]').forEach((link) => {
            link.addEventListener('click', (e) => {
                const id = link.dataset.chatId;
                // If on auditor page, intercept navigation to dynamically load
                if (window.location.pathname.startsWith('/auditor') || window.location.pathname === '/') {
                    e.preventDefault();
                    onSelect(id);
                }
            });
        });
    }
}

export function renderImportsHistory(conversations) {
    const list = document.getElementById('imports-chat-history-list');
    if (!list) return;

    if (!Array.isArray(conversations) || conversations.length === 0) {
        list.innerHTML = `
            <li class="imports-history-item" style="color: var(--text-soft); font-size: 12px;">
                No recent chat history found.
            </li>
        `;
        return;
    }

    list.innerHTML = conversations.map((chat) => {
        const time = new Date(chat.updated_at).toLocaleDateString(undefined, {
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        });
        const providerName = chat.provider === 'deepseek' ? 'DeepSeek' : 'OpenAI';
        const modelName = chat.model || 'default';

        return `
            <li class="imports-history-item imports-activity-item">
                <div class="imports-activity-flex">
                    <div class="imports-activity-main">
                        <span class="imports-activity-badge" style="background: var(--brand-soft); color: var(--brand); border-radius: 4px; padding: 2px 6px; font-size: 10px; font-weight: 700;">Chat</span>
                        <span class="imports-activity-title" style="font-weight: 600;">${escapeHtml(chat.title)}</span>
                    </div>
                    <div class="imports-activity-meta" style="margin-top: 4px; font-size: 11px; color: var(--text-soft); display: flex; gap: 8px;">
                        <span>${providerName} (${modelName})</span>
                        <span>•</span>
                        <span>${time}</span>
                    </div>
                    <a class="imports-activity-action-btn" href="/auditor?conversation_id=${chat.id}" style="
                        display: inline-flex;
                        align-items: center;
                        justify-content: center;
                        padding: 4px 10px;
                        background: var(--brand);
                        color: #fff;
                        border-radius: 4px;
                        font-size: 12px;
                        font-weight: 600;
                        text-decoration: none;
                        margin-top: 8px;
                    ">
                        Open
                    </a>
                </div>
            </li>
        `;
    }).join('');
}

function escapeHtml(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#39;');
}

export async function initGlobalChatHistory(activeId = null, onSelect = null) {
    const conversations = await fetchConversations();
    renderSidebarHistory(conversations, activeId, onSelect);
    renderImportsHistory(conversations);
}
