document.addEventListener('DOMContentLoaded', function() {
    // Sidebar toggle functionality for both mobile and desktop
    const hamburger = document.getElementById('hamburger');
    const closeSidebar = document.getElementById('close-sidebar');
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.querySelector('.main-content');
    
    // State tracking for sidebar
    let sidebarActive = window.innerWidth >= 992; // Default open on desktop
    
    // Initial sidebar state based on screen size
    function setInitialSidebarState() {
        if (window.innerWidth >= 992) {
            // Desktop view - sidebar open
            sidebar.classList.add('active');
            mainContent.classList.add('sidebar-open');
            sidebarActive = true;
        } else {
            // Mobile view - sidebar closed
            sidebar.classList.remove('active');
            mainContent.classList.remove('sidebar-open');
            sidebarActive = false;
        }
    }
    
    // Call initially
    setInitialSidebarState();
    
    // Add resize listener for responsive sidebar
    window.addEventListener('resize', function() {
        setInitialSidebarState();
    });
    
    // Toggle sidebar on hamburger click
    if (hamburger) {
        hamburger.addEventListener('click', function() {
            if (sidebarActive) {
                sidebar.classList.remove('active');
                mainContent.classList.remove('sidebar-open');
            } else {
                sidebar.classList.add('active');
                mainContent.classList.add('sidebar-open');
            }
            sidebarActive = !sidebarActive;
        });
    }
    
    // Close sidebar on close button click
    if (closeSidebar) {
        closeSidebar.addEventListener('click', function() {
            sidebar.classList.remove('active');
            mainContent.classList.remove('sidebar-open');
            sidebarActive = false;
        });
    }
    
    // Close sidebar when clicking outside on mobile only
    document.addEventListener('click', function(e) {
        if (window.innerWidth < 992 && // Only on mobile
            sidebar && 
            sidebar.classList.contains('active') && 
            !sidebar.contains(e.target) && 
            e.target !== hamburger && 
            !hamburger.contains(e.target)) {
            sidebar.classList.remove('active');
            mainContent.classList.remove('sidebar-open');
            sidebarActive = false;
        }
    });

    // Smooth scrolling for navigation links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const targetId = this.getAttribute('href');
            if (targetId !== '#') {
                e.preventDefault();
                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    // Close sidebar on mobile if open
                    if (window.innerWidth < 992 && sidebar && sidebar.classList.contains('active')) {
                        sidebar.classList.remove('active');
                        mainContent.classList.remove('sidebar-open');
                        sidebarActive = false;
                    }
                    
                    window.scrollTo({
                        top: targetElement.offsetTop - 70,
                        behavior: 'smooth'
                    });
                }
            }
        });
    });
    
    // Active menu highlighting
    function setActiveMenu() {
        const sections = document.querySelectorAll('section[id]');
        let scrollY = window.pageYOffset;
        
        sections.forEach(current => {
            const sectionHeight = current.offsetHeight;
            const sectionTop = current.offsetTop - 100;
            const sectionId = current.getAttribute('id');
            
            if (scrollY > sectionTop && scrollY <= sectionTop + sectionHeight) {
                document.querySelector('.menu a[href*=' + sectionId + ']')?.classList.add('active');
            } else {
                document.querySelector('.menu a[href*=' + sectionId + ']')?.classList.remove('active');
            }
        });
    }
    
    window.addEventListener('scroll', setActiveMenu);
    
    // Review stars rating functionality
    const stars = document.querySelectorAll('.stars i');
    const ratingInput = document.getElementById('ratingInput');
    
    if (stars.length && ratingInput) {
        stars.forEach(star => {
            star.addEventListener('mouseover', function() {
                const rating = this.dataset.rating;
                highlightStars(rating);
            });
            
            star.addEventListener('mouseout', function() {
                const currentRating = ratingInput.value;
                highlightStars(currentRating);
            });
            
            star.addEventListener('click', function() {
                const rating = this.dataset.rating;
                ratingInput.value = rating;
                highlightStars(rating);
            });
        });
    }
    
    function highlightStars(count) {
        stars.forEach(star => {
            if (star.dataset.rating <= count) {
                star.classList.add('active');
            } else {
                star.classList.remove('active');
            }
        });
    }
    
    // Load reviews
    const reviewsSlider = document.getElementById('reviewsSlider');
    if (reviewsSlider) {
        loadReviews();
    }
    
    // Review form submission
    const reviewForm = document.getElementById('reviewForm');
    if (reviewForm) {
        reviewForm.addEventListener('submit', function(e) {
            e.preventDefault();
            submitReview();
        });
    }

    // Contact form submission
    const contactForm = document.querySelector('.contact-form form');
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            // Simulate form submission
            const formData = new FormData(this);
            const submitButton = this.querySelector('button[type="submit"]');
            
            // Disable button and show loading state
            submitButton.disabled = true;
            submitButton.innerHTML = 'Mengirim...';
            
            // Simulate API call delay
            setTimeout(() => {
                // Reset form
                this.reset();
                
                // Show success message
                const formMessage = document.createElement('div');
                formMessage.className = 'alert alert-success';
                formMessage.textContent = 'Terima kasih! Pesan Anda telah terkirim.';
                this.appendChild(formMessage);
                
                // Restore button state
                submitButton.disabled = false;
                submitButton.innerHTML = 'Kirim Pesan';
                
                // Remove message after 3 seconds
                setTimeout(() => {
                    formMessage.remove();
                }, 3000);
            }, 1000);
        });
    }
    
    // Newsletter form submission
    const newsletterForm = document.querySelector('.newsletter form');
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const emailInput = this.querySelector('input[type="email"]');
            const submitButton = this.querySelector('button[type="submit"]');
            
            // Disable button
            submitButton.disabled = true;
            
            // Simulate API call
            setTimeout(() => {
                // Reset form
                this.reset();
                
                // Show success message
                const formMessage = document.createElement('div');
                formMessage.className = 'newsletter-message';
                formMessage.textContent = 'Terima kasih telah berlangganan!';
                this.appendChild(formMessage);
                
                // Restore button state
                submitButton.disabled = false;
                
                // Remove message after 3 seconds
                setTimeout(() => {
                    formMessage.remove();
                }, 3000);
            }, 1000);
        });
    }
    
    // Profile tab functionality
    const profileTabLinks = document.querySelectorAll('.profile-menu a');
    if (profileTabLinks.length > 0) {
        profileTabLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Remove active class from all tabs
                profileTabLinks.forEach(item => item.classList.remove('active'));
                document.querySelectorAll('.profile-tab').forEach(tab => tab.classList.remove('active'));
                
                // Add active class to current tab
                this.classList.add('active');
                const tabId = this.getAttribute('data-tab');
                document.getElementById(tabId).classList.add('active');
            });
        });
    }
});

