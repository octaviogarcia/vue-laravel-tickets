<script setup>
import { ref,computed, Transition } from 'vue';
import './app.css'

const props = defineProps(['id','title','show_modal','minimize_modal']);
const show_modal = ref(props.show_modal ?? false);
const minimize_modal = ref(props.minimize_modal ?? false);

defineExpose({
  show_modal,
  minimize_modal
});
</script>

<template>
  <Transition name="modal">
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
          <Transition name="modal-body">
            <div class="modal-body" v-show="!minimize_modal">
              <slot>
                <div>XXXXXXXXXXXXXXXXXXXXX</div>
              </slot>
            </div>
          </Transition>
        </div>
      </div>
    </div>
  </Transition>
</template>
