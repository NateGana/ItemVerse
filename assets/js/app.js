document.querySelectorAll('form[data-validate]').forEach(function(f){
f.addEventListener('submit',function(ev){
var bad=null;
f.querySelectorAll('[required]').forEach(function(x){if(!bad&&!x.value.trim())bad=x;});
var p=f.querySelector('input[name=password]');
if(!bad&&p&&f.dataset.validate==='register'&&p.value.length<8)bad=p;
if(bad){ev.preventDefault();bad.focus();bad.classList.add('err');}
else{var b=f.querySelector('button[type=submit]');if(b){b.disabled=true;b.textContent='Working…';}}
});
});
document.querySelectorAll('.flash').forEach(function(x){setTimeout(function(){x.remove();},6000);});
document.querySelectorAll('input.cmp').forEach(function(c){c.addEventListener('change',function(){
if(document.querySelectorAll('input.cmp:checked').length>3){c.checked=false;alert('You can compare up to three items.');}
});});
