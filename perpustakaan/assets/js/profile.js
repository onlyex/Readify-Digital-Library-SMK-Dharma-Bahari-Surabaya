document.addEventListener('DOMContentLoaded',function(){
  const file = document.querySelector('input[type=file][name=avatar]');
  if(!file) return;
  file.addEventListener('change', function(){
    const f = this.files[0];
    if(!f) return;
    const pr = document.createElement('img'); pr.className='nav-avatar';
    const reader = new FileReader();
    reader.onload = function(e){ pr.src=e.target.result; }
    reader.readAsDataURL(f);
    // show preview next to upload button
    const parent = this.closest('form'); if(!parent) return; 
    let prev = parent.querySelector('.avatar-preview');
    if(!prev){ prev = document.createElement('div'); prev.className='avatar-preview'; prev.style.marginLeft='8px'; parent.insertBefore(prev, parent.querySelector('button')); }
    prev.innerHTML=''; prev.appendChild(pr);
  });
});
