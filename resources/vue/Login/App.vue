<script setup>
import { ref } from 'vue';
import '/resources/css/style.css';

const creating_user = !!blade_vars.creating_user;
const email = ref('');
const name = ref('');
const password = ref('');
const response = ref('');

function show_response(e1,e2){
  axios.post(window.location.href, {
    email: email.value,name: name.value,password: password.value
  })
  .then(function(r){
    if(creating_user)
      response.value = r.data;
    else
      window.location.assign(r.data);
  })
  .catch(function(e){
    response.value = e.response.data.message;
    console.log(e);
  });
}
</script>

<style src="./app.css" scoped></style>
<style>
  body {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 90vh;
  }
</style>

<template>
  <form class="div_fondo" @submit.prevent="show_response">
    <div>
      <input type="text" name="email" placeholder="EMAIL" v-model="email"/>
    </div>
    <div v-if="creating_user">
      <input type="text" name="name" placeholder="NAME" v-model="name"/>
    </div>
    <div>
      <input type="password" name="password" placeholder="PASSWORD" v-model="password"/>
    </div>
    <div v-if="!creating_user">
      <input type="submit" value="Login"/>
    </div>
    <div v-else>
      <input type="submit" value="Create"/>
    </div>
    <div v-if="response">
      <p>{{response}}</p>
    </div>
    <a v-if="!creating_user" href="user_create">Create user</a>
    <a v-else href="login">Go to login</a>
  </form>
</template>
