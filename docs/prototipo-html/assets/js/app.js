'use strict';

// Solo presentación. Los ejemplos no consultan ni actualizan una base de datos.
const icons = {
  laptop: '<rect x="4" y="4" width="16" height="12" rx="2"/><path d="M2 20h20l-2-4H4zM10 18h4"/>',
  wrench: '<path d="M14 6a5 5 0 0 0-6 6L3 17a2.8 2.8 0 0 0 4 4l5-5a5 5 0 0 0 6-6l-4 3-3-3z"/>',
  download: '<path d="M12 3v12m-4-4 4 4 4-4M4 16v5h16v-5"/>',
  wifi: '<path d="M2 8a16 16 0 0 1 20 0M5 12a11 11 0 0 1 14 0M8 16a6 6 0 0 1 8 0"/><circle cx="12" cy="20" r="1"/>',
  shield: '<path d="m12 2 8 4v6c0 5-8 10-8 10S4 17 4 12V6z"/><path d="m8 12 3 3 5-6"/>',
  headset: '<path d="M4 14v-3a8 8 0 0 1 16 0v6a4 4 0 0 1-4 4h-3"/><rect x="3" y="10" width="4" height="8" rx="2"/><rect x="17" y="10" width="4" height="8" rx="2"/>',
  bolt: '<path d="m13 2-9 12h7l-1 8 10-13h-8z"/>',
  people: '<circle cx="9" cy="7" r="3"/><path d="M3 21v-3a6 6 0 0 1 12 0v3M17 4a3 3 0 0 1 0 6m2 5a5 5 0 0 1 2 4v2"/>',
  search: '<circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 5 5"/>',
  package: '<path d="m12 2 9 5v10l-9 5-9-5V7zm0 0v10m9-5-9 5-9-5m9 5v10M7.5 4.5l9 5V14"/>',
  receipt: '<path d="M5 3h14v19l-3-2-4 2-4-2-3 2zM8 8h8M8 12h8M8 16h5"/>',
  info: '<circle cx="12" cy="12" r="9"/><path d="M12 11v6M12 7v.1"/>',
  menu: '<path d="M4 6h16M4 12h16M4 18h16"/>'
};
const svg = name => `<svg class="icon" viewBox="0 0 24 24" aria-hidden="true">${icons[name] || icons.info}</svg>`;
const logo = '<svg viewBox="0 0 32 36" aria-hidden="true"><path fill="currentColor" d="M3 3 29 11 17 16 13 32z"/><path fill="#72b6ff" d="m17 16 12-5-8 12z"/></svg><span class="brand-copy"><strong>AKS</strong><small>Soluciones</small></span>';
const page = document.documentElement.dataset.page;
const nav = [['inicio','Inicio','index.html'],['servicios','Servicios','servicios.html'],['seguimiento','Seguimiento','seguimiento.html']];
document.getElementById('site-header').innerHTML = `<div class="container nav-wrap"><a class="brand" href="index.html" aria-label="AKS Soluciones, inicio">${logo}</a><button class="nav-toggle" aria-controls="navigation" aria-expanded="false" aria-label="Abrir menú">${svg('menu')}</button><nav class="nav-links" id="navigation" aria-label="Navegación principal">${nav.map(([id,label,url])=>`<a href="${url}" ${page===id?'aria-current="page"':''}>${label}</a>`).join('')}<a href="index.html#nosotros">Nosotros</a><a href="#contacto">Contacto</a></nav><button class="button button-primary header-cta" data-contact>Solicitar servicio</button></div>`;
document.getElementById('site-footer').innerHTML = `<div class="container footer-wrap" id="contacto"><a class="brand" href="index.html" aria-label="AKS Soluciones, inicio">${logo}</a><div class="footer-contact"><address>Calle General Suárez 1036, Surquillo, Lima</address><p>AKS Soluciones · Prototipo frontend · Datos de ejemplo</p></div></div>`;
document.querySelector('.process-section')?.setAttribute('id','nosotros');
document.querySelector('.nav-toggle').addEventListener('click', e => {
  const button=e.currentTarget, open=button.getAttribute('aria-expanded')!=='true';
  button.setAttribute('aria-expanded',String(open));button.setAttribute('aria-label',open?'Cerrar menú':'Abrir menú');
  document.getElementById('navigation').classList.toggle('is-open',open);
});
document.querySelectorAll('.nav-links a').forEach(a=>a.addEventListener('click',()=>{
  document.querySelector('.nav-toggle').setAttribute('aria-expanded','false');document.querySelector('.nav-toggle').setAttribute('aria-label','Abrir menú');
  document.getElementById('navigation').classList.remove('is-open');
}));

const services = [
  {name:'Mantenimiento de equipos',icon:'laptop',price:60,description:'Limpieza, optimización y puesta a punto para que tu equipo rinda como el primer día.'},
  {name:'Reparación de hardware',icon:'wrench',price:80,description:'Diagnóstico y reemplazo de componentes: fuentes, discos, pantallas y más.'},
  {name:'Instalación de software',icon:'download',price:40,description:'Sistemas operativos, programas y configuraciones listas para trabajar.'},
  {name:'Redes y conectividad',icon:'wifi',price:90,description:'Configuración de redes, Wi-Fi y cableado para hogares y negocios.'},
  {name:'Seguridad de datos',icon:'shield',price:100,description:'Respaldo, recuperación y protección de la información de tu equipo.'},
  {name:'Soporte técnico',icon:'headset',price:50,description:'Atención técnica presencial y orientación según lo que tu equipo necesita.'}
];
document.querySelectorAll('[data-service-grid]').forEach(grid=>{
  const catalog=grid.dataset.serviceGrid==='catalog';
  grid.innerHTML=services.map((s,i)=>`<div class="col-md-6 col-lg-4"><article class="service-card"><span class="icon-box">${svg(s.icon)}</span><h3>${s.name}</h3><p>${s.description}</p>${catalog?`<div class="service-bottom"><span class="price"><small>Precio referencial</small>S/ ${s.price.toFixed(2)}</span><button class="service-select" data-service="${i}" aria-label="Consultar ${s.name.toLowerCase()}">Consultar</button></div>`:''}</article></div>`).join('');
});
document.querySelectorAll('[data-icon]').forEach(el=>el.innerHTML=svg(el.dataset.icon));

