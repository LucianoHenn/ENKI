import { ifNotAuthenticated } from '../services/auth';

export default [
  {
    path: '/login',
    component: () => import('../layouts/auth-layout.vue'),
    beforeEnter: ifNotAuthenticated,
    children: [
      {
        path: '',
        name: 'login',
        component: () => import('../views/auth/login_boxed.vue'),
      }
    ]
  }
]
