<script setup>
import { ref, computed, onMounted, nextTick } from 'vue';
import UintModifier from './UintModifier.vue'

const count = ref(0);//Reactivo

function increment() { count.value++; }

const even = computed(function(){return (count.value % 2) == 0;});
const odd  = computed(function(){return !even.value;});

const colsModifier = ref({value: 1});
const rowsModifier = ref({value: 1});
const widthModifier = ref({value: 200});
const heightModifier = ref({value: 100});

const divTableResizeObserver = new ResizeObserver(function(entries){
  const px_to_int = function(s){
    s = parseInt(s.slice(0,-2));
    return s!=s? 0 : s;//NaN check
  };
  for(const entry of entries){
    if(entry.target.id != 'divTable') continue;
    const leftpx = px_to_int(entry.target.style.borderLeftWidth)
    + px_to_int(entry.target.style.paddingLeft)
    + px_to_int(entry.target.style.marginLeft);
    const rightpx = px_to_int(entry.target.style.borderRightWidth)
    + px_to_int(entry.target.style.paddingRight)
    + px_to_int(entry.target.style.marginRight);
    const toppx = px_to_int(entry.target.style.borderTopWidth)
    + px_to_int(entry.target.style.paddingTop)
    + px_to_int(entry.target.style.marginTop);
    const bottompx = px_to_int(entry.target.style.borderBottomWidth)
    + px_to_int(entry.target.style.paddingBottom)
    + px_to_int(entry.target.style.marginBottom);
    const rect = entry.target.getBoundingClientRect();
    widthModifier.value.value  = rect.width-leftpx-rightpx;
    heightModifier.value.value = rect.height-toppx-bottompx;
  }
});

onMounted(function(){
  console.log(`Initial count ${count.value}`);
  nextTick(function(){
    divTableResizeObserver.observe(document.getElementById('divTable'));
  });
});

</script>

<style src="./app2.css" scoped></style>

<template>
  <p>Different view!</p>
  <button @click="increment">
    Count is: {{ count }}
  </button>
  <button @click="count=0"><!-- Inline event handling, DO NOT use .value -->
    Reset
  </button>
  <p v-show="even">Is even</p>
  <p v-show="odd">Is odd</p><!-- Could use v-if and v-else aswell -->
  <p>Square is {{ count*count }}</p><!-- Inline expression -->
  <p>
    <UintModifier ref="rowsModifier" value-name="Rows" :value=rowsModifier.value />
    <UintModifier ref="colsModifier" value-name="Columns" :value=colsModifier.value />
  </p>
  <p>
    <UintModifier ref="widthModifier" value-name="Width" :value=widthModifier.value />
    <UintModifier ref="heightModifier" value-name="Height" :value=heightModifier.value />
  </p>
  <div id="divTable" :style="{ 'border': '1px solid black','overflow': 'scroll',
                 'width': widthModifier.value+'px','height': heightModifier.value+'px',
                  'resize': 'both'}">
    <table>
      <thead>
        <tr>
          <th v-for="index in colsModifier.value" :key="index">column</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="rindex in rowsModifier.value" :key="rindex">
          <td v-for="index in colsModifier.value" :key="index">value</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
