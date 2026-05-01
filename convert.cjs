const fs = require('fs');
const marked = require('marked');

// Read docinfo.txt
const doc = fs.readFileSync('docinfo.txt', 'utf8');

// Find the content bounded by the markdown block
const mdStart = doc.indexOf('```markdown') + 11;
const mdEnd = doc.lastIndexOf('```');
let mdContent = doc.substring(mdStart, mdEnd);

// Find chapters
const chapters = [];
const chapterRegex = /## Chapter (\d+): (.*?) \{#.*?\}([\s\S]*?)(?=## Chapter \d+:|## Support & Feedback|## Quick Reference)/g;

let match;
while ((match = chapterRegex.exec(mdContent)) !== null) {
    chapters.push({
        num: match[1],
        title: match[2].trim(),
        content: match[3].trim()
    });
}

// Custom renderer for marked to apply inline styles
const renderer = new marked.Renderer();
renderer.heading = function (text, level) {
    if (level === 3) {
        return `<h4 style="color: var(--text-main); margin-top: 24px; font-size: 16px;">${text}</h4>\n`;
    }
    if (level === 4) {
        return `<h5 style="color: var(--text-main); margin-top: 20px; font-size: 15px; font-weight: 600;">${text}</h5>\n`;
    }
    return `<h${level}>${text}</h${level}>\n`;
};
renderer.list = function (body, ordered, start) {
    const type = ordered ? 'ol' : 'ul';
    return `<${type} style="padding-left: 20px;">\n${body}</${type}>\n`;
};
renderer.listitem = function (text) {
    return `<li style="margin-bottom: 8px;">${text}</li>\n`;
};
renderer.table = function(header, body) {
    return `<table style="width: 100%; border-collapse: collapse; margin-top: 16px; margin-bottom: 16px; font-size: 13px;">
<thead style="background: var(--panel-hover); border-bottom: 1px solid var(--panel-stroke);">
${header}</thead>
<tbody>${body}</tbody></table>\n`;
};
renderer.tablerow = function(content) {
    return `<tr style="border-bottom: 1px solid var(--panel-stroke);">${content}</tr>\n`;
};
renderer.tablecell = function(content, flags) {
    const type = flags.header ? 'th' : 'td';
    return `<${type} style="padding: 8px; text-align: left;">${content}</${type}>\n`;
};

marked.setOptions({ renderer });

let sidebarHTML = `<div class="settings-nav-sub" id="help-nav-sub" style="display:none;">\n`;
let sectionsHTML = '';

chapters.forEach(ch => {
    const paneId = `help-c${ch.num}`;
    sidebarHTML += `    <button class="settings-nav-btn settings-nav-sub-btn" type="button" data-settings-tab="${paneId}">${ch.num}. ${ch.title}</button>\n`;
    
    let htmlContent = marked.parse(ch.content);
    
    sectionsHTML += `
                <section class="settings-pane" data-settings-pane="${paneId}">
                    <header class="settings-pane-head">
                        <h3>${ch.title}</h3>
                    </header>
                    <div class="auto-hide-scrollbar" style="padding: 24px; color: var(--text-muted); line-height: 1.6; font-size: 14px; max-height: 55vh; overflow-y: auto;">
                        ${htmlContent}
                    </div>
                </section>\n`;
});
sidebarHTML += `</div>`;

// Read settings-modal.blade.php
let blade = fs.readFileSync('resources/views/partials/settings-modal.blade.php', 'utf8');

// Replace sidebar
const sidebarRegex = /<div class="settings-nav-sub" id="help-nav-sub" style="display:none;">[\s\S]*?<\/div>/;
blade = blade.replace(sidebarRegex, sidebarHTML);

// Replace panes
// The panes start at <section class="settings-pane" data-settings-pane="help-getting-started">
// And end at the closing </section> of help-developer
const panesRegex = /<section class="settings-pane" data-settings-pane="help-getting-started">[\s\S]*?<\/section>\s*<\/div>\s*<\/div>/;

// Wait, the regex needs to accurately capture all help panes and end right before the closing div of the settings-modal-content
const panesStart = blade.indexOf('<section class="settings-pane" data-settings-pane="help-getting-started">');
const panesEnd = blade.indexOf('</div>\n        </div>\n    </div>\n</dialog>');

if (panesStart !== -1 && panesEnd !== -1) {
    const beforePanes = blade.substring(0, panesStart);
    const afterPanes = blade.substring(panesEnd);
    blade = beforePanes + sectionsHTML + '            ' + afterPanes;
} else {
    console.error("Could not find panes to replace");
}

fs.writeFileSync('resources/views/partials/settings-modal.blade.php', blade);
console.log('Successfully updated settings-modal.blade.php');
