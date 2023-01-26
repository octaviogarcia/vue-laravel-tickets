<script setup>
import '/resources/css/style.css';
import './app.css';
import Menu from '../Menu/App.vue';
</script>

<template>
  <div id="main">
    <Menu/>
    <div id="contenido">
      <slot><div>Contenido!</div></slot>
    </div>
  </div>
</template>
