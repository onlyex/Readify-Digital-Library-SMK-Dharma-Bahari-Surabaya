// Toast helper: show one or multiple toasts in the bottom-right
console.log('App JS loaded');
window.showToast = function(msg, opts){
	opts = opts || {};
	var container = document.getElementById('toast-container');
	if(!container){ container = document.createElement('div'); container.id='toast-container'; container.style.position='fixed'; container.style.right='16px'; container.style.bottom='20px'; container.style.zIndex='99999'; document.body.appendChild(container); }
	var t = document.createElement('div'); t.className='toast'; t.innerHTML = msg;
	container.appendChild(t);
	setTimeout(()=>{ t.style.opacity='1'; t.style.transform='translateY(0)'; },50);
	setTimeout(()=>{ t.style.opacity='0'; t.style.transform='translateY(8px)'; setTimeout(()=>t.remove(),420); }, opts.duration || 4200);
	t.addEventListener('click', ()=>{ t.remove(); });
}