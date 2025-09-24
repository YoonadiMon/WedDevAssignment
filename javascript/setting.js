
// Make entire settings list items clickable
document.querySelectorAll('.settings-list li[data-href]').forEach(function(item) {
    item.addEventListener('click', function() {
        const href = this.getAttribute('data-href');
        if (href) {
            window.location.href = href;
        }
    });
});

