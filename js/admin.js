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

    // const dialog = document.getElementById("password-confirm-dialog");
    // const wrapper = document.getElementById("popup-wrapper");
    // const form = document.getElementById("password-confirm-form");
    // const input = document.getElementById("confirm-password-input");
    // const error = document.getElementById("password-error");
    // const cancelBtn = document.getElementById("cancel-password-confirm");

    // let pendingForm = null; // The form waiting for confirmation

    // if(form != null){
    //     // Intercept all delete or edit POST forms
    //     document.querySelectorAll("form[method='POST']").forEach(f => {
    //         f.addEventListener("submit", e => {
    //         // Skip add / non-dangerous actions
    //         const hasDangerousField = f.querySelector("[name^='delete_']") || f.querySelector("[name^='edit_']");
    //         if (!hasDangerousField) return; // allow normal submit
    
    //         e.preventDefault();
    //         pendingForm = f;
    //         showPasswordDialog();
    //         });
    //     });
        
    //     console.log();
    //     form.addEventListener("submit", async e => {
    //         e.preventDefault();
    //         const password = input.value.trim();
    //         if (!password) return;
    
    //         const result = await fetch("./php/auth.php", {
    //         method: "POST",
    //         headers: { "Content-Type": "application/x-www-form-urlencoded" },
    //         body: `password=${encodeURIComponent(password)}`
    //         });
    
    //         if (result.ok) {
    //         if (pendingForm) pendingForm.submit(); // allow original form submit now
    //         } else {
    //         error.textContent = "Incorrect password. Try again.";
    //         error.classList.remove("hidden");
    //         }
    //     });


    // }

});