// Load reviews function
function loadReviews() {
    const reviewsSlider = document.getElementById('reviewsSlider');
    
    // Show loading state
    reviewsSlider.innerHTML = '<div class="loading">Memuat review...</div>';
    
    // Fetch reviews from API
    fetch('fetch_reviews.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Clear loading state
                reviewsSlider.innerHTML = '';
                
                if (data.reviews.length === 0) {
                    reviewsSlider.innerHTML = '<div class="no-reviews">Belum ada review.</div>';
                    return;
                }
                
                // Create review cards
                data.reviews.forEach(review => {
                    const reviewCard = createReviewCard(review);
                    reviewsSlider.appendChild(reviewCard);
                });
                
                // Initialize slider if multiple reviews
                if (data.reviews.length > 1) {
                    initReviewSlider();
                }
            } else {
                reviewsSlider.innerHTML = '<div class="error">Gagal memuat review.</div>';
            }
        })
        .catch(error => {
            console.error('Error loading reviews:', error);
            reviewsSlider.innerHTML = '<div class="error">Terjadi kesalahan saat memuat review.</div>';
        });
}

// Create review card element
function createReviewCard(review) {
    const card = document.createElement('div');
    card.className = 'review-card';
    
    // Create stars based on rating
    let starsHTML = '';
    for (let i = 1; i <= 5; i++) {
        if (i <= review.rating) {
            starsHTML += '<i class="fas fa-star"></i>';
        } else {
            starsHTML += '<i class="far fa-star"></i>';
        }
    }
    
    // Create avatar based on whether profile image exists
    let avatarHTML = '';
    if (review.profile_image) {
        avatarHTML = `<img src="${review.profile_image}" alt="${review.user_name}" class="user-avatar">`;
    } else {
        avatarHTML = `<i class="fas fa-user-circle"></i>`;
    }
    
    card.innerHTML = `
        <div class="review-header">
            <div class="avatar">
                ${avatarHTML}
            </div>
            <div class="review-info">
                <h4>${review.user_name}</h4>
                <div class="review-date">${review.created_at}</div>
            </div>
        </div>
        <div class="review-rating">${starsHTML}</div>
        <div class="review-text">${review.review_text}</div>
    `;
    
    return card;
}

