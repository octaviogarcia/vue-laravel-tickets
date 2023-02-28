<script setup>
import WithMenu from '../components/WithMenu/App.vue';
import TicketViewer from '../components/TicketViewer/App.vue';
const states = blade_vars.states;
const user  = blade_vars.user;
</script>

<style src="./app.css" scoped></style>

<template>
  <WithMenu :user=user>
    <TicketViewer :states=states :user=user></TicketViewer>
  </WithMenu>
</template>
