<script setup>
import { ref, computed, watch, onMounted,nextTick } from 'vue';
import WithMenu from '../components/WithMenu/App.vue';
import Buscador from '../components/Buscador/App.vue';
import Modal from '../components/Modal/App.vue';
import Popover from '../components/Popover/App.vue';
import TicketViewer from '../components/TicketViewer/App.vue';

const blade_vars_estados = blade_vars.estados;
const estados = blade_vars_estados.map(function(v){
  return {name: v, val: v}
});


const tickets = ref([]);

const tickets_v = computed(function(){
  return tickets.value.map(function(t){
    return {...t,tags: (t.tags ?? []).join(' ')}
  });
});

const columns = {
  '#': 'numero',Titulo: 'titulo',Autor: 'autor',
  Estado: 'estado', Tags: 'tags',
  Creado: 'created_at', Modificado: 'modified_at',
};

const order = ref({
  column: null,asc: null
});

function change_order(event,col){
  if(order.value.asc === null){
    order.value.column = col;
    order.value.asc = true;
  }
  else if(order.value.asc){
    order.value.column = col;
    order.value.asc = false;
  }
  else {
    order.value.column = null;
    order.value.asc = null;
  }
}

function eliminar(event,ticket,idx){
  popover_data.value.show = false;
  tickets.value.splice(idx,1)
}

const buscador_template = {
  numero: { name: 'Número', type: 'input' },
  titulo: { name: 'Titulo', type: 'input' },
  autor: { name: 'Autor', type: 'input' },
  estado: {
    name: 'Estado',
    type: 'select',
    options: [    
      {name: '- TODOS -', val: ''},
      ...estados
    ],
  },
  tags: { name: 'Tags', type: 'input' },
  created_at: {
    name: 'Creado', type: 'input', input_type: 'date', vals: ['',''],
  },
  modified_at: {
    name: 'Modificado', type: 'input', input_type: 'date', vals: ['',''],
  },
};

const buscador = ref({});

watch(() => ({...buscador.value.rtrn}),buscador_cambio,{deep: true});
watch(() => ({...order.value}),buscador_cambio,{deep: true});

function buscador_cambio(){
  axios.post('/search_tickets',{
    ...buscador.value.rtrn,
    order: {...order.value},
  })
  .then(function(response){
    tickets.value = response.data;
  })
  .catch(function(error){
    tickets.value = [];
    console.log(error);
  });
}

const viewing_ticket = ref(null);
const modal_ver_ticket_refs = ref({
  show_modal: false,
});

function ver_ticket(event,ticket){
  popover_data.value.show = false;
  viewing_ticket.value = ticket.numero;
  modal_ver_ticket_refs.value.show_modal = true;
}

const popover_data = ref({
  x: -1000,y: -1000,data: {},show: false,
});

function show_popover(event,tidx){
  event.stopPropagation();
  popover_data.value.show = true;
  popover_data.value.x = event.clientX;
  popover_data.value.y = event.clientY;
  popover_data.value.data = {
    ticket: tickets.value[tidx],
    tidx: tidx
  };
}

function hide_popover(event,data){
  popover_data.value.show = false;
}
</script>

<style src="./app.css" scoped></style>

<template>
  <WithMenu>
    <Buscador ref="buscador" :values="buscador_template"></Buscador>
    <div id="div_tickets">
      <table id="tickets">
        <thead>
          <tr>
            <th v-for="(col,name) in columns" :key="name" @click="change_order($event,col)">
              {{ name }}{{ col == order.column? (order.asc? '↑' : '↓') : '↕' }}
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(tv,tidx) in tickets_v" :key="tidx" @click="show_popover($event,tidx)">
            <td v-for="(attr,name) in columns" :key="name">
              {{ tv[attr] }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <Modal ref="modal_ver_ticket_refs" :show_modal="modal_ver_ticket_refs.show_modal" id="modalVerTicket" :title="`Ticket #${viewing_ticket}`" >
      <div style="overflow: scroll;height: 100%;width: 100%;padding: 0;margin: 0;">
        <TicketViewer :estados=blade_vars_estados></TicketViewer>
      </div>
    </Modal>
    <Popover :x="popover_data.x" :y="popover_data.y" v-show="popover_data.show" @click-outside="hide_popover">
      <div class="acciones">
        <button style="margin-right: -1px;" @click="ver_ticket($event,popover_data.data.ticket)">VER</button>
        <button @click="eliminar($event,popover_data.data.ticket,popover_data.data.tidx)">ELIMINAR</button>
      </div>
    </Popover>
  </WithMenu>
</template>
