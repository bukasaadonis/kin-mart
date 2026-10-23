document.addEventListener('DOMContentLoaded',()=>{
  document.querySelectorAll('[data-confirm]').forEach(el=>el.addEventListener('click',e=>{if(!confirm(el.dataset.confirm||'Confirmer cette action ?'))e.preventDefault()}));
  document.querySelectorAll('.auto-dismiss').forEach(el=>setTimeout(()=>{el.style.opacity='0';el.style.transform='translateY(-6px)';setTimeout(()=>el.remove(),220)},4200));
  document.querySelectorAll('[data-password-toggle]').forEach(p=>p.addEventListener('click',()=>{const t=document.querySelector(p.dataset.passwordToggle);if(t){t.type=t.type==='password'?'text':'password';p.textContent=t.type==='password'?'Afficher':'Masquer'}}));
  const sidebar=document.getElementById('kmSidebar'), backdrop=document.querySelector('[data-sidebar-backdrop]');
  const closeSidebar=()=>{sidebar?.classList.remove('open');backdrop?.classList.remove('show')};
  document.querySelectorAll('[data-sidebar-toggle]').forEach(b=>b.addEventListener('click',()=>{sidebar?.classList.toggle('open');backdrop?.classList.toggle('show')}));
  backdrop?.addEventListener('click',closeSidebar);
  document.querySelectorAll('.km-sidebar a').forEach(a=>a.addEventListener('click',()=>{if(window.innerWidth<992)closeSidebar()}));
  document.querySelectorAll('.shop-product,.category-card,.km-stat').forEach((el,i)=>{el.style.animationDelay=`${Math.min(i*25,250)}ms`;el.classList.add('km-reveal')});
});
