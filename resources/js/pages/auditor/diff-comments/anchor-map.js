import { parseDiffFiles } from './parse-diff-files';
import { normalizePath } from './utils';

function extractHeaderPath(wrapper) {
    const label = wrapper.querySelector('.d2h-file-name');
    return normalizePath(label?.textContent || '');
}

export function mapRenderedFiles(container, diffText) {
    const parsedFiles = parseDiffFiles(diffText);
    const wrappers = Array.from(container.querySelectorAll('.d2h-file-wrapper'));
    const wrapperMap = new Map();

    wrappers.forEach((wrapper, index) => {
        const canonicalPath = parsedFiles[index]?.canonicalPath || extractHeaderPath(wrapper);
        if (!canonicalPath) return;

        wrapper.dataset.diffPath = canonicalPath;
        wrapperMap.set(canonicalPath, wrapper);
    });

    return wrapperMap;
}

function findRowCodeCell(row, cellIndex = 1) {
    return row.children[cellIndex] || null;
}

function findSideBySideAnchorCell(wrapper, targetLine, side = 'RIGHT') {
    const tableSelector = side === 'LEFT'
        ? '.d2h-file-side-diff:first-child .d2h-diff-tbody'
        : '.d2h-file-side-diff:last-child .d2h-diff-tbody';
    const table = wrapper.querySelector(tableSelector);
    if (!table) return null;

    const rows = Array.from(table.querySelectorAll('tr'));
    for (const row of rows) {
        const lineCell = row.querySelector('.d2h-code-side-linenumber');
        if (!lineCell) continue;

        const lineNumber = Number.parseInt(lineCell.textContent.trim(), 10);
        if (lineNumber === targetLine) {
            return findRowCodeCell(row);
        }
    }

    return null;
}

function findLineByLineAnchorCell(wrapper, targetLine, side = 'RIGHT') {
    const rows = Array.from(wrapper.querySelectorAll('.d2h-file-diff .d2h-diff-tbody tr'));
    for (const row of rows) {
        const lineCell = row.querySelector('.d2h-code-linenumber');
        if (!lineCell) continue;

        const newLineNumber = Number.parseInt(lineCell.querySelector('.line-num2')?.textContent.trim() || '', 10);
        const oldLineNumber = Number.parseInt(lineCell.querySelector('.line-num1')?.textContent.trim() || '', 10);
        const targetMatches = side === 'LEFT'
            ? oldLineNumber === targetLine
            : newLineNumber === targetLine;

        if (targetMatches) {
            return findRowCodeCell(row);
        }
    }

    return null;
}

export function findAnchorCell(wrapperMap, path, lineNumber, outputFormat, side = 'RIGHT') {
    const wrapper = wrapperMap.get(normalizePath(path));
    if (!wrapper || !Number.isInteger(lineNumber)) return null;

    if (outputFormat === 'line-by-line') {
        return findLineByLineAnchorCell(wrapper, lineNumber, side);
    }

    return findSideBySideAnchorCell(wrapper, lineNumber, side);
}
