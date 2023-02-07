<script setup>
import { ref, computed, watch, onMounted,nextTick } from 'vue';
import WithMenu from '../components/WithMenu/App.vue';
import Buscador from '../components/Buscador/App.vue';
import Modal from '../components/Modal/App.vue';
import Popover from '../components/Popover/App.vue';
import TicketViewer from '../components/TicketViewer/App.vue';

const blade_vars_states = blade_vars.states;
const tickets = ref([]);

const tickets_v = computed(function(){
  return tickets.value.map(function(t){
    const tags = t.tags ?? [];
    return {...t,tags: tags.join(' ')}
  });
});

const columns = {
  '#': 'number',Title: 'title',Author: 'author',
  Status: 'status', Tags: 'tags',
  Created: 'created_at', Modified: 'updated_at',
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
  number: { name: 'Number', type: 'input' },
  title: { name: 'Title', type: 'input' },
  autor: { name: 'Author', type: 'input' },
  status: {
    name: 'Status',
    type: 'select',
    options: [    
      {name: '- ALL -', val: ''},
      ...blade_vars_states.map(function(v){
        return {name: v, val: v}
      })
    ],
  },
  tags: { name: 'Tags @TODO: LIST', type: 'input' },
  created_at: {
    name: 'Created', type: 'input', input_type: 'date', vals: ['',''],
  },
  updated_at: {
    name: 'Modified', type: 'input', input_type: 'date', vals: ['',''],
  },
  text: {
    name: 'Text', type: 'input'
  }
};

const buscador = ref({});

watch(() => buscador.value.watch_trigger,buscador_cambio,{deep: true});
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

const viewing_ticket = ref(undefined);
const modal_title = computed(function(){
  if(viewing_ticket.value !== null)
    return 'Ticket #'+viewing_ticket.value;
  return 'New ticket';
});
const modal_ver_ticket_refs = ref({
  show_modal: false,
});

function ver_ticket(event,ticket){
  popover_data.value.show = false;
  viewing_ticket.value = ticket? ticket.number : null;
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
      <button id="nuevo" @click="ver_ticket($event,nuevo)">NEW</button>
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
    <Modal ref="modal_ver_ticket_refs" :show_modal="modal_ver_ticket_refs.show_modal" id="modalVerTicket" :title=modal_title >
      <div style="overflow: scroll;height: 100%;width: 100%;padding: 0;margin: 0;">
        <TicketViewer :number=viewing_ticket :states=blade_vars_states></TicketViewer>
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
