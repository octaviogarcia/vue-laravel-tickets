<script setup>
import { ref,computed, watch } from 'vue';

const props = defineProps(['id','title','show_modal','minimize_modal']);
const show_modal = ref(props.show_modal ?? false);
const minimize_modal = ref(props.minimize_modal ?? false);

watch([show_modal,minimize_modal],function([n_show_modal,n_minimize_modal],[o_show_modal,o_minimize_modal]){
  //We don't allow you to open the modal with the modal minimized
  if(n_show_modal && !o_show_modal && n_minimize_modal){
    minimize_modal.value = false;
  }
});

defineExpose({
  show_modal,
  minimize_modal
});
</script>

<style src="./app.css" scoped></style>

<template>
  <div :id="props.id" class="modal" :minimizado="minimize_modal? true : null" v-show="show_modal">
    <div>
      <div class="modal-window">
        <div class="modal-header">
          <div>
            <div class="modal-header-title">{{props.title ?? 'XXXXXXXXXXXXXXXXXXXXX'}}</div>
            <div class="modal-header-buttons">
              <button @click="minimize_modal = !minimize_modal">_</button>
              <button @click="show_modal = false">☓</button>
            </div>
          </div>
        </div>
        <div class="modal-body">
          <slot>
            <div>XXXXXXXXXXXXXXXXXXXXX</div>
          </slot>
        </div>
      </div>
    </div>
  </div>
</template>
