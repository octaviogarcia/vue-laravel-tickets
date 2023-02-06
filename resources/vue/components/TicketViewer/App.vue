<script setup>
import { ref, watch } from 'vue';

const props = defineProps(['states','number']);

const tickets = ref([]);
const tickets_v = ref([]);

watch(() => props.number,function(){
  if(props.number === null){
    tickets.value = [{
      'id': null,
      'number': '-NEW-',
      'title': '',
      'author': '@TODO: USER',
      'state': props.states[0],
      'tags': [],
      'text': '',
      'files': '',
      'created_at': '',
      'modified_at': '',
    }];
    tickets_v.value = JSON.parse(JSON.stringify(tickets.value));
    tickets_v.value[0].editing = true;
  }
  else if(props.number){
    axios.get('/get_ticket/'+(props.number ?? ''))
    .then(function(response){
      tickets.value = response.data;
      tickets_v.value = JSON.parse(JSON.stringify(tickets.value));
    })
    .catch(function(error){
      console.log(error);
    });
  }
});

function guardar(event,tidx){
  const ticket_v = tickets_v.value[tidx];
  const ticket   = tickets.value[tidx];  
  axios.post('/save_ticket',tickets.value[tidx])
  .then(function(response){
    ticket_v.editing = !ticket_v.editing;
    ticket_v.tags = (ticket_v.tags ?? []).filter((s) => s.length > 0);
    copyObject(ticket_v,ticket,ticket);
  })
  .catch(function(error){
    console.log(error);
  });
}

function cancelar(event,tidx){
  const ticket_v = tickets_v.value[tidx];
  const ticket   = tickets.value[tidx];
  copyObject(ticket,ticket_v,ticket);
  ticket_v.editing = false;
}

function copyObject(from,to,keysfrom){
  for(const k of Object.keys(keysfrom)){
    if(Array.isArray(from[k])){//Stop using keysfrom and just deepclone
      to[k] = JSON.parse(JSON.stringify(from[k]));
    }
    else if(typeof from[k] == 'object' && from[k] !== null){
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
  if(ticket_v.editing){
    ticket_v.file_select.dispatchEvent(new MouseEvent('click'));
  }
}

function seleccionArchivos(event,ticket_v){
  for(const file of (event.target.files ?? [])){
    if(!ticket_v.files){
      ticket_v.files = [];
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
      
      ticket_v.files.push({
        name: file.name,
        url: window.URL.createObjectURL(blob),
        is_new: true,
        file: file,
      });
    }
    reader.readAsBinaryString(file);
  }
}

function cambio_tag(event,ticket_v,tagidx){
  ticket_v.tags[tagidx] = event.target.textContent;
  if(tagidx == (ticket_v.tags.length-1) && event.target.textContent != ''){
    ticket_v.tags.push('');
  }
}
</script>

<style src="./app.css" scoped></style>

<template>
  <div class="ticket_viewer">
    <div :data-tidx="tidx" class="ticket div_fondo" v-for="(ticket_v,tidx) in tickets_v" :key="tidx">
      <div v-if="tidx==0">
        <div class="cabecera_ticket">
          <div>Number</div>
          <div>{{ ticket_v.number ?? '-NUEVO-' }}</div>
        </div>
        <div class="cabecera_ticket">
          <div>Created</div>
          <div>{{ ticket_v.created_at ?? '--' }}</div>
        </div>
        <div class="cabecera_ticket">
          <div>Modified</div>
          <div>{{ ticket_v.modified_at ?? '--'}}</div>
        </div>
        <div class="cabecera_ticket">
          <div>Title</div>
          <div><input style="width: 100%;" v-model="ticket_v.title" :disabled="!ticket_v.editing"></div>
        </div>
        <div class="cabecera_ticket">
          <div>State</div>
          <div>
            <select style="width: 100%;" v-model="ticket_v.state" :disabled="!ticket_v.editing">
              <option v-for="(e,eidx) in props.states" :key="eidx">{{ e }}</option>
            </select>
          </div>
        </div>
      </div>
      <div class="cabecera_ticket">
        <div>Author</div>
        <div><input style="width: 100%;" v-model="ticket_v.author" :disabled="!ticket_v.editing"></div>
      </div>
      <div v-if="tidx==0" class="cabecera_ticket">
        <div>Tags</div>
        <div class="taglist">
          <div style="float: left;width: 7em;" v-for="(tag,tagidx) in ticket_v.tags" :key="tagidx" >
            <div class="tag contenteditable" 
              :contenteditable="ticket_v.editing? true : null"
              @focusout="(ev) => ticket_v.tags[tagidx] = ev.target.innerHTML"
              v-html="tag">
            </div>
            <button class="cruz_borrar" @click="ticket_v.tags.splice(tagidx,1)" v-if="ticket_v.editing">×</button>
          </div>
          <div style="float: left;width: 7em;" v-if="ticket_v.editing">
            <div class="tag contenteditable" 
              :contenteditable="ticket_v.editing? true : null"
              @focusout="(ev) => {ticket_v.tags.push(ev.target.innerHTML);ev.target.innerHTML='';}">
            </div>
          </div>
        </div>
      </div>
      <hr style="width: 97%;">
      <div class="contenteditable cuerpo"
        :contenteditable="ticket_v.editing? true : null"
        @focusout="(ev) => ticket_v.text = ev.target.innerHTML"
        v-html="ticket_v.text">
      </div>
      <div v-show="(ticket_v.files ?? []).length">
        <div>Files</div>
        <div>
          <div class="archivo" v-for="(f,fidx) in ticket_v.files">
            <a :href="f.url" target="_blank" :title="f.title" :download="f.name">{{ f.name }}</a>
            <button v-if="ticket_v.editing" class="cruz_borrar" @click="ticket_v.files.splice(fidx,1)">×</button>
          </div>
        </div>
      </div>
      <div class="acciones">
        <button @click="guardar($event,tidx)">{{ ticket_v.editing? 'SAVE' : 'EDIT'}}</button>
        <button v-show="ticket_v.editing" @click="cancelar($event,tidx)">CANCEL</button>
        <button v-show="ticket_v.editing" @click="adjuntar($event,ticket_v)">ATTACH</button>
        <input :ref="(el) => { ticket_v.file_select = el; }" type="file" multiple  
          class="file_select" 
          style="position: absolute; top: -1000px; left: -1000px;visiblity: hidden;"
          @change="seleccionArchivos($event,ticket_v)">
        <button v-show="ticket_v.editing">DELETE</button>
        <button v-show="!ticket_v.editing">HISTORY</button>
      </div>
    </div>
  </div>
</template>
