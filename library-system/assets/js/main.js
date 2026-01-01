// Mobile menu toggle
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
    const mobileMenu = document.querySelector('.mobile-menu');
    
    if(mobileMenuBtn && mobileMenu) {
        mobileMenuBtn.addEventListener('click', function() {
            mobileMenu.style.display = mobileMenu.style.display === 'block' ? 'none' : 'block';
        });
    }
    
    // Close mobile menu when clicking outside
    document.addEventListener('click', function(e) {
        if(!e.target.closest('.mobile-menu') && !e.target.closest('.mobile-menu-btn')) {
            mobileMenu.style.display = 'none';
        }
    });
    
    // Auto logout after 30 minutes of inactivity
    let inactivityTime = function() {
        let time;
        
        window.onload = resetTimer;
        document.onmousemove = resetTimer;
        document.onkeypress = resetTimer;
        
        function logout() {
            window.location.href = 'auth/logout.php';
        }
        
        function resetTimer() {
            clearTimeout(time);
            time = setTimeout(logout, 1800000); // 30 minutes
        }
    };
    
    inactivityTime();
    
    // Flash message auto hide
    const flashMessages = document.querySelectorAll('.alert');
    if(flashMessages.length > 0) {
        flashMessages.forEach(msg => {
            setTimeout(() => {
                msg.style.opacity = '0';
                setTimeout(() => {
                    msg.style.display = 'none';
                }, 500);
            }, 5000);
        });
    }
});