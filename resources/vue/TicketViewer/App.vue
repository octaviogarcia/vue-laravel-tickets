<script setup>
import WithMenu from '../components/WithMenu/App.vue';
import TicketViewer from '../components/TicketViewer/App.vue';
const states = blade_vars.states;
</script>

<style src="./app.css" scoped></style>

<template>
  <WithMenu>
    <TicketViewer :states=states></TicketViewer>
  </WithMenu>
</template>
