<div class="settings-modal" id="apps-modal" aria-hidden="true">
    <div class="settings-modal-backdrop" data-close="apps-modal"></div>
    <section class="settings-modal-card" role="dialog" aria-modal="true" aria-label="Apps">
        <button class="settings-modal-close" type="button" aria-label="Close apps" data-close="apps-modal">&times;</button>
        <div class="settings-modal-layout" style="display:flex; flex-direction:column; padding: 24px;">
            <header class="settings-pane-head" style="margin-bottom: 24px;">
                <h3 style="font-size: 20px; font-weight: 600; color: var(--text-main);">Apps</h3>
                <p style="color: var(--text-sub); font-size: 14px; margin-top:4px;">Enhance your workflow with additional utilities.</p>
            </header>
            <div class="settings-provider-list" style="display:grid; gap:12px;">
                <article class="settings-provider-item" id="doc-gen-toggle-btn" style="cursor: pointer; transition: background 0.2s;">
                    <div class="settings-provider-top">
                        <div class="settings-provider-brand">
                            <span class="settings-provider-logo" aria-hidden="true" style="display:flex; align-items:center; justify-content:center; background:var(--panel-stroke); border-radius:8px; width:40px; height:40px;">
                                <svg viewBox="0 0 24 24" focusable="false" style="width:20px; height:20px; color:var(--text-main);">
                                    <path d="M7 4h10a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Z" fill="none" stroke="currentColor" stroke-width="1.8" />
                                    <path d="M7 8h10M7 12h10M7 16h6" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                                </svg>
                            </span>
                            <div>
                                <div class="settings-provider-name" style="font-weight: 500; font-size: 15px; color: var(--text-main);">Document Generator</div>
                                <div class="settings-provider-sub" style="font-size: 13px; color: var(--text-sub);">Generate structured markdown reports from chat</div>
                            </div>
                        </div>
                        <span class="settings-provider-pill">Activate</span>
                    </div>
                </article>
            </div>
        </div>
    </section>
</div>
