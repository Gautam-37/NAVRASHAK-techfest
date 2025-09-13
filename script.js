// Enhanced JavaScript with backend integration for NAVAKSHARA website

document.addEventListener('DOMContentLoaded', function() {
    initializeNavigation();
    initializeAnimations();
    initializeRegistrationForm();
    initializeParticles();
});

// Navigation functionality
function initializeNavigation() {
    const navToggle = document.getElementById('nav-toggle');
    const navMenu = document.getElementById('nav-menu');
    const navbar = document.getElementById('navbar');

    // Mobile menu toggle
    if (navToggle && navMenu) {
        navToggle.addEventListener('click', () => {
            navMenu.classList.toggle('active');
            navToggle.classList.toggle('active');
        });
    }
}

// Registration form functionality
function initializeRegistrationForm() {
    const teamSizeSelect = document.getElementById('team-size');
    const teamMembersContainer = document.getElementById('team-members-container');
    const registrationForm = document.getElementById('navakshara-registration-form');

    // Team members dynamic fields
    if (teamSizeSelect && teamMembersContainer) {
        teamSizeSelect.addEventListener('change', function() {
            const teamSize = parseInt(this.value);
            teamMembersContainer.innerHTML = '';

            if (teamSize > 1) {
                for (let i = 2; i <= teamSize; i++) {
                    const memberDiv = document.createElement('div');
                    memberDiv.className = 'form-group';
                    memberDiv.innerHTML = `
                        <label>Team Member ${i} Name</label>
                        <input type="text" name="member_${i}" placeholder="Enter team member ${i} name">
                    `;
                    teamMembersContainer.appendChild(memberDiv);
                }
            }
        });
    }

    // Form submission with backend integration
    if (registrationForm) {
        registrationForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const submitBtn = this.querySelector('.submit-btn');
            const originalText = submitBtn.textContent;
            
            try {
                // Show loading state
                submitBtn.textContent = 'Submitting...';
                submitBtn.disabled = true;
                
                // Get form data
                const formData = new FormData(this);
                const data = Object.fromEntries(formData.entries());
                
                // Client-side validation
                if (!validateForm(data)) {
                    throw new Error('Please fill in all required fields correctly.');
                }
                
                // Submit to backend
                const response = await fetch('backend/process_registration.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (!response.ok) {
                    throw new Error(result.error || 'Registration failed');
                }
                
                // Success - show success message
                this.style.display = 'none';
                document.getElementById('success-message').style.display = 'block';
                
                console.log('Registration successful:', result);
                
            } catch (error) {
                console.error('Registration error:', error);
                alert(error.message || 'An error occurred during registration. Please try again.');
                
                // Reset button state
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            }
        });
    }

    // Newsletter form
    const newsletterForm = document.getElementById('newsletter-form');
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const emailInput = this.querySelector('input[type="email"]');
            const email = emailInput.value.trim();
            
            if (email && isValidEmail(email)) {
                alert('Thank you for subscribing to our newsletter!');
                emailInput.value = '';
            } else {
                alert('Please enter a valid email address.');
            }
        });
    }
}

// Event selection functionality
function selectEvent(eventType) {
    // Remove previous selection
    document.querySelectorAll('.registration-card').forEach(card => {
        card.classList.remove('selected');
    });
    
    // Add selection to clicked card
    const selectedCard = document.querySelector(`[data-event="${eventType}"]`);
    if (selectedCard) {
        selectedCard.classList.add('selected');
    }
    
    // Show registration form
    const registrationForm = document.getElementById('registration-form');
    const selectedEventTitle = document.getElementById('selected-event-title');
    const selectedEventInput = document.getElementById('selected-event-input');
    
    if (registrationForm && selectedEventTitle && selectedEventInput) {
        registrationForm.style.display = 'block';
        selectedEventInput.value = eventType;
        
        const eventTitles = {
            'rc_plane': 'RC Plane Competition Registration',
            'drone_racing': 'Drone Racing Registration',
            'robot_war': 'Robot War Registration'
        };
        
        selectedEventTitle.textContent = eventTitles[eventType] || 'Event Registration';
        
        // Scroll to form
        setTimeout(() => {
            registrationForm.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }, 100);
    }
}

// Utility function to scroll to register section
function scrollToRegister() {
    const registerSection = document.getElementById('register');
    if (registerSection) {
        registerSection.scrollIntoView({ behavior: 'smooth' });
    }
}