// Initialize review slider
function initReviewSlider() {
    const reviewsSlider = document.getElementById('reviewsSlider');
    const reviewCards = reviewsSlider.querySelectorAll('.review-card');
    
    // Add navigation buttons
    const prevBtn = document.createElement('button');
    prevBtn.className = 'slider-nav prev-btn';
    prevBtn.innerHTML = '<i class="fas fa-chevron-left" style="display:none;"></i>';
    
    const nextBtn = document.createElement('button');
    nextBtn.className = 'slider-nav next-btn';
    nextBtn.innerHTML = '<i class="fas fa-chevron-right" style="display:none;"></i>';
    
    reviewsSlider.parentNode.appendChild(prevBtn);
    reviewsSlider.parentNode.appendChild(nextBtn);
    
    // Set initial slide
    let currentSlide = 0;
    const totalSlides = reviewCards.length;
    
    // Add slider-container class to reviewsSlider
    reviewsSlider.classList.add('slider-container');
    
    // Function to show slide
    function showSlide(index) {
        // Update current slide index
        currentSlide = index;
        
        // Update slide positions
        reviewCards.forEach((card, i) => {
            card.style.transform = `translateX(${100 * (i - currentSlide)}%)`;
        });
    }
    
    // Initialize slides
    reviewCards.forEach((card, i) => {
        card.style.transform = `translateX(${i}%)`;
    });
}

// Submit review function
function submitReview() {
    const rating = document.getElementById('ratingInput').value;
    const reviewText = document.getElementById('reviewInput').value;
    const reviewMessage = document.getElementById('reviewMessage');
    
    // Validate input
    if (!rating || rating < 1 || rating > 5) {
        reviewMessage.textContent = 'Silakan pilih rating.';
        reviewMessage.className = 'message error';
        return;
    }
    
    if (!reviewText.trim()) {
        reviewMessage.textContent = 'Review tidak boleh kosong.';
        reviewMessage.className = 'message error';
        return;
    }
    
    // Show loading state
    const submitButton = document.querySelector('#reviewForm button[type="submit"]');
    submitButton.disabled = true;
    submitButton.innerHTML = 'Mengirim...';
    reviewMessage.textContent = 'Mengirim review...';
    reviewMessage.className = 'message info';
    
    // Create form data
    const formData = new FormData();
    formData.append('rating', rating);
    formData.append('review', reviewText);
    
    // Submit review
    fetch('review_submit.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            reviewMessage.textContent = data.message;
            reviewMessage.className = 'message success';
            
            // Reset form
            document.getElementById('reviewInput').value = '';
            
            // Add new review to the slider
            const reviewsSlider = document.getElementById('reviewsSlider');
            const newReviewCard = createReviewCard(data.review);
            
            // Check if no-reviews message exists
            const noReviews = reviewsSlider.querySelector('.no-reviews');
            if (noReviews) {
                reviewsSlider.innerHTML = '';
            }
            
            // Add new review to the beginning
            if (reviewsSlider.firstChild) {
                reviewsSlider.insertBefore(newReviewCard, reviewsSlider.firstChild);
            } else {
                reviewsSlider.appendChild(newReviewCard);
            }
            
            // Reinitialize slider if needed
            if (reviewsSlider.querySelectorAll('.review-card').length > 1) {
                // Remove existing navigation buttons if any
                const existingNavs = document.querySelectorAll('.slider-nav');
                existingNavs.forEach(nav => nav.remove());
                
                initReviewSlider();
            }
        } else {
            // Show error message
            reviewMessage.textContent = data.message;
            reviewMessage.className = 'message error';
        }
    })
    .catch(error => {
        console.error('Error submitting review:', error);
        reviewMessage.textContent = 'Terjadi kesalahan. Silakan coba lagi.';
        reviewMessage.className = 'message error';
    })
    .finally(() => {
        // Restore button state
        submitButton.disabled = false;
        submitButton.innerHTML = 'Kirim Review';
        
        // Hide message after 3 seconds
        setTimeout(() => {
            reviewMessage.textContent = '';
            reviewMessage.className = 'message';
        }, 3000);
    });
}