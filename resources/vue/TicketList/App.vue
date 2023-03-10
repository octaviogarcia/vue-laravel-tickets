<script setup>
import { ref, computed, watch, onMounted,nextTick } from 'vue';
import WithMenu from '../components/WithMenu/App.vue';
import Searcher from '../components/Searcher/App.vue';
import Modal from '../components/Modal/App.vue';
import Popover from '../components/Popover/App.vue';
import TicketViewer from '../components/TicketViewer/App.vue';

const blade_vars_states = blade_vars.states;
const user = blade_vars.user;
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

function delete_ticket(event,ticket,idx){
  popover_data.value.show = false;
  
  axios.delete('/delete_parent_ticket/'+ticket.number)
  .then(function(response){
    tickets.value.splice(idx,1);
  })
  .catch(function(error){
    console.log(error);
  });
}

const searcher_template = ref({
  number: { name: 'Number', type: 'input' },
  title: { name: 'Title', type: 'input' },
  author: { name: 'Author', type: 'input' },
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
  tags: { name: 'Tags', type: 'input', vals: ['',''] },
  created_at: {
    name: 'Created', type: 'input', input_type: 'date', vals: ['',''],
  },
  updated_at: {
    name: 'Modified', type: 'input', input_type: 'date', vals: ['',''],
  },
  text: {
    name: 'Text', type: 'input'
  }
});

const searcher = ref({});

watch(() => ({...order.value}),searcher_change,{deep: true});

function searcher_change(){
  if(searcher.value.rtrn?.tags?.filter(function(t){ return t.length > 0; }).length 
     ==
     searcher_template.value.tags.vals.length){
    searcher_template.value.tags.vals.push('');
  }
  else{
    searcher_template.value.tags.vals = searcher_template.value.tags.vals.filter(
      function(t,tidx){ return t.length > 0; }
    );
    searcher_template.value.tags.vals.push('');
    //@HACK: to avoid getting a simple string value instead of array in rtrn
    while(searcher_template.value.tags.vals.length <= 1)
      searcher_template.value.tags.vals.push('');
  }
  
  for(const attr of Object.keys({...searcher_template.value})){//Clear errors
    if(searcher_template.value[attr].error)
      searcher_template.value[attr].error = '';
  }
  
  axios.post('/search_tickets',{
    ...searcher.value.rtrn,
    order: {...order.value},
  })
  .then(function(response){
    tickets.value = response.data;
  })
  .catch(function(error){
    tickets.value = [];
    const errors = error.response.data.errors;
    for(const attr of Object.keys(errors ?? {})){
      searcher_template.value[attr].error = errors[attr].join('\n ');
    }
  });
}

const viewing_ticket = ref(undefined);
const modal_title = computed(function(){
  if(viewing_ticket.value !== null)
    return 'Ticket #'+viewing_ticket.value;
  return 'New ticket';
});
const modal_view_ticket = ref({
  show_modal: false,
});

function view_ticket(event,ticket){
  popover_data.value.show = false;
  viewing_ticket.value = ticket? ticket.number : null;
  modal_view_ticket.value.show_modal = true;
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
  <WithMenu :user=user>
    <Searcher ref="searcher" :values="searcher_template" @val-change="searcher_change($event)"></Searcher>
    <div id="div_tickets">
      <button @click="view_ticket($event)">NEW</button>
      <table id="tickets">
        <thead>
          <tr>
            <th v-for="(col,name) in columns" :key="name" @click="change_order($event,col)">
              {{ name }}{{ col == order.column? (order.asc? '???' : '???') : '???' }}
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
    <Modal ref="modal_view_ticket" :show_modal="modal_view_ticket.show_modal" :title=modal_title >
      <div style="overflow: scroll;height: 100%;width: 100%;padding: 0;margin: 0;">
        <TicketViewer :number=viewing_ticket :states=blade_vars_states :user=user></TicketViewer>
      </div>
    </Modal>
    <Popover :x="popover_data.x" :y="popover_data.y" v-show="popover_data.show" @click-outside="hide_popover">
      <div class="acciones">
        <button style="margin-right: -1px;" @click="view_ticket($event,popover_data.data.ticket)">VIEW</button>
        <button @click="delete_ticket($event,popover_data.data.ticket,popover_data.data.tidx)">DELETE</button>
      </div>
    </Popover>
  </WithMenu>
</template>
