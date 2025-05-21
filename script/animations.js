// Apply animations to all clickable elements
document.addEventListener('DOMContentLoaded', function() {
    // Add the CSS file to the head
    const link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = 'style/animations.css';
    document.head.appendChild(link);
    
    // Apply the clickable-element class to all buttons
    const buttons = document.querySelectorAll('button');
    buttons.forEach(button => {
        button.classList.add('clickable-element');
    });
    
    // Apply to all <a> tags
    const links = document.querySelectorAll('a');
    links.forEach(link => {
        link.classList.add('clickable-element');
    });
    
    // Apply to all elements with onclick attributes
    const clickableElements = document.querySelectorAll('[onclick]');
    clickableElements.forEach(element => {
        if (!element.classList.contains('clickable-element')) {
            element.classList.add('clickable-element');
        }
    });
    
    // Apply to all input buttons and submits
    const inputButtons = document.querySelectorAll('input[type="button"], input[type="submit"]');
    inputButtons.forEach(input => {
        input.classList.add('clickable-element');
    });
    
    // Apply to all elements with role="button"
    const roleButtons = document.querySelectorAll('[role="button"]');
    roleButtons.forEach(element => {
        element.classList.add('clickable-element');
    });
    
    // Apply to card elements that are clickable (common pattern)
    const cards = document.querySelectorAll('.card, .card-hover');
    cards.forEach(card => {
        // Check if the card has a click handler or is wrapped in an <a> tag
        if (card.onclick || card.closest('a') || card.querySelector('a')) {
            card.classList.add('clickable-element', 'card');
        }
    });
    
    // Apply to dropdown toggles
    const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
    dropdownToggles.forEach(toggle => {
        toggle.classList.add('clickable-element', 'dropdown-toggle');
    });
    
    // Apply special animations to important buttons
    const donateButtons = document.querySelectorAll('button:contains("Donate Now")');
    donateButtons.forEach(button => {
        button.classList.add('pulse');
    });
    
    // Apply glow effect to sign in buttons
    const signInButtons = document.querySelectorAll('button:contains("Sign In")');
    signInButtons.forEach(button => {
        button.classList.add('glow');
    });
    
    // Apply bounce effect to announcement cards
    const announcementCards = document.querySelectorAll('#homepage-announcements .card');
    announcementCards.forEach(card => {
        card.classList.add('bounce');
    });
    
    // Apply shake effect to notification elements
    const notifications = document.querySelectorAll('.notification');
    notifications.forEach(notification => {
        notification.classList.add('shake');
    });
    
    // Observe DOM changes to apply animations to dynamically added elements
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.addedNodes.length) {
                mutation.addedNodes.forEach(function(node) {
                    if (node.nodeType === 1) { // Element node
                        // Apply to buttons
                        const newButtons = node.querySelectorAll ? node.querySelectorAll('button') : [];
                        newButtons.forEach(button => {
                            button.classList.add('clickable-element');
                        });
                        
                        // Apply to links
                        const newLinks = node.querySelectorAll ? node.querySelectorAll('a') : [];
                        newLinks.forEach(link => {
                            link.classList.add('clickable-element');
                        });
                        
                        // Apply to elements with onclick
                        const newClickable = node.querySelectorAll ? node.querySelectorAll('[onclick]') : [];
                        newClickable.forEach(element => {
                            if (!element.classList.contains('clickable-element')) {
                                element.classList.add('clickable-element');
                            }
                        });
                        
                        // Check if the node itself is clickable
                        if (node.onclick || node.tagName === 'BUTTON' || node.tagName === 'A' || 
                            (node.getAttribute && node.getAttribute('role') === 'button')) {
                            node.classList.add('clickable-element');
                        }
                    }
                });
            }
        });
    });
    
    // Start observing the document
    observer.observe(document.body, {
        childList: true,
        subtree: true
    });
    
    // Fix for :contains selector which isn't standard
    // This needs to run after the page is loaded
    setTimeout(function() {
        // Find donate buttons by text content
        document.querySelectorAll('button').forEach(button => {
            if (button.textContent.includes('Donate Now')) {
                button.classList.add('pulse', 'clickable-element');
            }
            if (button.textContent.includes('Sign In')) {
                button.classList.add('glow', 'clickable-element');
            }
        });
    }, 500);
}); 