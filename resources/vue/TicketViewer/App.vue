<script setup>
import { ref, computed, onMounted,nextTick } from 'vue';
import Menu from '../Menu/App.vue'

const estados = ['ABIERTO','SOLUCIONADO','CERRADO'];

const tickets = ref(Array.from({length: 100}, () => Math.floor(Math.random() * 100)).map(function(v,idx){
  function rand(maxnum){
    const next_pow10 = 10**Math.ceil(Math.log10(maxnum));
    return Math.floor(Math.random()*next_pow10)%maxnum;
  }
  const inicial = idx == 0?
  {
    titulo: 'Titulo'+idx,
    estado: estados[rand(estados.length)],
    tags: Array.from({length: rand(4)},(v) => 'tag'+rand(4)),
    created_at: new Date(blade_vars.server_time*1000).toLocaleString(),
    modified_at: new Date(blade_vars.server_time*1000).toLocaleString(),
  } : {};
  return {
    numero: rand(10000),
    autor: 'Autor'+rand(23),
    texto: 'Textooooooooo'+rand(100),
    ...inicial,
    archivos: Array.from({length: rand(4)},(v) => (
      {
        nombre:'archivo'+rand(4)+'.jpg',
        url: 'archivos/ruta'+rand(4)+'.jpg',
      }
    ))
  };
}));

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
  if(ticket_v.editando && tidx == 0){
    ticket_v.tags.push('');
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

function adjuntar(event,ticket_v){
  if(ticket_v.editando){
    ticket_v.file_select.dispatchEvent(new MouseEvent('click'));
  }
}

function seleccionArchivos(event,ticket_v){
  for(const file of (event.target.files ?? [])){
    if(!ticket_v.archivos){
      ticket_v.archivos = [];
    }
    
    const reader = new FileReader();
    reader.onload = function(){
      const byteCharacters = reader.result;
      const byteNumbers = new Array(byteCharacters.length);
      for (let i = 0; i < byteCharacters.length; i++) {
        byteNumbers[i] = byteCharacters.charCodeAt(i);
      }
      const byteArray = new Uint8Array(byteNumbers);
      const blob = new Blob([byteArray], { type: file.type });
      
      ticket_v.archivos.push({
        nombre: file.name,
        url: window.URL.createObjectURL(blob),
        nuevo: true,
        file: file,
      });
    }
    reader.readAsBinaryString(file);
  }
}

function cambio_tag(event,ticket_v,tagidx){
  ticket_v.tags[tagidx] = event.target.textContent;
  console.log(tagidx);
  if(tagidx == (ticket_v.tags.length-1) && event.target.textContent != ''){
    ticket_v.tags.push('');
  }
}

onMounted(function(){
  nextTick(function(){
    for(const idx in tickets_v.value){
      tickets_v.value[idx].cuerpo = document.querySelector(`#ticket${idx} .cuerpo`);
      tickets_v.value[idx].file_select = document.querySelector(`#ticket${idx} .file_select`);
    }
  });
});
</script>

<template>
  <div id="main">
    <Menu/>
    <div id="contenido">
      <div id="tickets">
        <div :id="`ticket${tidx}`" class="ticket div_fondo" v-for="(ticket_v,tidx) in tickets_v" :key="tidx">
          <div v-if="tidx==0">
            <div class="cabecera_ticket">
              <div>Número</div>
              <div>{{ ticket_v.numero ?? '-NUEVO-' }}</div>
            </div>
            <div class="cabecera_ticket">
              <div>Creado</div>
              <div>{{ ticket_v.created_at ?? dateCreacion }}</div>
            </div>
            <div class="cabecera_ticket">
              <div>Modificado</div>
              <div>{{ ticket_v.modified_at ?? '--'}}</div>
            </div>
            <div class="cabecera_ticket">
              <div>Titulo</div>
              <div><input style="width: 100%;" v-model="ticket_v.titulo" :disabled="!ticket_v.editando"></div>
            </div>
            <div class="cabecera_ticket">
              <div>Estado</div>
              <div><select style="width: 100%;" v-model="ticket_v.estado" :disabled="!ticket_v.editando">
                <option></option>
                <option>ABIERTO</option>
                <option>SOLUCIONADO</option>
                <option>CERRADO</option>
              </select></div>
            </div>
          </div>
          <div class="cabecera_ticket">
            <div>Autor</div>
            <div><input style="width: 100%;" v-model="ticket_v.autor" :disabled="!ticket_v.editando"></div>
          </div>
          <div v-if="tidx==0" class="cabecera_ticket">
            <div>Tags</div>
            <div class="taglist">
              <div style="float: left;width: 7em;" v-for="(tag,tagidx) in ticket_v.tags" :key="tagidx" >
                <div class="tag contenteditable" 
                  :contenteditable="ticket_v.editando? true : null"
                  @focusout="cambio_tag($event,ticket_v,tagidx)">{{ tag }}</div>
                <button v-if="ticket_v.editando && tagidx < (ticket_v.tags.length-1)" class="cruz_borrar" @click="ticket_v.tags.splice(tagidx,1)">×</button>
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
          <div class="cuerpo contenteditable"
            :contenteditable="ticket_v.editando? true : null"
            v-html="to_html(ticket_v.texto)">
          </div>
          <div v-if="ticket_v.archivos">
            <div>Archivos</div>
            <div>
              <div class="archivo" v-for="(a,aidx) in ticket_v.archivos">
                <a :href="a.url" target="_blank" :title="a.title" :download="a.nombre">{{ a.nombre }}</a>
                <button v-if="ticket_v.editando" class="cruz_borrar" @click="ticket_v.archivos.splice(aidx,1)">×</button>
              </div>
            </div>
          </div>
          <div class="acciones">
            <button @click="guardar($event,tidx)">{{ ticket_v.editando? 'GUARDAR' : 'EDITAR'}}</button>
            <button v-if="ticket_v.editando" @click="cancelar($event,tidx)">CANCELAR</button>
            <button v-if="ticket_v.editando" @click="adjuntar($event,ticket_v)">ADJUNTAR</button>
            <input type="file" multiple  
              class="file_select" 
              style="position: absolute; top: -1000px; left: -1000px;visiblity: hidden;"
              @change="seleccionArchivos($event,ticket_v)">
            <button v-if="ticket_v.editando">ELIMINAR</button>
            <button v-if="!ticket_v.editando">HISTORIAL</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
