<script setup>
import { ref, computed, onMounted,nextTick } from 'vue';
import Menu from '../Menu/App.vue'

const estados = ['ABIERTO','SOLUCIONADO','CERRADO'];

const tickets = Array.from({length: 100}, () => Math.floor(Math.random() * 100)).map(function(v,idx){
  function rand(maxnum){
    const next_pow10 = 10**Math.ceil(Math.log10(maxnum));
    return Math.floor(Math.random()*next_pow10)%maxnum;
  }
  return {
    numero: rand(10000),
    titulo: 'Titulo'+idx,
    autor: 'Autor'+rand(23),
    estado: estados[rand(estados.length)],
    tags: Array.from({length: rand(4)},(v) => 'tag'+rand(4)),
    created_at: new Date(blade_vars.server_time*1000).toLocaleString(),
    modified_at: new Date(blade_vars.server_time*1000).toLocaleString(),
  }
});

</script>

<template>
  <div id="main">
    <Menu/>
    <div id="contenido">
      <div id="buscador" class="div_fondo">
        <div>
          <div>Número</div>
          <div><input></div>
        </div>
        <div>
          <div>Titulo</div>
          <div><input></div>
        </div>
        <div>
          <div>Autor</div>
          <div><input></div>
        </div>
        <div>
          <div>Estado</div>
          <div>
            <select>
              <option v-for="(e,eidx) in estados" :key="eidx">
                {{ e }}
              </option>
            </select>
          </div>
        </div>
        <div>
          <div>Tags</div>
          <div><input></div>
        </div>
        <div>
          <div>Creado</div>
          <div><input type="date"><input type="date"></div>
        </div>
        <div>
          <div>Modificado</div>
          <div><input type="date"><input type="date"></div>
        </div>
      </div>
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
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(ticket,tidx) in tickets" :key="tidx">
              <td>{{ ticket.numero ?? '-NUEVO-' }}</td>
              <td>{{ ticket.titulo }}</td>
              <td>{{ ticket.autor }}</td>
              <td>{{ ticket.estado }}</td>
              <td>{{ (ticket.tags ?? []).join(' ') }}</td>
              <td>{{ ticket.created_at }}</td>
              <td>{{ ticket.modified_at }}</td>
              <td>
                <div class="acciones">
                  <button>VER</button>
                  <button>ELIMINAR</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
