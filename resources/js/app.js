import './bootstrap';
import 'bootstrap/dist/js/bootstrap.bundle.min.js';

document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('collapsed');
            document.getElementById('main-content').classList.toggle('expanded');
        });