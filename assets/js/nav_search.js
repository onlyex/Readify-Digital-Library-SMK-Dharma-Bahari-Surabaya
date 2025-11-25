document.addEventListener('DOMContentLoaded', function() {
  var btn = document.getElementById('navSearchToggle');
  var panel = document.getElementById('navSearchPanel');
  if (!btn || !panel) {
    console.log('Search button or panel not found');
    return;
  }

  btn.addEventListener('click', function(e) {
    e.preventDefault();
    e.stopPropagation();
    panel.style.display = 'block';
    panel.setAttribute('aria-hidden', 'false');
    var input = panel.querySelector('input[name=q]');
    if (input) input.focus();
    console.log('Search icon clicked, panel shown');
  });

  // Tutup panel jika klik di luar panel
  document.addEventListener('click', function(e) {
    if (panel.style.display === 'block' && !panel.contains(e.target) && e.target !== btn) {
      panel.style.display = 'none';
      panel.setAttribute('aria-hidden', 'true');
      console.log('Panel closed by outside click');
    }
  });

  // Tutup panel dengan tombol Escape
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && panel.style.display === 'block') {
      panel.style.display = 'none';
      panel.setAttribute('aria-hidden', 'true');
      console.log('Panel closed by Escape');
    }
  });

  // Tombol tutup di panel
  var closeBtn = panel.querySelector('button[type="button"]');
  if (closeBtn) {
    closeBtn.addEventListener('click', function() {
      panel.style.display = 'none';
      panel.setAttribute('aria-hidden', 'true');
      console.log('Panel closed by close button');
    });
  }
});
