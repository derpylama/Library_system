let isLogin = true;
function toggleForm() {
    const formTitle = document.getElementById('form-title');
    const action = document.getElementById('action');
    const submitBtn = document.getElementById('submit-btn');
    const toggleText = document.getElementById('toggle-text');
    
    if (isLogin) {
        formTitle.innerText = 'Register';
        action.value = 'register';
        submitBtn.innerText = 'Create Account';
        toggleText.innerHTML = '<a href="#" onclick="toggleForm()">Login</a>';
    } else {
        formTitle.innerText = 'Login';
        action.value = 'login';
        submitBtn.innerText = 'Login';
        toggleText.innerHTML = '<a href="#" onclick="toggleForm()">Create account</a>';
    }
    isLogin = !isLogin;
}