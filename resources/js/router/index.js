import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../stores/auth';

import LoginPage from '../pages/LoginPage.vue';
import DashboardPage from '../pages/DashboardPage.vue';
import ExamsPage from '../pages/ExamsPage.vue';
import NotFoundPage from '../pages/NotFoundPage.vue';
import ProfilePage from '../pages/ProfilePage.vue';
import AdminUsersPage from '../pages/admin/AdminUsersPage.vue';
import AdminClassesPage from '../pages/admin/AdminClassesPage.vue';
import AdminExamsPage from '../pages/admin/AdminExamsPage.vue';
import AdminQuestionsPage from '../pages/admin/AdminQuestionsPage.vue';
import AdminAssignPage from '../pages/admin/AdminAssignPage.vue';
import AdminGradingPendingPage from '../pages/admin/AdminGradingPendingPage.vue';
import AdminGradingExamUsersPage from '../pages/admin/AdminGradingExamUsersPage.vue';
import AdminGradingDetailPage from '../pages/admin/AdminGradingDetailPage.vue';
import AdminExamMonitorPage from '../pages/admin/AdminExamMonitorPage.vue';
import AdminSettingsPage from '../pages/admin/AdminSettingsPage.vue';
import TeacherClassesPage from '../pages/teacher/TeacherClassesPage.vue';
import TeacherStudentsPage from '../pages/teacher/TeacherStudentsPage.vue';
import TeacherExamsPage from '../pages/teacher/TeacherExamsPage.vue';
import TeacherQuestionsPage from '../pages/teacher/TeacherQuestionsPage.vue';
import TeacherAssignPage from '../pages/teacher/TeacherAssignPage.vue';
import TeacherAttendancePage from '../pages/teacher/TeacherAttendancePage.vue';
import TeacherAttendanceStatsPage from '../pages/teacher/TeacherAttendanceStatsPage.vue';
import TeacherExamMonitorPage from '../pages/teacher/TeacherExamMonitorPage.vue';
import StudentExamsPage from '../pages/student/StudentExamsPage.vue';
import StudentTakeExamPage from '../pages/student/StudentTakeExamPage.vue';
import StudentResultsPage from '../pages/student/StudentResultsPage.vue';
import StudentResultDetailPage from '../pages/student/StudentResultDetailPage.vue';

