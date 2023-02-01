<script setup>
import { ref, computed, watch, onMounted,nextTick } from 'vue';
import WithMenu from '../components/WithMenu/App.vue';
import Buscador from '../components/Buscador/App.vue';
import Modal from '../components/Modal/App.vue';
import Popover from '../components/Popover/App.vue';
import TicketViewer from '../components/TicketViewer/App.vue';

const estados = [
  {name: 'ABIERTO', val: 'ABIERTO'},
  {name: 'SOLUCIONADO', val: 'SOLUCIONADO'},
  {name: 'CERRADO', val: 'CERRADO'}
];

const tickets = ref(Array.from({length: 100}, () => Math.floor(Math.random() * 100)).map(function(v,idx){
  function rand(maxnum){
    const next_pow10 = 10**Math.ceil(Math.log10(maxnum));
    return Math.floor(Math.random()*next_pow10)%maxnum;
  }
  return {
    numero: rand(10000),
    titulo: 'Titulo'+idx,
    autor: 'Autor'+rand(23),
    estado: estados[rand(estados.length)].name,
    tags: Array.from({length: rand(4)},(v) => 'tag'+rand(4)),
    created_at: new Date(blade_vars.server_time*1000).toLocaleString(),
    modified_at: new Date(blade_vars.server_time*1000).toLocaleString(),
  }
}));

function eliminar(event,ticket,idx){
  popover_data.value.show = false;
  tickets.value.splice(idx,1)
}

const buscador = {
  numero: { name: 'Número', type: 'input' },
  titulo: { name: 'Titulo', type: 'input' },
  Autor: { name: 'Autor', type: 'input' },
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

function buscador_cambio(event,test){
  console.log(test);//@TODO: API request
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

function show_popover(event,ticket,tidx){
  event.stopPropagation();
  popover_data.value.show = true;
  popover_data.value.x = event.clientX;
  popover_data.value.y = event.clientY;
  popover_data.value.data = {
    ticket: ticket,
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
    <Buscador id="buscador" :values="buscador" @change="buscador_cambio"></Buscador>
    <div id="div_tickets">
      <table id="tickets">
        <thead>
          <tr>
            <th>#</th>
            <th>Titulo</th>
            <th>Autor</th>
            <th>Estado</th>
            <th>Tags</th>
            <th>Creado</th>
            <th>Modificado</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(ticket,tidx) in tickets" :key="tidx" @click="show_popover($event,ticket,tidx)">
            <td>{{ ticket.numero }}</td>
            <td>{{ ticket.titulo }}</td>
            <td>{{ ticket.autor }}</td>
            <td>{{ ticket.estado }}</td>
            <td>{{ (ticket.tags ?? []).join(' ') }}</td>
            <td>{{ ticket.created_at }}</td>
            <td>{{ ticket.modified_at }}</td>
          </tr>
        </tbody>
      </table>
    </div>
    <Modal ref="modal_ver_ticket_refs" :show_modal="modal_ver_ticket_refs.show_modal" id="modalVerTicket" :title="`Ticket #${viewing_ticket}`" >
      <div style="overflow: scroll;height: 100%;width: 100%;padding: 0;margin: 0;">
        <TicketViewer></TicketViewer>
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
