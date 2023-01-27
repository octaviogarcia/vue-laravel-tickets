<script setup>
import { ref,computed } from 'vue';
import './app.css'

const props = defineProps(['id','title','show_modal','minimize_modal']);
const show_modal = ref(props.show_modal ?? false);
const minimize_modal = ref(props.minimize_modal ?? false);

const modal_class = computed(function(){
  return {
    modal: true,
  }
});
const title_style = computed(function(){
  return {
    height: minimize_modal.value? '100%' : '10%',
    borderBottomWidth: minimize_modal.value? null : '1px',
    borderBottomStyle: minimize_modal.value? null : 'solid',
  };
});
const body_style = computed(function(){
  return {
    display: minimize_modal.value? 'none' : 'block',
    height: '90%'
  };
});

defineExpose({
  show_modal,
  minimize_modal
});
</script>

<template>
  <div :id="props.id" :class="modal_class" :minimizado="minimize_modal? true : null" v-show="show_modal">
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
