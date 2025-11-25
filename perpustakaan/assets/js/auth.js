document.addEventListener('DOMContentLoaded',function(){
  const toggle = document.getElementById('authToggle'); const panel = document.getElementById('authPanel');
  if(toggle){ toggle.addEventListener('click',e=>{ e.stopPropagation(); panel.classList.toggle('hidden'); panel.setAttribute('aria-hidden', panel.classList.contains('hidden')); });
    document.addEventListener('click',e=>{ if(!e.target.closest('.auth-inline')){ panel.classList.add('hidden'); panel.setAttribute('aria-hidden','true'); } });
    document.querySelectorAll('.tab').forEach(t=>t.addEventListener('click', ()=>{
      document.querySelector('.tab.active').classList.remove('active'); t.classList.add('active');
      document.querySelectorAll('.tab-content').forEach(tc=>tc.classList.add('hidden'));
      document.getElementById(t.dataset.tab).classList.remove('hidden');
    }));
  }
});