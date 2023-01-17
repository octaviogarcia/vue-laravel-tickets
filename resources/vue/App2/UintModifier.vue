<script setup>
  import { ref } from 'vue';
  
  const props = defineProps({
    valueName: String,
    value: Number,
  });
  
  //const { _, value} = toRefs(props);
  const value = ref(props.value);
  
  defineExpose({
    value
  });
</script>

<template>
  <span style="padding-left: 25px;">
    <span style="padding-right: 5px;">{{ props.valueName }}: {{ value }}</span>
    <button @click="value = value>0? value-1: 0">-</button>
    <button @click="value++">+</button>
  </span>
</template>
