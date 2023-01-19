<script setup>
import { ref, computed, onMounted,nextTick } from 'vue';

const tickets = ref([
  {
    titulo: 'Tiitulo',
    estado: 'SOLUCIONADO', 
    tags : ['tag1','tag2','tag3'],
    autor: 'Autooooor',
    texto: 'Textoooooo',
  },
  {
    autor: 'Autooooor1',
    texto: [
      {
        tipo: 'parrafo',
        texto: {
          tipo: 'negrita',
          texto: 'negrita',
        }
      },
      {
        tipo: 'cursiva',
        texto: 'cursiva',
      },
      {
        tipo: 'subrayar',
        texto: 'subrayar',
      },
      {
        tipo: 'color',
        texto: 'color',
      },
      {
        tipo: 'color',
        color: 'red',
        texto: 'color',
      },
    ]
  },
  {
    autor: 'Autooooor2',
    texto: [{
      tipo: 'negrita',
      texto: 'negrita',
    },
    {
      tipo: 'negrita',
      texto: 'negrita',
    },
    {
      tipo: 'subrayar',
      texto: 'subrayar',
    },
    {
      tipo: 'cursiva',
      texto: 'cursiva',
    },
    {
      tipo: 'color',
      color: 'red',
      texto: 'color'
    }]
  },
  {
    autor: 'Autooooor3',
    texto: 'Textoooooo3',
  },
]);

const tickets_v = ref(JSON.parse(JSON.stringify(tickets.value)));

const dateCreacion = new Date(blade_vars.server_time*1000).toLocaleString();

function aplicarSeleccion(obj,tidx){
  const selecciones = window.getSelection();
  for(let rangeIdx=0;rangeIdx<selecciones.rangeCount;rangeIdx++){
    const seleccion  = selecciones.getRangeAt(rangeIdx);
    const nodoInicio = seleccion.startContainer.parentNode;
    const nodoFin    = seleccion.endContainer.parentNode;
    if(nodoInicio.closest('.cuerpo') == null || nodoFin.closest('.cuerpo')   == null
    || nodoInicio.closest(`#ticket${tidx}`) == null || nodoFin.closest(`#ticket${tidx}`) == null){
      return;
    }
    const obj_clone = obj.cloneNode(true);
    obj_clone.appendChild(seleccion.extractContents());
    seleccion.insertNode(obj_clone);
  }
}

const estilos = {
  parrafo: {
    elemento: () => document.createElement('div'),
    tags: () => ['<div>','</div>'],
  },
  negrita: {
    elemento: () => document.createElement('b'),
    tags: () => ['<b>','</b>'],
  },
  cursiva: {
    elemento: () => document.createElement('i'),
    tags: () => ['<i>','</i>'],
  },
  subrayar: {
    elemento: () => document.createElement('u'),
    tags: () => ['<u>','</u>'],
  },
  color: {
    elemento: function(tidx){
      const span = document.createElement('span');
      const color_v = tickets_v.value[tidx].color;
      span.style.color = color_v? color_v : '#000000';
      return span;
    },
    tags: function(texto){
      return ['<span style="color:'+(texto.color? texto.color : 'white')+';">','</span>'];
    },
  },
}
const nodeName_a_estilo = {
    DIV: {
      to_obj: (node) => ({
        tipo: 'parrafo',
        texto: to_json(node),
      }),
    },
    B: {
      to_obj: (node) => ({
        tipo: 'negrita',
        texto: to_json(node)
      }),
    },
    I: {
      to_obj: (node) => ({
        tipo: 'cursiva',
        texto: to_json(node)
      }),
    },
    U: {
      to_obj: (node) => ({
        tipo: 'subrayar',
        texto: to_json(node)
      }),
    },
    SPAN: {
      to_obj: (node) => ({
        tipo: 'color',
        color: node.style.color? node.style.color : 'white',
        texto: to_json(node)
      }),
      estilo: 'color',
    }
}

function aplicarEstilo(event,tidx,tipo){
  return aplicarSeleccion(estilos[tipo].elemento(tidx),tidx);
}

function to_html(texto){
  if(typeof texto == 'string') return texto.replaceAll('<','&lt;').replaceAll('>','&gt;');
  if(typeof texto != 'object') throw 'Type unsupported '+texto;
  if(Array.isArray(texto)){
    return texto.map(to_html).join('');
  }
  let open  = '';
  let close = '';
  if(texto.tipo in estilos){
    [open,close] = estilos[texto.tipo].tags(texto);
  }
  return open+to_html(texto.texto)+close;
}

function to_json_impl(parent){
  const json = [];
  for(const node of parent.childNodes){
    if(node.nodeName in nodeName_a_estilo){
      json.push(nodeName_a_estilo[node.nodeName].to_obj(node));
      continue;
    }
    
    if(node.nodeName == '#text'){
      if(node.textContent == '') continue;
      const texto = node.textContent.replaceAll('<','&lt;').replaceAll('>','&gt;')
      if(parent.childNodes.length == 1){
        return texto;
      }
      json.push(texto);
      continue;
    }
    
    const texto = node.outerHTML? node.outerHTML : node.textContent;
    json.push(texto.replaceAll('<','&lt;').replaceAll('>','&gt;'));
  }
  return json;
}

function to_json(cuerpo){
  if(cuerpo == null) return [];
  return to_json_impl(cuerpo);
}

