import './bootstrap';
import 'diff2html/bundles/css/diff2html.min.css';
import { Diff2HtmlUI } from 'diff2html/lib/ui/js/diff2html-ui-slim.js';
import { initAuditorPage } from './pages/auditor';
import { initImportsPage } from './pages/imports';

window.Diff2HtmlUI = Diff2HtmlUI;

document.addEventListener('DOMContentLoaded', function () {
    initAuditorPage();
    initImportsPage();
});
