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


const estilos = {
  parrafo: {
    elemento: () => document.createElement('div')
  },
  negrita: {
    elemento: () => document.createElement('b')
  },
  cursiva: {
    elemento: () => document.createElement('i')
  },
  subrayar: {
    elemento: () => document.createElement('u')
  },
  color: {
    elemento: function(tidx){
      const span = document.createElement('span');
      span.style.color = tickets_v.value[tidx].color;
      return span;
    }
  },
}

function aplicarEstilo(event,tidx,tipo){
  return aplicarSeleccion(estilos[tipo].elemento(tidx),tidx);
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

function to_json_impl(parent){
  const json = [];
  for(const node of parent.childNodes){
    switch(node.nodeName){
      case 'DIV':
        json.push({
          tipo: 'parrafo',
          texto: to_json(node)
        });
      case 'B':{
        json.push({
          tipo: 'negrita',
          texto: to_json(node)
        });
      }break;
      case 'I':{
        json.push({
          tipo: 'cursiva',
          texto: to_json(node)
        });
      }break;
      case 'U':{
        json.push({
          tipo: 'subrayar',
          texto: to_json(node)
        });
      }break;
      case 'SPAN':{
        json.push({
          tipo: 'color',
          color: node.style.color? node.style.color : 'white',
          texto: to_json(node)
        });
      }break;
      case '#text':{
        if(node.textContent == '') continue;
        if(parent.childNodes.length == 1){
          return node.textContent;
        }
        json.push(node.textContent);
      }break;
      default:{
        console.log(node.nodeName);
        json.push({//Escapar <>?
          texto: node.innerHTML? node.innerHTML : node.textContent
        })
      }break;
    }
  }
  return json;
}

function to_json(cuerpo){
  if(cuerpo == null) return [];
  return to_json_impl(cuerpo);
}

function guardar(event,tidx){
  const ticket_v = tickets_v.value[tidx];
  ticket_v.editando = !ticket_v.editando;
  if(ticket_v.editando){
    return;
  }
  const cuerpo = document.querySelector(`#ticket${tidx} .cuerpo`);
  tickets_v.value[tidx].texto_html = cuerpo? cuerpo.innerHTML : '';
  tickets.value[tidx].texto = to_json(cuerpo);
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
        <button @click="aplicarEstilo($event,tidx,'negrita')" v-if="tickets_v[tidx].editando"><b>N</b></button>
        <button @click="aplicarEstilo($event,tidx,'cursiva')" v-if="tickets_v[tidx].editando"><i>Curs</i></button>
        <button @click="aplicarEstilo($event,tidx,'subrayar')" v-if="tickets_v[tidx].editando"><u>Sub</u></button>
        <button @click="aplicarEstilo($event,tidx,'color')" v-if="tickets_v[tidx].editando">
          <span>Color</span>
          <input class="colorpicker" type="color" v-model="tickets_v[tidx].color">
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
