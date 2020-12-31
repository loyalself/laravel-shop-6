
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue');

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

//Vue.component('example-component', require('./components/ExampleComponent.vue'));
//Vue.component('SelectDistrict');

// 此处需在引入 Vue 之后引入
require('./components/SelectDistrict');  //3.6. 新建收货地址 添加
require('./components/UserAddressesCreateAndEdit'); //3.6. 新建收货地址 添加

const app = new Vue({
    el: '#app'
});
