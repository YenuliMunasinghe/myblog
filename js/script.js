// myblog/js/script.js

document.addEventListener('DOMContentLoaded', () => {
    console.log('Blog application JavaScript loaded.');

    const likeForm = document.getElementById('like-form');
    if (likeForm) {
        likeForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const formData = new FormData(likeForm);
            const likeBtn = document.getElementById('like-btn');
            const likeNumber = document.getElementById('like-number');
            const likeText = document.getElementById('like-text');

            try {
                const response = await fetch(likeForm.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    if (data.success) {
                        if (likeNumber) {
                            likeNumber.textContent = data.total_likes;
                        }
                        if (likeBtn) {
                            const icon = likeBtn.querySelector('i');
                            if (data.liked) {
                                likeBtn.classList.add('liked');
                                likeBtn.title = 'Unlike this post';
                                if (icon) icon.className = 'fas fa-heart';
                                if (likeText) likeText.textContent = 'Unlike';
                            } else {
                                likeBtn.classList.remove('liked');
                                likeBtn.title = 'Like this post';
                                if (icon) icon.className = 'far fa-heart';
                                if (likeText) likeText.textContent = 'Like';
                            }
                        }
                    } else if (data.message) {
                        alert(data.message);
                    }
                } else {
                    likeForm.submit();
                }
            } catch (err) {
                console.error('AJAX like submission error:', err);
                likeForm.submit();
            }
        });
    }
});