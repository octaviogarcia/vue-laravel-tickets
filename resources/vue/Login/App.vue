<script setup>
import { ref, computed } from 'vue';
import '/resources/css/style.css';
import Tooltip from '../components/Tooltip/App.vue';

const creating_user = !!blade_vars.creating_user;
const request = ref({});
const response = ref('');
const errors = ref({});

function submit(){
  for(const k of Object.keys({...errors.value})){
    errors.value[k] = '';
  }
  axios.post(window.location.href, {...request.value})
  .then(function(r){
    if(r.data.message)
      response.value = r.data.message;
    if(r.data.redirect)
      window.location.assign(r.data.redirect);
  })
  .catch(function(e){
    console.log(e);
    const resp_message = e.response.data.message;
    const resp_errors  = e.response.data.errors;
    if(!resp_errors && resp_message){
      response.value = resp_message;
      return;
    }
    for(const k of Object.keys(resp_errors)){
      errors.value[k] = resp_errors[k].join(' // ');
    }
  });
}

const email_confirmed = computed(function(){
  return request.value.email == request.value.c_email;
});
const password_confirmed = computed(function(){
  return request.value.password == request.value.c_password;
});
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
  <form class="div_fondo" @submit.prevent="submit">
    <div v-if="creating_user">
      <Tooltip :text="errors.name">
        <input type="text" name="name" placeholder="NAME" v-model="request.name" :errored="errors.name? true : null"/>
      </Tooltip>
    </div>
    <div>
      <Tooltip :text="errors.email">
        <input type="text" name="email" placeholder="EMAIL" v-model="request.email" :errored="errors.email? true : null"/>
      </Tooltip>
    </div>
    <div v-if="creating_user">
      <input type="text" placeholder="CONFIRM EMAIL" v-model="request.c_email" :errored="email_confirmed? null : true"/>
    </div>
    <div>
      <Tooltip :text="errors.password">
        <input type="password" name="password" placeholder="PASSWORD" v-model="request.password" :errored="errors.password? true : null"/>
      </Tooltip>
    </div>
    <div v-if="creating_user">
      <input type="password" name="password" placeholder="CONFIRM PASSWORD" v-model="request.c_password" :errored="password_confirmed? null : true"/>
    </div>
    <div v-if="!creating_user">
      <input type="submit" value="Login"/>
    </div>
    <div v-else>
      <input type="submit" value="Create" :disabled="email_confirmed && password_confirmed? null : true"/>
    </div>
    <div v-if="response">
      <p>{{response}}</p>
    </div>
    <a v-if="!creating_user" href="user_create">Create user</a>
    <a v-else href="login">Go to login</a>
  </form>
</template>
