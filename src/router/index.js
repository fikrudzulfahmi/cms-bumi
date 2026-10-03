import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const routes = [
  {
    path: '/',
    component: () => import('@/layouts/PublicLayout.vue'),
    children: [
      { path: '', name: 'beranda', component: () => import('@/views/Beranda.vue') },
      { path: 'profil', name: 'profil', component: () => import('@/views/Profil.vue') },
      { path: 'berita', name: 'berita', component: () => import('@/views/BeritaList.vue') },
      { path: 'berita/:slug', name: 'berita-detail', component: () => import('@/views/BeritaDetail.vue') },
      { path: 'jurusan', name: 'jurusan', component: () => import('@/views/Jurusan.vue') },
      { path: 'jurusan/:slug', name: 'jurusan-detail', component: () => import('@/views/JurusanDetail.vue') },
      { path: 'layanan', name: 'layanan', component: () => import('@/views/Layanan.vue') },
    ],
  },
  {
    path: '/admin/login',
    name: 'admin-login',
    component: () => import('@/views/admin/AdminLogin.vue'),
  },
  {
    path: '/admin',
    component: () => import('@/layouts/AdminLayout.vue'),
    meta: { requiresAuth: true },
    children: [
      { path: '', name: 'admin-dashboard', component: () => import('@/views/admin/Dashboard.vue') },
      { path: 'pengaturan', name: 'admin-settings', component: () => import('@/views/admin/ManageSettings.vue') },
      { path: 'profil', name: 'admin-profil', component: () => import('@/views/admin/ManageProfil.vue') },
      { path: 'guru', name: 'admin-guru', component: () => import('@/views/admin/ManageGuru.vue') },
      { path: 'berita', name: 'admin-berita', component: () => import('@/views/admin/ManageBerita.vue') },
      { path: 'umpan-balik', name: 'admin-umpan-balik', component: () => import('@/views/admin/ManageUmpanBalik.vue') },
      { path: 'jurusan', name: 'admin-jurusan', component: () => import('@/views/admin/ManageJurusan.vue') },
      { path: 'fasilitas', name: 'admin-fasilitas', component: () => import('@/views/admin/ManageFasilitas.vue') },
      { path: 'ekstrakurikuler', name: 'admin-ekstra', component: () => import('@/views/admin/ManageEkstra.vue') },
      { path: 'galeri', name: 'admin-galeri', component: () => import('@/views/admin/ManageGaleri.vue') },
    ],
  },
  { path: '/:pathMatch(.*)*', redirect: '/' },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) return savedPosition
    return { top: 0 }
  },
})

router.beforeEach((to) => {
  const auth = useAuthStore()
  if (to.meta.requiresAuth && !auth.isAuthenticated) {
    return { name: 'admin-login', query: { redirect: to.fullPath } }
  }
  if (to.name === 'admin-login' && auth.isAuthenticated) {
    return { name: 'admin-dashboard' }
  }
})

export default router
