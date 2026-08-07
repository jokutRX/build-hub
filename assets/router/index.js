import { createRouter, createWebHistory } from 'vue-router'
import SupplyRequestsView from '../views/SupplyRequestsView.vue'

const routes = [
  {
    path: '/',
    redirect: '/requests'
  },
  {
    path: '/requests',
    name: 'supply-requests',
    component: SupplyRequestsView
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

export default router