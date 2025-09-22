

document.querySelectorAll('.conversation-form-input').forEach(function(item) {
    item.addEventListener('input', function() {
        this.rows = this.value.split('\n').length
    })
})

document.querySelectorAll('[data-conversation]').forEach(function(item) {
    item.addEventListener('click', function(e) {
        e.preventDefault()
        document.querySelectorAll('.conversation').forEach(function(i) {
            i.classList.remove('active')
        })
        document.querySelector(this.dataset.conversation).classList.add('active')
    })
})

document.querySelectorAll('.conversation-back').forEach(function(item) {
    item.addEventListener('click', function(e) {
        e.preventDefault()
        this.closest('.conversation').classList.remove('active')
        document.querySelector('.conversation-default').classList.add('active')
    })
})
// end: Coversation

// Existing functionality
document.querySelectorAll('.conversation-form-input').forEach(function(item) {
    item.addEventListener('input', function() {
        this.rows = this.value.split('\n').length
    })
})

document.querySelectorAll('[data-conversation]').forEach(function(item) {
    item.addEventListener('click', function(e) {
        e.preventDefault()
        document.querySelectorAll('.conversation').forEach(function(i) {
            i.classList.remove('active')
        })
        document.querySelector(this.dataset.conversation).classList.add('active')
    })
})

document.querySelectorAll('.conversation-back').forEach(function(item) {
    item.addEventListener('click', function(e) {
        e.preventDefault()
        this.closest('.conversation').classList.remove('active')
        document.querySelector('.conversation-default').classList.add('active')
    })
})

// NEW: Chat sending functionality
function getCurrentTime() {
    const now = new Date();
    return now.toLocaleTimeString('en-US', { 
        hour: '2-digit', 
        minute: '2-digit',
        hour12: false 
    });
}

function createMessageHTML(text, isMe = true) {
    const currentTime = getCurrentTime();
    const messageClass = isMe ? 'conversation-item me' : 'conversation-item';
    const imageUrl = "https://images.unsplash.com/photo-1534528741775-53994a69daeb?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxzZWFyY2h8OXx8cGVvcGxlfGVufDB8fDB8fHww&auto=format&fit=crop&w=500&q=60";
    
    return `
        <li class="${messageClass}">
            <div class="conversation-item-side">
                <img class="conversation-item-image" src="${imageUrl}" alt="" />
            </div>
            <div class="conversation-item-content">
                <div class="conversation-item-wrapper">
                    <div class="conversation-item-box">
                        <div class="conversation-item-text">
                            <p>${text}</p>
                            <div class="conversation-item-time">${currentTime}</div>
                        </div>
                    </div>
                </div>
            </div>
        </li>
    `;
}

function sendMessage(conversationElement, messageText) {
    const conversationWrapper = conversationElement.querySelector('.conversation-wrapper');
    const messageHTML = createMessageHTML(messageText, false); // Changed to false for user messages
    
    // Add the new message
    conversationWrapper.insertAdjacentHTML('beforeend', messageHTML);
    
    // Scroll to bottom
    const conversationMain = conversationElement.querySelector('.conversation-main');
    conversationMain.scrollTop = conversationMain.scrollHeight;
    
    // Optional: Simulate a response after 1 second
    setTimeout(() => {
        const responseHTML = createMessageHTML("Thanks for your message! This is an automated response.", true); // Changed to true for bot responses
        conversationWrapper.insertAdjacentHTML('beforeend', responseHTML);
        conversationMain.scrollTop = conversationMain.scrollHeight;
    }, 1000);
}

// Add event listeners for send buttons and Enter key
document.querySelectorAll('.conversation-form-submit').forEach(function(submitBtn) {
    submitBtn.addEventListener('click', function(e) {
        e.preventDefault();
        
        const conversationElement = this.closest('.conversation');
        const textArea = conversationElement.querySelector('.conversation-form-input');
        const messageText = textArea.value.trim();
        
        if (messageText) {
            sendMessage(conversationElement, messageText);
            textArea.value = '';
            textArea.rows = 1;
        }
    });
});

// Add Enter key support (Shift+Enter for new line, Enter to send)
document.querySelectorAll('.conversation-form-input').forEach(function(textArea) {
    textArea.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            
            const conversationElement = this.closest('.conversation');
            const messageText = this.value.trim();
            
            if (messageText) {
                sendMessage(conversationElement, messageText);
                this.value = '';
                this.rows = 1;
            }
        }
    });
});


//search function
document.getElementById('chat-search-input').addEventListener('input', function () {
  const searchValue = this.value.toLowerCase();
  const listItems = document.querySelectorAll('.content-messages-list > li');

  listItems.forEach(item => {
    if (item.textContent.toLowerCase().includes(searchValue)) {
      item.style.display = '';
    } else {
      item.style.display = 'none';
    }
  });
});

document.querySelectorAll('[data-conversation]').forEach(function(item) {
    item.addEventListener('click', function(e) {
        e.preventDefault();
        // Remove 'active' from all conversations
        document.querySelectorAll('.conversation').forEach(function(i) {
            i.classList.remove('active')
        });
        // Add 'active' to the clicked conversation
        document.querySelector(this.dataset.conversation).classList.add('active');

        // Hide the unread notification badge inside the clicked chat item
        const unreadBadge = this.querySelector('.content-message-unread');
        if (unreadBadge) {
            unreadBadge.style.display = 'none'; // or unreadBadge.remove();
        }
    })
});

// On clicking a chat contact
document.querySelectorAll('[data-conversation]').forEach(function(item) {
    item.addEventListener('click', function(e) {
        e.preventDefault();

        // Hide unread badge on clicked contact
        const unreadBadge = this.querySelector('.content-message-unread');
        if (unreadBadge) {
            const count = parseInt(unreadBadge.textContent, 10);

            // Hide the badge visually
            unreadBadge.style.display = 'none';

            // Update stored total unread
            let totalUnread = parseInt(localStorage.getItem('chatTotalUnread')) || 0;
            totalUnread = Math.max(0, totalUnread - count);
            localStorage.setItem('chatTotalUnread', totalUnread);

            // Update the main chat icon badge display
            updateMainBadge(totalUnread);
        }

        // Show the clicked conversation (existing logic)
        document.querySelectorAll('.conversation').forEach(function(i) {
            i.classList.remove('active');
        });
        document.querySelector(this.dataset.conversation).classList.add('active');
    });
});