// Form validation functions
function validateForm(data) {
    // Check required fields
    const requiredFields = ['name', 'email', 'phone', 'college', 'team_name', 'event'];
    for (let field of requiredFields) {
        if (!data[field] || data[field].trim() === '') {
            alert(`Please fill in the ${field.replace('_', ' ')} field.`);
            return false;
        }
    }
    
    // Email validation
    if (!isValidEmail(data.email)) {
        alert('Please enter a valid email address.');
        return false;
    }
    
    // Phone validation
    if (!isValidPhone(data.phone)) {
        alert('Please enter a valid phone number (10-15 digits).');
        return false;
    }
    
    // Team size validation
    const teamSize = parseInt(data.team_size);
    if (isNaN(teamSize) || teamSize < 1 || teamSize > 5) {
        alert('Please select a valid team size (1-5 members).');
        return false;
    }
    
    return true;
}

function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

function isValidPhone(phone) {
    const phoneRegex = /^[0-9+\-\s()]{10,15}$/;
    return phoneRegex.test(phone);
}

// Utility functions for better UX
function showLoading(element, message = 'Loading...') {
    if (element) {
        element.disabled = true;
        element.textContent = message;
    }
}

function hideLoading(element, originalText) {
    if (element) {
        element.disabled = false;
        element.textContent = originalText;
    }
}

// Export functions for global access
window.selectEvent = selectEvent;
window.scrollToRegister = scrollToRegister;

// Console welcome message
console.log(`
🚀 NAVAKSHARA TechFest Website
Where Innovation Takes Flight!

Event Registration System Active
- RC Plane Competition
- Drone Racing  
- Robot War

Built for CUTM Bhubaneswar Aeromodeling Club
`);

// Performance monitoring
window.addEventListener('load', function() {
    const loadTime = performance.now();
    console.log(`Page loaded in ${Math.round(loadTime)}ms`);
});

// Error handling
window.addEventListener('error', function(e) {
    console.error('JavaScript Error:', e.error);
    // In production, you might want to send this to an error tracking service
});

// Handle browser back/forward navigation
window.addEventListener('popstate', function(e) {
    // Handle any cleanup needed when user navigates back
    const navMenu = document.getElementById('nav-menu');
    const navToggle = document.getElementById('nav-toggle');
    
    if (navMenu && navMenu.classList.contains('active')) {
        navMenu.classList.remove('active');
        navToggle.classList.remove('active');
    }
});

    // Smooth scrolling for navigation links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;
            
            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                // Close mobile menu if open
                if (navMenu && navMenu.classList.contains('active')) {
                    navMenu.classList.remove('active');
                    navToggle.classList.remove('active');
                }
                
                window.scrollTo({
                    top: targetElement.offsetTop - 80,
                    behavior: 'smooth'
                });
            }
        });
    });

    // Navbar scroll effect
    if (navbar) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                navbar.style.padding = '10px 0';
                navbar.style.background = 'rgba(10, 20, 40, 0.95)';
            } else {
                navbar.style.padding = '15px 0';
                navbar.style.background = 'rgba(10, 20, 40, 0.8)';
            }
        });
    }
}

// Animation functionality
function initializeAnimations() {
    // Set initial state for animated elements
    const elements = document.querySelectorAll('.event-card, .about-image, .contact-info, .registration-card');
    elements.forEach(element => {
        element.style.opacity = '0';
        element.style.transform = 'translateY(20px)';
        element.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
    });

    // Animation on scroll
    function animateOnScroll() {
        elements.forEach(element => {
            const position = element.getBoundingClientRect();
            if (position.top < window.innerHeight - 100) {
                element.style.opacity = '1';
                element.style.transform = 'translateY(0)';
            }
        });
    }

    window.addEventListener('scroll', animateOnScroll);
    window.addEventListener('load', animateOnScroll);
}

// Particles.js initialization
function initializeParticles() {
    if (typeof particlesJS !== 'undefined' && document.getElementById('particles-js')) {
        particlesJS('particles-js', {
            particles: {
                number: { value: 80, density: { enable: true, value_area: 800 }},
                color: { value: ['#00d9ff', '#6c42f5', '#ffb800'] },
                shape: { type: 'circle' },
                opacity: { value: 0.5, random: true, anim: { enable: true, speed: 1, opacity_min: 0.1, sync: false }},
                size: { value: 3, random: true, anim: { enable: true, speed: 2, size_min: 0.1, sync: false }},
                line_linked: { enable: true, distance: 150, color: '#ffffff', opacity: 0.3, width: 1 },
                move: { enable: true, speed: 1.5, direction: 'none', random: true, straight: false, out_mode: 'out', bounce: false }
            },
            interactivity: {
                detect_on: 'canvas',
                events: { onhover: { enable: true, mode: 'grab' }, onclick: { enable: true, mode: 'push' }, resize: true },
                modes: { grab: { distance: 140, line_linked: { opacity: 1 }}, push: { particles_nb: 2 }}
            },
            retina_detect: true