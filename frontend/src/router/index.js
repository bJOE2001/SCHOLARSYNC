import { createRouter, createWebHistory } from 'vue-router'
import LandingPage from '../pages/LandingPage.vue'
import HowToApplyPage from '../pages/HowToApplyPage.vue'
import LoginPage from '../pages/LoginPage.vue'
import StudentRegistrationPage from '../pages/StudentRegistrationPage.vue'
import StudentDashboardPage from '../pages/student/StudentDashboardPage.vue'
import ScholarshipApplicationPage from '../pages/student/ScholarshipApplicationPage.vue'
import DocumentUploadPage from '../pages/student/DocumentUploadPage.vue'
import ApplicationStatusPage from '../pages/student/ApplicationStatusPage.vue'
import StudentProfilePage from '../pages/student/StudentProfilePage.vue'
import AdminDashboardPage from '../pages/admin/AdminDashboardPage.vue'
import ScholarshipManagementPage from '../pages/admin/ScholarshipManagementPage.vue'
import ApplicationsManagementPage from '../pages/admin/ApplicationsManagementPage.vue'
import ApplicationViewPage from '../pages/admin/ApplicationViewPage.vue'
import DocumentReviewPage from '../pages/admin/DocumentReviewPage.vue'
import DocumentViewPage from '../pages/admin/DocumentViewPage.vue'
import ComplianceMonitoringPage from '../pages/admin/ComplianceMonitoringPage.vue'
import PredictiveAnalyticsPage from '../pages/admin/PredictiveAnalyticsPage.vue'
import ReportsPage from '../pages/admin/ReportsPage.vue'
import ScholarsPage from '../pages/admin/ScholarsPage.vue'
import AdminProfilePage from '../pages/admin/AdminProfilePage.vue'
import AnnouncementsPage from '../pages/AnnouncementsPage.vue'

const routes = [
  { path: '/', name: 'landing', component: LandingPage },
  { path: '/how-to-apply', name: 'how-to-apply', component: HowToApplyPage },
  { path: '/login', name: 'login', component: LoginPage },
  { path: '/register', name: 'register', component: StudentRegistrationPage },
  { path: '/student', redirect: '/student/dashboard' },
  { path: '/student/dashboard', name: 'student-dashboard', component: StudentDashboardPage },
  { path: '/student/application', name: 'student-application', component: ScholarshipApplicationPage },
  { path: '/student/documents', name: 'student-documents', component: DocumentUploadPage },
  { path: '/student/status', name: 'student-status', component: ApplicationStatusPage },
  {
    path: '/student/announcements',
    name: 'student-announcements',
    component: AnnouncementsPage,
    meta: { role: 'student' },
  },
  { path: '/student/profile', name: 'student-profile', component: StudentProfilePage },
  { path: '/admin', redirect: '/admin/dashboard' },
  { path: '/admin/dashboard', name: 'admin-dashboard', component: AdminDashboardPage },
  { path: '/admin/scholarships', name: 'admin-scholarships', component: ScholarshipManagementPage },
  { path: '/admin/applications', name: 'admin-applications', component: ApplicationsManagementPage },
  { path: '/admin/applications/:id', name: 'admin-application-view', component: ApplicationViewPage },
  { path: '/admin/documents', name: 'admin-documents', component: DocumentReviewPage },
  { path: '/admin/documents/:id', name: 'admin-document-view', component: DocumentViewPage },
  { path: '/admin/scholars', name: 'admin-scholars', component: ScholarsPage },
  { path: '/admin/compliance', name: 'admin-compliance', component: ComplianceMonitoringPage },
  { path: '/admin/analytics', name: 'admin-analytics', component: PredictiveAnalyticsPage },
  { path: '/admin/reports', name: 'admin-reports', component: ReportsPage },
  { path: '/admin/profile', name: 'admin-profile', component: AdminProfilePage },
  {
    path: '/admin/announcements',
    name: 'admin-announcements',
    component: AnnouncementsPage,
    meta: { role: 'admin' },
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior() {
    return { top: 0 }
  },
})

export default router
