export function readDiffFile(file) {
    return new Promise((resolve, reject) => {
        if (!file) {
            reject(new Error('No file selected'));
            return;
        }

        const name = (file.name || '').toLowerCase();
        const allowed = name.endsWith('.diff') || name.endsWith('.patch') || name.endsWith('.txt');
        if (!allowed) {
            reject(new Error('Use a .diff, .patch, or .txt file'));
            return;
        }

        const reader = new FileReader();
        reader.onload = function () {
            const content = typeof reader.result === 'string' ? reader.result : '';
            if (!content.trim()) {
                reject(new Error('File is empty'));
                return;
            }
            resolve({ name: file.name, content });
        };
        reader.onerror = function () {
            reject(new Error('Could not read file'));
        };
        reader.readAsText(file);
    });
}
