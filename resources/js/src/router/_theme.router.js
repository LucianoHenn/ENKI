import AppLayout from '../theme/layouts/app-layout.vue';

export default [
  {
    path: '/theme',
    component: AppLayout,
    children: [
      {
        path: '',
        name: 'theme',
        component: () => import('../theme/views/index2.vue'),
      },
      {
        path: 'font-icons',
        name: 'theme-font-icons',
        component: () => import('../theme/views/font_icons.vue'),
      },
    ]
  },
];
