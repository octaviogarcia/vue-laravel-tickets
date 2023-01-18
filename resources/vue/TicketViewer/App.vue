<script setup>
import { ref, computed, onMounted } from 'vue';

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
    }]
  },
  {
    autor: 'Autooooor3',
    texto: 'Textoooooo3',
  },
]);

const tickets_v = ref(tickets.value.map(function(){return {};}));

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
function negrita(event,tidx){  
  aplicarSeleccion(document.createElement('b'),tidx);
}
function cursiva(event,tidx){
  aplicarSeleccion(document.createElement('i'),tidx);
}
function subrayar(event,tidx){
  aplicarSeleccion(document.createElement('u'),tidx);
}
function color(event,tidx){
  const span = document.createElement('span');
  span.style.color = tickets.value[tidx].color;
  aplicarSeleccion(span,tidx);
}

function to_html(texto){
  if(typeof texto == 'string') return texto;
  if(typeof texto != 'object') throw 'Type unsupported '+texto;
  if(Array.isArray(texto)){
    return texto.map(to_html).join('');
  }
  let open  = '';
  let close = '';
  switch(texto.tipo){
    case 'parrafo':{
      open  = '<div>';
      close = '</div>';
    }break;
    case 'negrita':{
      open  = '<b>';
      close = '</b>';
    }break;
    case 'cursiva':{
      open  = '<i>'
      close = '</i>';
    }break;
    case 'subrayar':{
      open  = '<u>';
      close = '</u>';
    }break;
    case 'color':{
      open  = '<span style="color:'+(texto.color ?? 'white')+';">';
      close = '</span>';
    }break;
    default:{
    }break;
  }
  return open+to_html(texto.texto)+close;
}

function to_json(html){
  let idx = 0;
  const json = [];
  while(true){
    {
      const old_idx = idx;
      idx = html.slice(idx).search(/(<div>|<b>|<i>|<u>|<span)/g);
      if(idx == -1){
        if(json.length == 0) return html.slice(old_idx);
        else if(html.slice(old_idx).length > 0){
          json.push({
            texto: html.slice(old_idx)
          });
          return json;
        }
        else return json;
      }
      idx = old_idx + idx;
    }
    const ss = html.slice(idx);
    if(ss.startsWith('<div>')){
      const begin = '<div>'.length;
      const end   = ss.search('</div>');
      if(end != -1){        
        idx += end + '</div>'.length;
      }
      const childss = ss.substring(begin,end == -1? undefined : end);
      json.push({
        tipo: 'parrafo',
        texto: to_json(childss),
      });
      if(end == -1){
        return json;
      }
    }
    else if(ss.startsWith('<b>')){
      const begin = '<b>'.length;
      const end   = ss.search('</b>');
      if(end != -1){
        idx += end + '</b>'.length;
      }
      const childss = ss.substring(begin,end == -1? undefined : end);
      json.push({
        tipo: 'negrita',
        texto: to_json(childss),
      });
      if(end == -1){
        return json;
      }
    }
    else if(ss.startsWith('<i>')){
      const begin = '<i>'.length;
      const end   = ss.search('</i>');
      if(end != -1){
        idx += end + '</i>'.length;
      }
      const childss = ss.substring(begin,end == -1? undefined : end);
      json.push({
        tipo: 'cursiva',
        texto: to_json(childss),
      });
      if(end == -1){
        return json;
      }
    }
    else if(ss.startsWith('<u>')){
      const begin = '<u>'.length;
      const end   = ss.search('</u>');
      if(end != -1){
        idx += end + '</u>'.length;
      }
      const childss = ss.substring(begin,end == -1? undefined : end);
      json.push({
        tipo: 'subrayar',
        texto: to_json(childss),
      });
      if(end == -1){
        return json;
      }
    }
    else if(ss.startsWith('<span')){
      const color_begin = ss.search('style="color:')+'style="color:'.length;
      const color_end = ss.search(';">');
      const color = color_end != -1? ss.substring(color_begin,color_end) : 'white';
      const begin = color_end+';">'.length;
      const end   = ss.search('</span>');
      if(end != -1){
        idx += end + '</span>'.length;
      }
      const childss = ss.substring(begin,end == -1? undefined : end);
      json.push({
        tipo: 'color',
        color: color,
        texto: to_json(childss),
      });
      if(end == -1){
        return json;
      }
    }
    else{
      console.log('Unsupported tag');
      json.push({
        texto: ss,
      });
      return json;
    }
  }
  return json;
}

