function showTab(tabName) {
    sessionStorage.setItem('currentTab', tabName);
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
document.addEventListener('DOMContentLoaded', () => {
    const savedTab = sessionStorage.getItem('currentTab') || 'users';
    showTab(savedTab);
    // On change for add-media-sab-preset to when selected option has value 'custom', remove hidden class from add-media-sab-custom, else add hidden class to add-media-sab-custom
    const sabCodePreset = document.getElementById('add-media-sab-preset');
    const sabCodeCustom = document.getElementById('add-media-sab-custom');
    if (sabCodePreset && sabCodeCustom) {
        sabCodePreset.addEventListener('change', () => {
            if (sabCodePreset.value === 'custom') {
                sabCodeCustom.classList.remove('hidden');
            } else {
                sabCodeCustom.classList.add('hidden');
            }
        });
    }

    // On change for any class .edit-media-sab-preset to when selected option has value 'custom', remove hidden class from corresponding .edit-media-sab-custom, else add hidden class to corresponding .edit-media-sab-custom
    //   both share same data-id=
    document.querySelectorAll('.edit-media-sab-preset').forEach(presetSelect => {
        presetSelect.addEventListener('change', () => {
            const id = presetSelect.getAttribute('data-id');
            const customInput = document.querySelector('.edit-media-sab-custom[data-id="' + id + '"]');
            if (presetSelect.value === 'custom') {
                customInput.classList.remove('hidden');
            } else {
                customInput.classList.add('hidden');
            }
        });
    });

});
