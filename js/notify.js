// Global Notification Utility
// Use: showNotification('message', 'success' | 'error' | 'info')
(function() {
  function getContainer() {
    let container = document.getElementById('toastContainer');
    if (!container) {
      container = document.createElement('div');
      container.id = 'toastContainer';
      container.className = 'toast-container';
      document.body.appendChild(container);
    }
    return container;
  }

  function iconFor(type) {
    if (type === 'success') return 'fa-check-circle';
    if (type === 'error') return 'fa-exclamation-circle';
    return 'fa-info-circle';
  }

  function showNotification(message, type = 'info', options = {}) {
    const duration = Number(options.duration || 4000);
    const container = getContainer();

    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.setAttribute('role', 'status');
    toast.setAttribute('aria-live', type === 'error' ? 'assertive' : 'polite');

    const content = document.createElement('div');
    content.className = 'toast-content';

    const icon = document.createElement('i');
    icon.className = `fas ${iconFor(type)}`;

    const text = document.createElement('span');
    text.textContent = message;

    const close = document.createElement('button');
    close.className = 'toast-close';
    close.setAttribute('aria-label', '閉じる');
    close.innerHTML = '<i class="fas fa-times"></i>';

    content.appendChild(icon);
    content.appendChild(text);
    toast.appendChild(content);
    toast.appendChild(close);
    container.appendChild(toast);

    // animate in
    requestAnimationFrame(() => {
      toast.classList.add('show');
    });

    let hideTimer = setTimeout(hide, duration);

    function hide() {
      toast.classList.add('hide');
      setTimeout(() => {
        if (container.contains(toast)) container.removeChild(toast);
      }, 250);
    }

    close.addEventListener('click', () => {
      clearTimeout(hideTimer);
      hide();
    });

    // Optional pause on hover
    toast.addEventListener('mouseenter', () => clearTimeout(hideTimer));
    toast.addEventListener('mouseleave', () => hideTimer = setTimeout(hide, 1200));
  }

  window.showNotification = showNotification;
})(); 