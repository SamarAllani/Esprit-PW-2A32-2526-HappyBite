document.addEventListener('DOMContentLoaded', () => {
    const registerForm = document.getElementById('register-form');
    const loginForm = document.getElementById('login-form');

    if (registerForm) {
        registerForm.addEventListener('input', validateRegisterForm);
        registerForm.addEventListener('submit', validateBeforeSubmit);
    }

    if (loginForm) {
        loginForm.addEventListener('input', validateLoginForm);
        loginForm.addEventListener('submit', validateBeforeSubmit);
    }

    function validateRegisterForm(event) {
        const target = event.target;
        const errorMsg = target.nextElementSibling;

        if (target.id === 'prenom' || target.id === 'nom') {
            const regex = /^[a-zA-ZÀ-ÿ\s]{3,50}$/;
            if (!regex.test(target.value)) {
                errorMsg.textContent = 'Le champ doit contenir 3 a 50 caracteres alphabetiques.';
                target.classList.add('invalid');
            } else {
                errorMsg.textContent = '';
                target.classList.remove('invalid');
            }
        }

        if (target.id === 'email') {
            const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!regex.test(target.value)) {
                errorMsg.textContent = 'Please enter a valid email address.';
                target.classList.add('invalid');
            } else {
                errorMsg.textContent = '';
                target.classList.remove('invalid');
            }
        }

        if (target.id === 'password') {
            const regex = /^(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/;
            const strengthIndicator = document.getElementById('password-strength');
            if (!regex.test(target.value)) {
                errorMsg.textContent = 'Password must be at least 8 characters, include an uppercase letter, a number, and a special character.';
                target.classList.add('invalid');
                strengthIndicator.textContent = 'Weak';
                strengthIndicator.style.color = 'red';
            } else {
                errorMsg.textContent = '';
                target.classList.remove('invalid');
                strengthIndicator.textContent = 'Strong';
                strengthIndicator.style.color = 'green';
            }
        }

        if (target.id === 'confirm-password') {
            const password = document.getElementById('password').value;
            if (target.value !== password) {
                errorMsg.textContent = 'Passwords do not match.';
                target.classList.add('invalid');
            } else {
                errorMsg.textContent = '';
                target.classList.remove('invalid');
            }
        }

    }

    function validateLoginForm(event) {
        const target = event.target;
        const errorMsg = target.nextElementSibling;

        if (target.id === 'email') {
            const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!regex.test(target.value)) {
                errorMsg.textContent = 'Please enter a valid email address.';
                target.classList.add('invalid');
            } else {
                errorMsg.textContent = '';
                target.classList.remove('invalid');
            }
        }

        if (target.id === 'password') {
            if (target.value.trim() === '') {
                errorMsg.textContent = 'Password cannot be empty.';
                target.classList.add('invalid');
            } else {
                errorMsg.textContent = '';
                target.classList.remove('invalid');
            }
        }
    }

    function validateBeforeSubmit(event) {
        const form = event.target;
        const invalidFields = form.querySelectorAll('.invalid');
        if (invalidFields.length > 0) {
            event.preventDefault();
            alert('Please fix the errors before submitting.');
        }
    }

    function previewImage() {}
});