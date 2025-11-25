document.addEventListener('DOMContentLoaded', function(){
  // preview for cover upload inputs
  document.querySelectorAll('input[type=file][name=cover]').forEach(function(inp){
    inp.addEventListener('change', function(e){
      var file = this.files && this.files[0];
      if(!file) return;
      var reader = new FileReader();
      reader.onload = function(ev){
        var img = document.getElementById('coverPreview');
        if(!img){
          img = document.createElement('img'); img.id='coverPreview'; img.style.maxWidth='160px'; img.style.borderRadius='8px'; img.style.display='block'; img.style.marginTop='8px';
          inp.parentNode.appendChild(img);
        }
        img.src = ev.target.result;
      };
      reader.readAsDataURL(file);
    });
  });
});
