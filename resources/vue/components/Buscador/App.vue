<script setup>
import { ref, watch, Transition, onMounted, nextTick } from 'vue';

const props = defineProps(['id','values','title']);

function simplify(arr){
  if(arr.length == 1){
    return arr[0]
  }
  else if(arr.length == 0){
    return null;
  }
  return arr;
}

const rtrn = ref({})
for(const attr of Object.keys(props.values)){
  props.values[attr].vals = props.values[attr].vals ?? [''];
  rtrn.value[attr] = simplify(props.values[attr].vals);
}

function value_change(event,attr,validx){
  props.values[attr].vals[validx] = event.target.value;
  rtrn.value[attr] = simplify(props.values[attr].vals);
}

const open = ref(false);

let first_render = ref(true);
watch(open,function(n_open,o_open){
  first_render.value = false;//@HACK: avoids triggering animation on load due CSS selector :not
});

defineExpose({
  rtrn,
});
</script>

<style src="./app.css" scoped></style>

<template>
  <div :id="props.id" class="div_fondo buscador" :minimizado="open? null : true" :first_render="first_render? true : null">
    <div class="title" @click="open=!open">
      <div>{{ props.title ?? 'FILTROS DE BÚSQUEDA' }}&nbsp;&nbsp;&nbsp;{{ open? '⦾':'⦿' }}</div>
    </div>
    <Transition name="attrs">
      <div class="attrs" v-show="open">
        <div class="attr" v-for="(aobj,attr) in values" :key="attr">
          <div>{{aobj.name}}</div>
          <div><!-- @TODO: tratar de usar Dynamic Components  <component :is=""></component> -->
            <template v-if="aobj.type == 'input'">
              <input v-for="(val,validx) in aobj.vals" 
              :type="aobj.input_type"
              :key="validx"
              @change="value_change($event,attr,validx)"
              :value="val">
            </template>
            <template v-else-if="aobj.type == 'select'">
              <select v-for="(val,validx) in aobj.vals" 
              :key="validx"
              @change="value_change($event,attr,validx)"
              :value="val">
                <option v-if="aobj.options" v-for="(o,oidx) in aobj.options" :key="oidx" :value="o.val">
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