const dialog=document.createElement('dialog');dialog.className='contact-dialog';dialog.setAttribute('aria-labelledby','contact-title');
dialog.innerHTML=`<button class="dialog-close" aria-label="Cerrar información de atención">×</button><span class="eyebrow">AKS SOLUCIONES</span><h2 id="contact-title">Conversemos sobre tu equipo</h2><p id="selected-service">Acércate a la tienda para consultar el diagnóstico y la atención de tu equipo.</p><h3>Visítanos</h3><address>Calle General Suárez 1036<br>Surquillo, Lima</address><p class="dialog-note">Este avance muestra la interfaz. Las solicitudes aún no se envían ni se guardan.</p><button class="button button-primary dialog-done">Entendido</button>`;
document.body.append(dialog);
document.addEventListener('click',e=>{
  const contact=e.target.closest('[data-contact], [data-service]');if(!contact)return;
  const index=contact.dataset.service;
  document.getElementById('selected-service').textContent=index!==undefined?`Servicio de interés: ${services[Number(index)].name}. Consulta en la tienda el diagnóstico y la cotización de tu equipo.`:'Acércate a la tienda para consultar el diagnóstico y la atención de tu equipo.';
  dialog.showModal();
});
dialog.querySelectorAll('.dialog-close,.dialog-done').forEach(b=>b.addEventListener('click',()=>dialog.close()));
dialog.addEventListener('click',e=>{if(e.target===dialog){const rect=dialog.getBoundingClientRect();if(e.clientX<rect.left||e.clientX>rect.right||e.clientY<rect.top||e.clientY>rect.bottom)dialog.close();}});

// RF5 / CU07: simulación visual de consulta pública. No es una conexión al servidor.
const statuses=['Recibido','En diagnóstico','En reparación','Listo para entrega','Entregado'];
const demoOrders = Object.freeze({
  'AKS-DEMO-0001':{number:'0001',device:'Laptop · Lenovo IdeaPad 3',date:'18/11/2026',state:2,history:[['Recibido','18/11/2026 · 09:30','Equipo registrado en recepción.'],['En diagnóstico','18/11/2026 · 14:15','Revisión técnica de la falla reportada.'],['En reparación','19/11/2026 · 10:00','Servicio técnico en proceso.']]},
  'AKS-DEMO-0002':{number:'0002',device:'PC de escritorio · Equipo de ejemplo',date:'17/11/2026',state:3,history:[['Recibido','17/11/2026 · 10:00','Equipo registrado en recepción.'],['En diagnóstico','17/11/2026 · 15:00','Revisión técnica de la falla reportada.'],['En reparación','18/11/2026 · 11:00','Atención del equipo.'],['Listo para entrega','19/11/2026 · 16:30','Servicio concluido. Equipo disponible para recoger.']]}
});
const trackingForm=document.getElementById('tracking-form');
if(trackingForm){
  const input=document.getElementById('tracking-code'),message=document.getElementById('tracking-message'),result=document.getElementById('tracking-result');
  const initial=result.innerHTML;
  document.getElementById('load-demo').addEventListener('click',()=>{input.value='AKS-DEMO-0001';trackingForm.requestSubmit();});
  input.addEventListener('input',()=>{input.removeAttribute('aria-invalid');message.textContent='';message.className='form-message';});
  trackingForm.addEventListener('submit',e=>{
    e.preventDefault();const code=input.value.trim().toUpperCase();
    result.innerHTML=initial;
    if(!code){message.textContent='Ingresa tu código de seguimiento para continuar.';message.className='form-message error';input.setAttribute('aria-invalid','true');input.focus();return;}
    if(!/^[A-Z0-9-]{4,64}$/.test(code)){message.textContent='Revisa el código: usa únicamente letras, números y guiones.';message.className='form-message error';input.setAttribute('aria-invalid','true');input.focus();return;}
    const order=Object.hasOwn(demoOrders,code)?demoOrders[code]:null;
    if(!order){message.textContent='No encontramos ese código en los datos de ejemplo. Revisa el código o utiliza el pedido de prueba.';message.className='form-message error';return;}
    input.removeAttribute('aria-invalid');message.textContent='Pedido de ejemplo encontrado. Esta consulta es una simulación.';message.className='form-message success';
    result.innerHTML=`<article class="order-card"><div class="order-heading"><h2>Pedido #${order.number}</h2><span class="badge">${statuses[order.state]}</span><span class="demo-label">Datos de ejemplo</span></div><ol class="status-timeline" aria-label="Estado del pedido">${statuses.map((s,i)=>`<li class="${i<order.state?'done':i===order.state?'current':''}" ${i===order.state?'aria-current="step"':''}>${s}</li>`).join('')}</ol><dl class="order-meta"><div><dt>Equipo</dt><dd>${order.device}</dd></div><div><dt>Fecha de ingreso</dt><dd>${order.date}</dd></div></dl></article><article class="history-card"><h2>Historial del pedido</h2><ol class="history">${order.history.map(([state,date,description])=>`<li><strong>${state}</strong><span class="history-date">${date}</span><p>${description}</p></li>`).join('')}</ol></article>`;
  });
}
