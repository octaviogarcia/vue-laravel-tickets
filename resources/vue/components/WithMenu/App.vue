<script setup>
import '/resources/css/style.css';
import Menu from '../Menu/App.vue';
const props = defineProps(['user']);
</script>

<style src="./app.css" scoped></style>

<template>
  <div id="main">
    <Menu :user=props.user />
    <div id="contenido">
      <slot><div>Contenido!</div></slot>
    </div>
  </div>
</template>
