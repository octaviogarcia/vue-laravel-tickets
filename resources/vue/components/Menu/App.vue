<script setup>
const props = defineProps(['user']);
</script>

<style src="./app.css" scoped></style>

<template>
  <div class="menu">
    <button class="ver_usuario">Usuario: {{props.user.name}}</button>
    <button class="ver_usuarios">USUARIOS</button>
    <button class="ver_tickers">TICKETS</button>
    <button class="ver_informes">INFORMES</button>
    <a href="logout"><button class="logout">LOGOUT</button></a>
  </div>
</template>
