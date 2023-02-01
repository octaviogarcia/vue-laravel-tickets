<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
const props = defineProps(['x','y']);
const emit = defineEmits(['click-outside']);
const popover = ref(null); 

function clickOutsideCheck(event){
  if (!(popover.value == event.target || popover.value.contains(event.target))) {
    emit('click-outside',event,[props.x,props.y]);
  }
}

onMounted(function(){
  document.addEventListener("click",clickOutsideCheck);
});
onUnmounted(function(){
  document.removeEventListener("click",clickOutsideCheck);
});
</script>

<style src="./app.css" scoped></style>

<template>
  <div ref="popover" class="popover" :style="{ left: x+'px', top: y+'px' }">
    <slot></slot>
  </div>
</template>
