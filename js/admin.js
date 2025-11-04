function showTab(tabName) {
    document.querySelectorAll('.tab').forEach(tab => tab.classList.add('hidden'));
    document.getElementById(tabName).classList.remove('hidden');
    document.querySelectorAll('nav button').forEach(btn => btn.classList.remove('active'));
    document.getElementById(tabName + '-btn').classList.add('active');
}

// Handle edit form toggles for both Users and Media
function toggleEditForm(id, type = 'media') {
    let formId = '';
    if (type === 'user') {
        formId = 'edit-form-user-' + id;
    } else if (type === 'copy') {
    formId = 'edit-form-copy-' + id;
    } else {
        formId = 'edit-form-media-' + id;
    }
    const form = document.getElementById(formId);
    if (form) form.classList.toggle('hidden');
}

// Default tab on load
document.addEventListener('DOMContentLoaded', () => showTab('users'));
