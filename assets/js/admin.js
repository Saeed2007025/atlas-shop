/**
 * Atlas Shop Admin Panel JavaScript
 */

document.addEventListener('DOMContentLoaded', function () {

    // Image Upload Live Preview
    const imageInputs = document.querySelectorAll('.image-upload-input');
    imageInputs.forEach(input => {
        input.addEventListener('change', function () {
            const previewId = this.getAttribute('data-preview');
            const previewImg = document.querySelector('#' + previewId);
            if (this.files && this.files[0] && previewImg) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    previewImg.src = e.target.result;
                    previewImg.style.display = 'block';
                };
                reader.readAsDataURL(this.files[0]);
            }
        });
    });

    // Tab Navigation
    const tabButtons = document.querySelectorAll('.admin-tab-btn');
    const tabContents = document.querySelectorAll('.admin-tab-content');

    tabButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            const target = this.getAttribute('data-tab');
            tabButtons.forEach(b => b.classList.remove('active'));
            tabContents.forEach(c => c.style.display = 'none');

            this.classList.add('active');
            const targetEl = document.querySelector('#' + target);
            if (targetEl) targetEl.style.display = 'block';
        });
    });

});