const routes = [
    {
        path: '/app/login',
        name: 'login',
        component: LoginPage,
        meta: { guestOnly: true },
    },
    {
        path: '/app',
        redirect: '/app/login',
    },
    {
        path: '/app/admin/dashboard',
        name: 'dashboard',
        component: DashboardPage,
        meta: { requiresAuth: true, requiresRole: ['admin'] },
    },
    {
        path: '/app/teacher/dashboard',
        name: 'teacher-dashboard',
        component: DashboardPage,
        meta: { requiresAuth: true, requiresRole: ['teacher'] },
    },
    {
        path: '/app/student/dashboard',
        name: 'student-dashboard',
        component: DashboardPage,
        meta: { requiresAuth: true, requiresRole: ['student'] },
    },
    {
        path: '/app/exams',
        name: 'exams',
        component: ExamsPage,
        meta: { requiresAuth: true },
    },
    {
        path: '/app/teacher/classes',
        name: 'teacher-classes',
        component: TeacherClassesPage,
        meta: { requiresAuth: true, requiresRole: ['teacher'] },
    },
    {
        path: '/app/teacher/students',
        name: 'teacher-students',
        component: TeacherStudentsPage,
        meta: { requiresAuth: true, requiresRole: ['teacher'] },
    },
    {
        path: '/app/teacher/exams',
        name: 'teacher-exams',
        component: TeacherExamsPage,
        meta: { requiresAuth: true, requiresRole: ['teacher'] },
    },
    {
        path: '/app/teacher/exams/:examId/questions',
        name: 'teacher-exam-questions',
        component: TeacherQuestionsPage,
        meta: { requiresAuth: true, requiresRole: ['teacher'] },
    },
    {
        path: '/app/teacher/exams/:examId/assign',
        name: 'teacher-exam-assign',
        component: TeacherAssignPage,
        meta: { requiresAuth: true, requiresRole: ['teacher'] },
    },
    {
        path: '/app/teacher/attendances',
        name: 'teacher-attendances',
        component: TeacherAttendancePage,
        meta: { requiresAuth: true, requiresRole: ['teacher'] },
    },
    {
        path: '/app/teacher/attendances/statistics',
        name: 'teacher-attendance-statistics',
        component: TeacherAttendanceStatsPage,
        meta: { requiresAuth: true, requiresRole: ['teacher'] },
    },
    {
        path: '/app/teacher/monitor',
        name: 'teacher-exam-monitor',
        component: TeacherExamMonitorPage,
        meta: { requiresAuth: true, requiresRole: ['teacher'] },
    },
    {
        path: '/app/student/exams',
        name: 'student-exams',
        component: StudentExamsPage,
        meta: { requiresAuth: true, requiresRole: ['student'] },
    },
    {
        path: '/app/student/exams/:examId/take',
        name: 'student-exam-take',
        component: StudentTakeExamPage,
        meta: { requiresAuth: true, requiresRole: ['student'] },
    },
    {
        path: '/app/student/results',
        name: 'student-results',
        component: StudentResultsPage,
        meta: { requiresAuth: true, requiresRole: ['student'] },
    },
    {
        path: '/app/student/results/:resultId',
        name: 'student-result-detail',
        component: StudentResultDetailPage,
        meta: { requiresAuth: true, requiresRole: ['student'] },
    },
    {
        path: '/app/profile',
        name: 'profile',
        component: ProfilePage,
        meta: { requiresAuth: true },
    },
    {
        path: '/app/admin/users',
        name: 'admin-users',
        component: AdminUsersPage,
        meta: { requiresAuth: true, requiresRole: ['admin'] },
    },
    {
        path: '/app/admin/classes',
        name: 'admin-classes',
        component: AdminClassesPage,
        meta: { requiresAuth: true, requiresRole: ['admin'] },
    },
    {
        path: '/app/admin/exams',
        name: 'admin-exams',
        component: AdminExamsPage,
        meta: { requiresAuth: true, requiresRole: ['admin'] },
    },
    {
        path: '/app/admin/exams/:examId/questions',
        name: 'admin-exam-questions',
        component: AdminQuestionsPage,
        meta: { requiresAuth: true, requiresRole: ['admin'] },
    },
    {
        path: '/app/admin/exams/:examId/assign',
        name: 'admin-exam-assign',
        component: AdminAssignPage,
        meta: { requiresAuth: true, requiresRole: ['admin'] },
    },
    {
        path: '/app/admin/grading/pending',
        name: 'admin-grading-pending',
        component: AdminGradingPendingPage,
        meta: { requiresAuth: true, requiresRole: ['admin'] },
    },
    {
        path: '/app/admin/grading/exams/:examId/users',
        name: 'admin-grading-exam-users',
        component: AdminGradingExamUsersPage,
        meta: { requiresAuth: true, requiresRole: ['admin'] },
    },
    {
        path: '/app/admin/grading/:userExamId',
        name: 'admin-grading-detail',
        component: AdminGradingDetailPage,
        meta: { requiresAuth: true, requiresRole: ['admin'] },
    },
    {
        path: '/app/admin/monitor',
        name: 'admin-exam-monitor',
        component: AdminExamMonitorPage,
        meta: { requiresAuth: true, requiresRole: ['admin'] },
    },
    {
        path: '/app/admin/settings',
        name: 'admin-settings',
        component: AdminSettingsPage,
        meta: { requiresAuth: true, requiresRole: ['admin'] },
    },
    {
        path: '/app/:role(admin|teacher|student)',
        redirect: '/app/dashboard',
    },
    {
        path: '/app/:pathMatch(.*)*',
        name: 'not-found',
        component: NotFoundPage,
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach(async (to) => {
    const authStore = useAuthStore();

    if (!authStore.isLoaded) {
        await authStore.fetchMe();
    }

    if (to.meta.requiresAuth && !authStore.isAuthenticated) {
        return { name: 'login', query: { redirect: to.fullPath } };
    }

    if (to.meta.requiresRole?.length && !to.meta.requiresRole.includes(authStore.user?.role)) {
        return authStore.roleHome;
    }

    if (to.meta.guestOnly && authStore.isAuthenticated) {
        return authStore.roleHome;
    }

    return true;
});

export default router;