function guardar(event,tidx){
  const ticket_v = tickets_v.value[tidx];
  ticket_v.editando = !ticket_v.editando;
  if(ticket_v.editando){
    return;
  }
  const html = document.querySelector(`#ticket${tidx} .cuerpo`).innerHTML;
  tickets_v.value[tidx].texto_html = html;
  //Si guardo, cambio la representacion json
  tickets.value[tidx].texto = to_json(html);
}

function cancelar(event,tidx){
  const ticket_v = tickets_v.value[tidx];
  document.querySelector(`#ticket${tidx} .cuerpo`).innerHTML = ticket_v.texto_html;
  ticket_v.editando = false;
}

onMounted(function(){
  for(const tidx in tickets.value){
    tickets_v.value[tidx].texto_html = to_html(tickets.value[tidx].texto);    
  }
});

</script>

<template>
  <div id="listaTickets">
    <div :id="`ticket${tidx}`" class="ticket" v-for="(ticket,tidx) in tickets" :key="tidx">
      <div v-if="tidx==0">
        <div>
          <div style="float:left;width: 10%;text-align: center;">Número</div>
          <div style="width: 80%;">{{ ticket.numero ?? '-NUEVO-' }}</div>
        </div>
        <div>
          <div style="float:left;width: 10%;text-align: center;">Creado</div>
          <div style="width: 80%;">{{ ticket.created_at ?? dateCreacion }}</div>
        </div>
        <div>
          <div style="float:left;width: 10%;text-align: center;">Modif.</div>
          <div style="width: 80%;">{{ ticket.modified_at ?? '--'}}</div>
        </div>
        <div>
          <div style="float: left;width: 10%;text-align: center;">Titulo</div>
          <input style="width: 80%;" v-model="ticket.titulo" :disabled="!tickets_v[tidx].editando">
        </div>
        <div>
          <div style="float: left;width: 10%;text-align: center;">Estado</div>
          <select style="width: 80%;" v-model="ticket.estado" :disabled="!tickets_v[tidx].editando">
            <option></option>
            <option>ABIERTO</option>
            <option>SOLUCIONADO</option>
            <option>CERRADO</option>
          </select>
        </div>
      </div>
      <div>
        <div style="float: left;width: 10%;text-align: center;">Autor</div>
        <input style="width: 80%;" v-model="ticket.autor" :disabled="!tickets_v[tidx].editando">
      </div>
      <div v-if="tidx==0">
        <div style="float: left;width: 10%;text-align: center;">Tags</div>
        <div class="taglist">
          <div v-for="(tag,tagidx) in ticket.tags" :key="tagidx" 
            :contenteditable="tickets_v[tidx].editando? true : null"
            @input="(event) => ticket.tags[tagidx] = event.target.textContent">
            {{ tag }}
          </div>
        </div>
      </div>
      <hr style="width: 97%;">
      <div class="enriquecer">
        <button @click="negrita($event,tidx)" v-if="tickets_v[tidx].editando"><b>N</b></button>
        <button @click="cursiva($event,tidx)" v-if="tickets_v[tidx].editando"><i>Curs</i></button>
        <button @click="subrayar($event,tidx)" v-if="tickets_v[tidx].editando"><u>Sub</u></button>
        <button @click="color($event,tidx)" v-if="tickets_v[tidx].editando">
          <span>Color</span>
          <input class="colorpicker" type="color" v-model="ticket.color">
        </button>
      </div>
      <div class="cuerpo"
        :contenteditable="tickets_v[tidx].editando? true : null"
        v-html="tickets_v[tidx].texto_html">
      </div>
      <div class="acciones">
        <button @click="guardar($event,tidx)">{{ tickets_v[tidx].editando? 'GUARDAR' : 'EDITAR'}}</button>
        <button v-if="tickets_v[tidx].editando" @click="cancelar($event,tidx)">CANCELAR</button>
        <button v-if="tickets_v[tidx].editando">ADJUNTAR</button>
        <button v-if="tickets_v[tidx].editando">ELIMINAR</button>
        <button v-if="!tickets_v[tidx].editando">HISTORIAL</button>
      </div>
    </div>
  </div>
</template>
