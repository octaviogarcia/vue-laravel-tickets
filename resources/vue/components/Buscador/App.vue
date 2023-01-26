<script setup>
import { ref, watch } from 'vue';
import './app.css'

const props = defineProps(['id','values']);
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
</script>

<template>
  <div :id="props.id" class="div_fondo">
    <div v-for="(prop,pidx) in Object.keys(values)" :key="pidx">
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
</template>
