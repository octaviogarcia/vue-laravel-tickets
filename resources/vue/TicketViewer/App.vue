<script setup>
import WithMenu from '../components/WithMenu/App.vue';
import TicketViewer from '../components/TicketViewer/App.vue';
const estados = blade_vars.estados;
</script>

<style src="./app.css" scoped></style>

<template>
  <WithMenu>
    <TicketViewer :estados=estados></TicketViewer>
  </WithMenu>
</template>
