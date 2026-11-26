'use strict';
const menu=document.querySelector('.nav-toggle');
menu?.addEventListener('click',()=>{const open=menu.getAttribute('aria-expanded')!=='true';menu.setAttribute('aria-expanded',String(open));menu.setAttribute('aria-label',open?'Cerrar menú':'Abrir menú');document.getElementById('navigation').classList.toggle('is-open',open);});
document.querySelectorAll('[data-copy]').forEach(button=>button.addEventListener('click',async()=>{try{await navigator.clipboard.writeText(button.dataset.copy);button.textContent='Código copiado';}catch{button.textContent='Selecciona y copia el código de arriba';}}));
const client=document.getElementById('cliente_id'),equipment=document.getElementById('equipo_id');
if(client&&equipment){
 const items=JSON.parse(document.getElementById('equipos-data').textContent);
 const showGroups=()=>{document.getElementById('nuevo-cliente').hidden=!!client.value;document.getElementById('nuevo-equipo').hidden=!!equipment.value;};
 const rebuild=(selected='')=>{equipment.replaceChildren(new Option('Registrar un equipo nuevo',''));items.filter(e=>String(e.cliente_id)===client.value).forEach(e=>equipment.add(new Option(e.nombre,e.id)));equipment.value=selected;showGroups();};
 client.addEventListener('change',()=>rebuild());equipment.addEventListener('change',showGroups);rebuild(equipment.dataset.initial||'');
}
const quote=document.getElementById('quote-form');
if(quote){
 const services=JSON.parse(document.getElementById('services-data').textContent),old=JSON.parse(document.getElementById('quote-old').textContent),container=document.getElementById('quote-items');let id=0;
 const cents=value=>{const [whole,part='']=String(value).split('.');return Number(whole)*100+Number(part.padEnd(2,'0').slice(0,2));};
 const recalc=()=>{let total=0;container.querySelectorAll('.quote-row').forEach(row=>{const service=services.find(s=>String(s.id)===row.querySelector('select').value),qty=Number(row.querySelector('input').value);const sum=service&&qty>0?cents(service.precio)*qty:0;total+=sum;row.querySelector('output').textContent='S/ '+(sum/100).toFixed(2);});document.getElementById('quote-total').textContent='S/ '+(total/100).toFixed(2);};
 const add=(data={})=>{if(container.children.length>=20)return;const row=document.createElement('div');row.className='quote-row';const select=document.createElement('select');select.name=`items[${id}][servicio_id]`;select.className='form-select';select.required=true;select.setAttribute('aria-label','Servicio');select.add(new Option('Selecciona un servicio',''));services.forEach(s=>select.add(new Option(`${s.nombre} · S/ ${s.precio}`,s.id)));select.value=String(data.servicio_id||'');const qty=document.createElement('input');qty.type='number';qty.name=`items[${id}][cantidad]`;qty.className='form-control';qty.min='1';qty.max='1000';qty.step='1';qty.required=true;qty.value=data.cantidad||1;qty.setAttribute('aria-label','Cantidad');const subtotal=document.createElement('output');subtotal.setAttribute('aria-label','Subtotal estimado');const remove=document.createElement('button');remove.type='button';remove.textContent='Quitar';remove.className='btn btn-outline-light btn-sm';remove.addEventListener('click',()=>{row.remove();recalc();});row.append(select,qty,subtotal,remove);container.append(row);id++;recalc();};
 document.getElementById('add-item').addEventListener('click',()=>add());container.addEventListener('change',recalc);container.addEventListener('input',recalc);if(old.length)old.forEach(add);else add();
}
document.addEventListener('DOMContentLoaded',()=>{
 const chartData=document.getElementById('states-data');
 if(chartData&&window.Chart){const data=JSON.parse(chartData.textContent);new Chart(document.getElementById('states-chart'),{type:'doughnut',data:{labels:data.labels,datasets:[{data:data.values,backgroundColor:['#91a3bc','#ffc75b','#2f80ff','#4dd3b0','#b39bff'],borderColor:'#101925',borderWidth:4}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}}}});}
});
