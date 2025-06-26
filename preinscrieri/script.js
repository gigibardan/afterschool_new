document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('preregisterForm');
    const submitBtn = form.querySelector('.submit-btn');
    const loadingOverlay = document.getElementById('loadingOverlay');
    const successModal = document.getElementById('successModal');
    const closeModalBtn = document.getElementById('closeModal');

    // Form submission
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        if (!validateForm()) {
            return;
        }

        // Show loading
        showLoading();
        
        try {
            const formData = new FormData(form);
            
            // Convert checkboxes to boolean values
            formData.set('acord_gdpr', document.getElementById('acord_gdpr').checked ? '1' : '0');
            formData.set('acord_marketing', document.getElementById('acord_marketing').checked ? '1' : '0');
            formData.set('acord_foto', document.getElementById('acord_foto').checked ? '1' : '0');
            
            const response = await fetch('../api/debug_submit.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            hideLoading();
            
            if (result.success) {
                showSuccessModal();
                form.reset();
            } else {
                alert('Eroare: ' + (result.message || 'A apărut o problemă la trimiterea formularului.'));
            }
            
        } catch (error) {
            hideLoading();
            console.error('Error:', error);
            alert('A apărut o eroare de conexiune. Vă rugăm să încercați din nou.');
        }
    });

    // Form validation
    function validateForm() {
        const requiredFields = form.querySelectorAll('[required]');
        let isValid = true;

        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                showFieldError(field, 'Acest câmp este obligatoriu');
                isValid = false;
            } else {
                clearFieldError(field);
            }
        });

        // Email validation
        const email = document.getElementById('email_parinte');
        if (email.value && !isValidEmail(email.value)) {
            showFieldError(email, 'Adresa de email nu este validă');
            isValid = false;
        }

        // Phone validation
        const phone = document.getElementById('telefon_parinte');
        if (phone.value && !isValidPhone(phone.value)) {
            showFieldError(phone, 'Numărul de telefon nu este valid');
            isValid = false;
        }

        // GDPR consent
        const gdprCheckbox = document.getElementById('acord_gdpr');
if (!gdprCheckbox.checked) {
    alert('Este obligatoriu să acceptați prelucrarea datelor personale conform GDPR.');
    // ADAUGĂ această linie:
    gdprCheckbox.focus();
    isValid = false;
}

        return isValid;
    }

    function showFieldError(field, message) {
        clearFieldError(field);
        
        field.style.borderColor = 'var(--color-error)';
        
        const errorDiv = document.createElement('div');
        errorDiv.className = 'field-error';
        errorDiv.style.color = 'var(--color-error)';
        errorDiv.style.fontSize = '0.875rem';
        errorDiv.style.marginTop = '0.25rem';
        errorDiv.textContent = message;
        
        field.parentNode.appendChild(errorDiv);
    }

    function clearFieldError(field) {
        field.style.borderColor = 'var(--color-gray-light)';
        const existingError = field.parentNode.querySelector('.field-error');
        if (existingError) {
            existingError.remove();
        }
    }

    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    function isValidPhone(phone) {
        const phoneRegex = /^[+]?[0-9\s\-\(\)]{8,15}$/;
        return phoneRegex.test(phone.replace(/\s/g, ''));
    }

    function showLoading() {
        loadingOverlay.style.display = 'flex';
        submitBtn.disabled = false;
    }

    function hideLoading() {
        loadingOverlay.style.display = 'none';
        submitBtn.disabled = false;
    }

    function showSuccessModal() {
        successModal.style.display = 'block';
    }

    function hideSuccessModal() {
        successModal.style.display = 'none';
    }

    // Modal events
    closeModalBtn.addEventListener('click', hideSuccessModal);
    
    successModal.addEventListener('click', function(e) {
        if (e.target === successModal) {
            hideSuccessModal();
        }
    });

    // Clear errors when user starts typing
    const formInputs = form.querySelectorAll('input, select, textarea');
    formInputs.forEach(input => {
        input.addEventListener('input', function() {
            clearFieldError(this);
        });
    });
});