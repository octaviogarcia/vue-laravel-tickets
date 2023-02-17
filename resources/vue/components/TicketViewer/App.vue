<script setup>
import { ref, watch } from 'vue';

const props = defineProps(['states','number']);

const tickets = ref([]);
const tickets_v = ref([]);

function ticket_vacio(){
  return {
    id: null,
    parent: tickets.value.length? tickets.value[0].number : null,
    number: null,
    title: '',
    author: '@TODO: USER',
    status: props.states[0],
    tags: [],
    text: '',
    files: [],
    created_at: '',
    updated_at: '',
  };
}

function agregar_ticket(){
  tickets.value.push(ticket_vacio());
  tickets_v.value.push(ticket_vacio());
  tickets_v.value[tickets_v.value.length-1].editing = true;
}

watch(() => props.number,function(){
  if(props.number === null){
    tickets.value   = [];
    tickets_v.value = [];
    agregar_ticket();
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

function buildFormData(formData, data, parentKey) {
  if (data && typeof data === 'object' && !(data instanceof Date) && !(data instanceof File)) {
    Object.keys(data).forEach(key => {
      buildFormData(formData, data[key], parentKey ? `${parentKey}[${key}]` : key);
    });
  } else {
    formData.append(parentKey, data == null ? '' : data);
  }
}
function objToFormData(data) {
  const formData = new FormData();
  buildFormData(formData, data);
  return formData;
}

function guardar(event,tidx){
  const ticket_v = tickets_v.value[tidx];
  if(!ticket_v.editing){
    ticket_v.editing = true;
    return;
  }
  ticket_v.tags = (ticket_v.tags ?? []).filter((s) => (s ?? '').length > 0);
  const ticket   = tickets.value[tidx];  
  
  const formDataObj = {};
  copyObject(ticket_v,formDataObj,ticket);
  delete formDataObj.files;
  delete formDataObj.new_files;
  const formData = objToFormData(formDataObj);
  
  for(const f of ticket_v.files){
    formData.append('old_files[]',f);
  }
  for(const f of (ticket_v.new_files ?? [])){
    formData.append('files[]',f.file,f.name);
  }
  
  axios.post('/save_ticket', formData)
  .then(function(response){
    copyObject(response.data,ticket,response.data);
    tickets_v.value[tidx] = JSON.parse(JSON.stringify(ticket));
    tickets_v.value[tidx].editing = false;
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
      
      ticket_v.new_files = ticket_v.new_files ?? [];
      ticket_v.new_files.push({
        name: file.name,
        url: window.URL.createObjectURL(blob),
        file: file,
      });
    }
    reader.readAsBinaryString(file);
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
          <div>{{ ticket_v.number }}</div>
        </div>
        <div class="cabecera_ticket">
          <div>Created</div>
          <div>{{ ticket_v.created_at }}</div>
        </div>
        <div class="cabecera_ticket">
          <div>Modified</div>
          <div>{{ ticket_v.updated_at }}</div>
        </div>
        <div class="cabecera_ticket">
          <div>Title</div>
          <div><input style="width: 100%;" v-model="ticket_v.title" :disabled="!ticket_v.editing"></div>
        </div>
        <div class="cabecera_ticket">
          <div>State</div>
          <div>
            <select style="width: 100%;" v-model="ticket_v.status" :disabled="!ticket_v.editing">
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
            <textarea class="tag contenteditable" 
              :contenteditable="ticket_v.editing? true : null"
              @focusout="(ev) => ticket_v.tags[tagidx] = ev.target.value.trim()"
              :value="tag">
            </textarea>
            <button class="cruz_borrar" @click="ticket_v.tags.splice(tagidx,1)" v-if="ticket_v.editing">×</button>
          </div>
          <div style="float: left;width: 7em;" v-if="ticket_v.editing">
            <textarea class="tag" 
              :contenteditable="ticket_v.editing? true : null"
              @focusout="(ev) => {
                if(ticket_v.tags === null) ticket_v.tags = [];
                ticket_v.tags.push(ev.target.value.trim());
                ev.target.value='';
              }">
            </textarea>
          </div>
        </div>
      </div>
      <hr style="width: 97%;">
      <div class="contenteditable cuerpo"
        :contenteditable="ticket_v.editing? true : null"
        @focusout="(ev) => ticket_v.text = ev.target.innerHTML"
        v-html="ticket_v.text">
      </div>
      <div v-show="(ticket_v.files ?? []).length || (ticket_v.new_files ?? []).length">
        <div>Files</div>
        <div>
          <div class="archivo" v-for="(f,fidx) in ticket_v.files">
            <template v-if="f">
              <a :href="`/ticket_file/${ticket_v.number}/${ticket_v.id}/${fidx}`" target="_blank" :download="f">{{ f }}</a>
              <button v-if="ticket_v.editing" class="cruz_borrar" @click="ticket_v.files.splice(fidx,1)">×</button>
            </template>
          </div>
          <div class="archivo" v-for="(f,fidx) in ticket_v.new_files">
            <a :href="f.url" target="_blank" :download="f.name">{{ f.name }}</a>
            <button v-if="ticket_v.editing" class="cruz_borrar" @click="ticket_v.new_files.splice(fidx,1)">×</button>
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
    <div v-if="tickets.length && tickets[0].number !== null && tickets[0].id !== null">
      <button @click="agregar_ticket()">AGREGAR</button>
    </div>
  </div>
</template>
