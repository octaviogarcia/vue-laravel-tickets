<script setup>
import { ref, watch, Transition } from 'vue';

const props = defineProps(['id','values','title']);
const emit = defineEmits(['change']);
const values = ref(props.values);

const rtrn = {}
for(const p of Object.keys(props.values)){
  props.values[p].vals = props.values[p].vals ?? [''];
  rtrn[p] = [...props.values[p].vals];
}

function value_change(event,prop,validx){
  values.value[prop].vals[validx] = event.target.value;
  rtrn[prop][validx] = event.target.value;
  emit('change',event,rtrn);
}

const open = ref(false);

let first_render = ref(true);
watch(open,function(n_open,o_open){
  first_render.value = false;//@HACK: avoids triggering animation on load due CSS selector :not
});
</script>

<style src="./app.css" scoped></style>

<template>
  <div :id="props.id" class="div_fondo buscador" :minimizado="open? null : true" :first_render="first_render? true : null">
    <div class="title" @click="open=!open">
      <div>{{ props.title ?? 'FILTROS DE BÚSQUEDA' }}&nbsp;&nbsp;&nbsp;{{ open? '⦾':'⦿' }}</div>
    </div>
    <Transition name="props">
      <div class="props" v-show="open">
        <div class="prop" v-for="(prop,pidx) in Object.keys(values)" :key="pidx">
          <div>{{values[prop].name}}</div>
          <div><!-- @TODO: tratar de usar Dynamic Components  <component :is=""></component> -->
            <template v-if="values[prop].type == 'input'">
              <input v-for="(val,validx) in values[prop].vals" 
              :type="values[prop].input_type"
              :key="validx"
              @change="value_change($event,prop,validx)"
              :value="val">
            </template>
            <template v-else-if="values[prop].type == 'select'">
              <select v-for="(val,validx) in values[prop].vals" 
              :key="validx"
              @change="value_change($event,prop,validx)"
              :value="val">
                <option v-if="values[prop].options" v-for="(o,oidx) in values[prop].options" :key="oidx" :value="o.val">
                  {{ o.name }}
                </option>
              </select>
            </template>
            <span v-else>Unknown type</span>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>
