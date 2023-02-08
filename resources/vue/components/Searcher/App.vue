<script setup>
import { ref, watch, Transition } from 'vue';
import Tooltip from '../Tooltip/App.vue';

const props = defineProps(['id','values','title']);
const emit = defineEmits(['val-change']);

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
  props.values[attr].error = props.values[attr].error ?? '';
  rtrn.value[attr] = simplify(props.values[attr].vals);
}

function value_change(event,attr,validx){
  update_val(event,attr,validx);
  emit('val-change',event);
}

function update_val(event,attr,validx){
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

emit('val-change',null);
</script>

<style src="./app.css" scoped></style>

<template>
  <div :id="props.id" class="div_fondo searcher" :minimized="open? null : true" :first_render="first_render? true : null">
    <div class="title" @click="open=!open">
      <div>{{ props.title ?? 'FILTERS' }}&nbsp;&nbsp;&nbsp;{{ open? '⦾':'⦿' }}</div>
    </div>
    <Transition name="attrs">
      <div class="attrs" v-show="open">
        <div class="attr" v-for="(aobj,attr) in props.values" :key="attr">
          <div>{{aobj.name}}</div>
          <Tooltip :text="aobj.error">
            <div><!-- @TODO: tratar de usar Dynamic Components  <component :is=""></component> -->
              <!-- @HACK: Binding val sets weird behavior on changing input,
               if you take too much to change the input (by not focusing-out) 
               it returns the value to the original
               so I just change the val on each keypress... @SLOW
               -->
              <template v-if="aobj.type == 'input'">
                <input v-for="(val,validx) in aobj.vals" 
                :errored="aobj.error? aobj.error : null"
                :type="aobj.input_type"
                :key="validx"
                @keyup="update_val($event,attr,validx)"
                @change="value_change($event,attr,validx)"
                :value="val"
                >
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
          </Tooltip>
        </div>
      </div>
    </Transition>
  </div>
</template>
