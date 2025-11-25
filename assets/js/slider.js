document.addEventListener('DOMContentLoaded',function(){
  const slider = document.getElementById('smartSlider'); if(!slider) return;
  const slides = slider.querySelector('.slides'); const count = slides.children.length; let idx=0;
  function go(n){ idx=(n+count)%count; slides.style.transform='translateX('+(-idx*100)+'%)'; }
  document.getElementById('prev').addEventListener('click',()=>go(idx-1)); document.getElementById('next').addEventListener('click',()=>go(idx+1));
  setInterval(()=>go(idx+1),4200);
});