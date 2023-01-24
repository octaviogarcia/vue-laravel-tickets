<script setup>
import { ref, computed, onMounted,nextTick } from 'vue';

const tickets = Array.from({length: 100}, () => Math.floor(Math.random() * 100)).map(function(v,idx){
  function rand(maxnum){
    const next_pow10 = 10**Math.ceil(Math.log10(maxnum));
    return Math.floor(Math.random()*next_pow10)%maxnum;
  }
  return {
    numero: rand(10000),
    titulo: 'Titulo'+idx,
    autor: 'Autor'+rand(23),
    estado: [
      'ABIERTO','SOLUCIONADO','CERRADO'
    ][rand(3)],
    tags: Array.from({length: rand(4)},(v) => 'tag'+rand(4)),
    created_at: new Date(blade_vars.server_time*1000).toLocaleString(),
    modified_at: new Date(blade_vars.server_time*1000).toLocaleString(),
  }
});
</script>

<template>
  <div id="main">
    <div id="menu">
      <button id="ver_usuario">Usuario: Pepe Sanchez</button>
      <button id="ver_usuarios">USUARIOS</button>
      <button id="ver_tickers">TICKETS</button>
      <button id="ver_informes">INFORMES</button>
      <button id="logout">LOGOUT</button>
    </div>
    <div id="contenido">
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
</template>