function guardar(event,tidx){
  const ticket_v = tickets_v.value[tidx];
  const ticket   = tickets.value[tidx];
  ticket_v.editando = !ticket_v.editando;
  if(ticket_v.editando){
    return;
  }
  const cuerpo = ticket_v.cuerpo;
  const json = to_json(cuerpo);
  const html = to_html(json);
  ticket_v.texto      = json;
  cuerpo.innerHTML    = html;
  if(ticket_v.tags){
    ticket_v.tags = ticket_v.tags.filter((s) => s.length > 0);
  }
  copyObject(ticket_v,ticket,ticket);
}

function cancelar(event,tidx){
  const ticket_v = tickets_v.value[tidx];
  const ticket   = tickets.value[tidx];
  ticket_v.editando = false;
  copyObject(ticket,ticket_v,ticket);
  const html = to_html(ticket_v.texto);
  ticket_v.cuerpo.innerHTML = html;
}

function copyObject(from,to,keysfrom){
  for(const k of Object.keys(keysfrom)){
    if(Array.isArray(from[k])){//Stop using keysfrom and just deepclone
      to[k] = JSON.parse(JSON.stringify(from[k]));
    }
    else if(typeof from[k] == 'object'){
      if(typeof to[k] != 'object'){
        to[k] = {};
      }
      copyObject(from[k],to[k],keysfrom[k]);
    }
    else{//Stop using keysfrom and just value copy
      to[k] = from[k];
    }
  }
}

onMounted(function(){
  nextTick(function(){
    for(const idx in tickets_v.value){
      tickets_v.value[idx].cuerpo = document.querySelector(`#ticket${idx} .cuerpo`);
    }
  });
});
</script>

<template>
  <div id="listaTickets">
    <div :id="`ticket${tidx}`" class="ticket" v-for="(ticket_v,tidx) in tickets_v" :key="tidx">
      <div v-if="tidx==0">
        <div>
          <div style="float:left;width: 10%;text-align: center;">Número</div>
          <div style="width: 80%;">{{ ticket_v.numero ?? '-NUEVO-' }}</div>
        </div>
        <div>
          <div style="float:left;width: 10%;text-align: center;">Creado</div>
          <div style="width: 80%;">{{ ticket_v.created_at ?? dateCreacion }}</div>
        </div>
        <div>
          <div style="float:left;width: 10%;text-align: center;">Modif.</div>
          <div style="width: 80%;">{{ ticket_v.modified_at ?? '--'}}</div>
        </div>
        <div>
          <div style="float: left;width: 10%;text-align: center;">Titulo</div>
          <input style="width: 80%;" v-model="ticket_v.titulo" :disabled="!ticket_v.editando">
        </div>
        <div>
          <div style="float: left;width: 10%;text-align: center;">Estado</div>
          <select style="width: 80%;" v-model="ticket_v.estado" :disabled="!ticket_v.editando">
            <option></option>
            <option>ABIERTO</option>
            <option>SOLUCIONADO</option>
            <option>CERRADO</option>
          </select>
        </div>
      </div>
      <div>
        <div style="float: left;width: 10%;text-align: center;">Autor</div>
        <input style="width: 80%;" v-model="ticket_v.autor" :disabled="!ticket_v.editando">
      </div>
      <div v-if="tidx==0">
        <div style="float: left;width: 10%;text-align: center;">Tags</div>
        <div class="taglist">
          <div style="float: left;width: 5em;" v-for="(tag,tagidx) in ticket_v.tags" :key="tagidx" >
            <div class="tag" 
              :contenteditable="ticket_v.editando? true : null"
              @focusout="(event) => ticket_v.tags[tagidx] = event.target.textContent">{{ tag }}</div>
            <button 
              v-if="ticket_v.editando"
              style="font-size: 1em;width: 1em;background: rgba(0,0,0,0);margin: 0px;padding: 0px;border: 0px;text-shadow: 0px 0px 4px white;"
              @click="ticket_v.tags.splice(tagidx,1)"
            >x</button>
          </div>
          <div style="width: 5%;float: left;" v-if="ticket_v.editando">
            <button style="width: 100%;" @click="ticket_v.tags.push('')">+</button>
          </div>
        </div>
      </div>
      <hr style="width: 97%;">
      <div class="enriquecer">
        <button @click="aplicarEstilo($event,tidx,'negrita')" v-if="ticket_v.editando"><b>N</b></button>
        <button @click="aplicarEstilo($event,tidx,'cursiva')" v-if="ticket_v.editando"><i>Curs</i></button>
        <button @click="aplicarEstilo($event,tidx,'subrayar')" v-if="ticket_v.editando"><u>Sub</u></button>
        <button @click="aplicarEstilo($event,tidx,'color')" v-if="ticket_v.editando">
          <span>Color</span>
          <input class="colorpicker" type="color" v-model="ticket_v.color">
        </button>
      </div>
      <div class="cuerpo"
        :contenteditable="ticket_v.editando? true : null"
        v-html="to_html(ticket_v.texto)">
      </div>
      <div class="acciones">
        <button @click="guardar($event,tidx)">{{ ticket_v.editando? 'GUARDAR' : 'EDITAR'}}</button>
        <button v-if="ticket_v.editando" @click="cancelar($event,tidx)">CANCELAR</button>
        <button v-if="ticket_v.editando">ADJUNTAR</button>
        <button v-if="ticket_v.editando">ELIMINAR</button>
        <button v-if="!ticket_v.editando">HISTORIAL</button>
      </div>
    </div>
  </div>
</template>
