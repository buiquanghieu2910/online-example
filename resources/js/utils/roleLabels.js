export const roleLabelMap = {
    admin: 'Quản trị viên',
    teacher: 'Giáo viên',
    student: 'Học sinh',
};

export function getRoleLabel(role) {
    return roleLabelMap[role] || 'Người dùng';
}

