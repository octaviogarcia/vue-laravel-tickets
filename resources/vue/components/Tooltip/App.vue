<script setup>
import { ref } from 'vue';
const props = defineProps(['text']);
const hovering = ref(false);

</script>

<style src="./app.css" scoped></style>

<template>
  <span @mouseover="hovering=true" @mouseleave="hovering=false">
    <span class="tooltip" v-show="props.text && hovering">{{ props.text }}</span>
    <span class="tooltipped"><slot></slot></span>
  </span>
</template>
