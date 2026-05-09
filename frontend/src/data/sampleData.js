export const featureCards = [
  {
    title: 'Online Scholarship Application',
    description: 'Students submit scholarship applications through guided digital forms.',
  },
  {
    title: 'Document Upload',
    description: 'Required files are organized by document type with visible review status.',
  },
  {
    title: 'Real-Time Status Tracking',
    description: 'Applicants can monitor each step from submission to final decision.',
  },
  {
    title: 'Notifications and Announcements',
    description: 'Important reminders and program updates are available in one place.',
  },
  {
    title: 'Predictive Analytics',
    description: 'Officers can identify application trends and scholars who may need support.',
  },
  {
    title: 'Reports and Dashboards',
    description: 'Summary cards, tables, and export-ready previews support faster decisions.',
  },
]

export const studentProfile = {
  name: 'Alyssa Mendoza',
  email: 'alyssa.mendoza@college.edu',
  phone: '0917 245 8831',
  program: 'Bachelor of Science in Information Technology',
  yearLevel: '3rd Year',
  scholarshipProgram: 'City Academic Excellence Grant',
  address: 'San Isidro, Cabanatuan City, Nueva Ecija',
}

export const studentDashboardStats = [
  {
    title: 'Application Status',
    value: 'Under Review',
    subtitle: 'Last updated today',
    tone: 'blue',
  },
  {
    title: 'Submitted Documents',
    value: '4 of 5',
    subtitle: 'One file pending upload',
    tone: 'indigo',
  },
  {
    title: 'Compliance Status',
    value: 'Compliant',
    subtitle: 'No active requirements',
    tone: 'green',
  },
  {
    title: 'Latest Announcement',
    value: 'Renewal Opens',
    subtitle: 'May 15, 2026',
    tone: 'amber',
  },
]

export const progressSteps = ['Submitted', 'Under Review', 'Approved / Rejected']

export const recentNotifications = [
  {
    title: 'Document review in progress',
    message: 'Your report card is queued for verification by the scholarship office.',
    time: '10 minutes ago',
  },
  {
    title: 'Application received',
    message: 'Your application for City Academic Excellence Grant was submitted successfully.',
    time: 'Yesterday',
  },
  {
    title: 'Announcement posted',
    message: 'Scholarship renewal window starts on May 15, 2026.',
    time: '2 days ago',
  },
]

export const uploadDocuments = [
  {
    documentType: 'Certificate of Indigency',
    fileName: 'indigency-certificate.pdf',
    status: 'Verified',
  },
  {
    documentType: 'Report Card',
    fileName: 'grade-report-sem1.pdf',
    status: 'Uploaded',
  },
  {
    documentType: 'Application Form',
    fileName: 'scholarship-application.pdf',
    status: 'Uploaded',
  },
  {
    documentType: 'Valid ID',
    fileName: 'student-id-front.pdf',
    status: 'Pending',
  },
  {
    documentType: 'Other Supporting Documents',
    fileName: 'parent-income-statement.pdf',
    status: 'For Revision',
  },
]

export const applicationTimeline = [
  {
    title: 'Application Submitted',
    date: 'May 2, 2026',
    status: 'Completed',
    detail: 'Application form was submitted by the applicant.',
  },
  {
    title: 'Documents Uploaded',
    date: 'May 3, 2026',
    status: 'Completed',
    detail: 'Four required documents were uploaded for review.',
  },
  {
    title: 'Under Review',
    date: 'May 8, 2026',
    status: 'Current',
    detail: 'Scholarship officer is validating eligibility and documentary requirements.',
  },
  {
    title: 'Approved / Rejected',
    date: 'Pending',
    status: 'Pending',
    detail: 'Final result will be posted after assessment.',
  },
]

export const announcements = [
  {
    title: 'Renewal Application Window',
    audience: 'All Scholars',
    date: 'May 15, 2026',
    message: 'Renewal applications for continuing scholars will open next week. Prepare your updated grades and certificate of enrollment.',
  },
  {
    title: 'Document Verification Schedule',
    audience: 'Applicants',
    date: 'May 20, 2026',
    message: 'Applicants with pending files should monitor their document status and submit revisions before the deadline.',
  },
  {
    title: 'Orientation for Approved Scholars',
    audience: 'Approved Scholars',
    date: 'June 1, 2026',
    message: 'Approved scholars are invited to attend the scholarship orientation at the college auditorium.',
  },
]

export const adminDashboardStats = [
  {
    title: 'Total Applicants',
    value: '1,248',
    subtitle: '+18% from last cycle',
    tone: 'blue',
  },
  {
    title: 'Pending Applications',
    value: '312',
    subtitle: 'Needs review',
    tone: 'amber',
  },
  {
    title: 'Approved Scholars',
    value: '684',
    subtitle: 'Active scholarship grants',
    tone: 'green',
  },
  {
    title: 'At-Risk Scholars',
    value: '47',
    subtitle: 'Flagged by monitoring',
    tone: 'red',
  },
]

export const recentActivities = [
  {
    activity: 'Approved application',
    user: 'Marcus Reyes',
    program: 'Academic Excellence Grant',
    date: 'May 9, 2026',
  },
  {
    activity: 'Requested document revision',
    user: 'Janelle Cruz',
    program: 'Financial Assistance Program',
    date: 'May 9, 2026',
  },
  {
    activity: 'Verified report card',
    user: 'Rafael Santos',
    program: 'STEM Scholarship',
    date: 'May 8, 2026',
  },
]

export const applications = [
  {
    id: 'app-001',
    applicantName: 'Alyssa Mendoza',
    email: 'alyssa.mendoza@college.edu',
    phone: '0917 245 8831',
    program: 'Academic Excellence Grant',
    course: 'BS Information Technology',
    yearLevel: '3rd Year',
    gpa: '1.45',
    address: 'San Isidro, Cabanatuan City, Nueva Ecija',
    reason: 'I am applying to continue my studies with less financial burden while maintaining strong academic performance.',
    dateSubmitted: 'May 2, 2026',
    status: 'Under Review',
    remarks: 'Application is complete. Report card and valid ID are under verification.',
  },
  {
    id: 'app-002',
    applicantName: 'Marcus Reyes',
    email: 'marcus.reyes@college.edu',
    phone: '0918 552 1940',
    program: 'Financial Assistance Program',
    course: 'BS Computer Science',
    yearLevel: '2nd Year',
    gpa: '1.68',
    address: 'Bantug, Science City of Munoz, Nueva Ecija',
    reason: 'I need financial support for school fees and learning materials while my family recovers from recent expenses.',
    dateSubmitted: 'May 3, 2026',
    status: 'Approved',
    remarks: 'Approved for the current scholarship cycle.',
  },
  {
    id: 'app-003',
    applicantName: 'Janelle Cruz',
    email: 'janelle.cruz@college.edu',
    phone: '0916 733 6182',
    program: 'Academic Excellence Grant',
    course: 'BS Information Systems',
    yearLevel: '4th Year',
    gpa: '1.72',
    address: 'Talavera, Nueva Ecija',
    reason: 'The scholarship will help me finish my final year and complete my capstone requirements.',
    dateSubmitted: 'May 4, 2026',
    status: 'For Revision',
    remarks: 'Valid ID copy is blurred. Request a clearer upload.',
  },
  {
    id: 'app-004',
    applicantName: 'Rafael Santos',
    email: 'rafael.santos@college.edu',
    phone: '0915 902 3371',
    program: 'STEM Scholarship',
    course: 'BS Data Science',
    yearLevel: '1st Year',
    gpa: '2.15',
    address: 'Palayan City, Nueva Ecija',
    reason: 'I want to pursue a technology career and need support for transportation and school supplies.',
    dateSubmitted: 'May 5, 2026',
    status: 'Rejected',
    remarks: 'Applicant did not meet the minimum GPA requirement for this program.',
  },
  {
    id: 'app-005',
    applicantName: 'Bianca Flores',
    email: 'bianca.flores@college.edu',
    phone: '0917 481 2047',
    program: 'Leadership Grant',
    course: 'BS Information Technology',
    yearLevel: '3rd Year',
    gpa: '1.82',
    address: 'Gapan City, Nueva Ecija',
    reason: 'I serve in student organizations and want to continue contributing while sustaining my studies.',
    dateSubmitted: 'May 7, 2026',
    status: 'Pending',
    remarks: 'Awaiting initial screening.',
  },
]

export const submittedDocuments = [
  {
    id: 'doc-001',
    studentName: 'Alyssa Mendoza',
    program: 'Academic Excellence Grant',
    documentType: 'Certificate of Indigency',
    fileName: 'indigency-certificate.pdf',
    fileSize: '1.2 MB',
    fileType: 'PDF',
    uploadDate: 'May 3, 2026',
    verificationStatus: 'Verified',
    remarks: 'Document is readable and matches applicant details.',
  },
  {
    id: 'doc-002',
    studentName: 'Marcus Reyes',
    program: 'Financial Assistance Program',
    documentType: 'Report Card',
    fileName: 'grade-report-sem1.pdf',
    fileSize: '856 KB',
    fileType: 'PDF',
    uploadDate: 'May 4, 2026',
    verificationStatus: 'Uploaded',
    remarks: 'Awaiting grade validation by the scholarship office.',
  },
  {
    id: 'doc-003',
    studentName: 'Janelle Cruz',
    program: 'Academic Excellence Grant',
    documentType: 'Valid ID',
    fileName: 'student-id.jpg',
    fileSize: '640 KB',
    fileType: 'JPG',
    uploadDate: 'May 4, 2026',
    verificationStatus: 'For Revision',
    remarks: 'Image is blurred. Request a clearer copy of the ID.',
  },
  {
    id: 'doc-004',
    studentName: 'Rafael Santos',
    program: 'STEM Scholarship',
    documentType: 'Application Form',
    fileName: 'application-form.pdf',
    fileSize: '1.8 MB',
    fileType: 'PDF',
    uploadDate: 'May 5, 2026',
    verificationStatus: 'Pending',
    remarks: 'Document is waiting for initial review.',
  },
]

export const scholarships = [
  {
    id: 'sch-001',
    scholarshipName: 'Academic Excellence Grant',
    description: 'Merit-based scholarship for students with strong academic performance.',
    eligibilityRequirements: 'Minimum GPA of 1.75, good moral standing, and no incomplete grades.',
    requiredDocuments: 'Report Card, Certificate of Enrollment, Valid ID, Application Form',
    deadline: 'June 15, 2026',
    availableSlots: '120',
    announcementDetails: 'Applications are open for qualified students with excellent academic standing.',
    status: 'Open',
    scholarshipType: 'Merit-Based',
    academicYear: '2026-2027',
    semester: '1st Semester',
    minimumGpa: '1.75',
    yearLevelAllowed: '2nd Year to 4th Year',
    programAllowed: 'All Programs',
    contactPerson: 'Dr. Camille Navarro',
    datePosted: 'May 9, 2026',
  },
  {
    id: 'sch-002',
    scholarshipName: 'Financial Assistance Program',
    description: 'Need-based assistance for students who require financial support.',
    eligibilityRequirements: 'Certificate of Indigency, enrolled status, and satisfactory academic standing.',
    requiredDocuments: 'Certificate of Indigency, Parent Income Statement, Report Card, Valid ID',
    deadline: 'June 30, 2026',
    availableSlots: '80',
    announcementDetails: 'Submit complete requirements before the deadline for screening.',
    status: 'Open',
    scholarshipType: 'Need-Based',
    academicYear: '2026-2027',
    semester: '1st Semester',
    minimumGpa: '2.25',
    yearLevelAllowed: 'All Year Levels',
    programAllowed: 'All Programs',
    contactPerson: 'Scholarship Office',
    datePosted: 'May 12, 2026',
  },
  {
    id: 'sch-003',
    scholarshipName: 'STEM Scholarship',
    description: 'Scholarship support for students in technology and science programs.',
    eligibilityRequirements: 'STEM-related program, minimum GPA of 1.90, and active enrollment.',
    requiredDocuments: 'Application Form, Report Card, Certificate of Enrollment, Recommendation Letter',
    deadline: 'July 10, 2026',
    availableSlots: '50',
    announcementDetails: 'Priority will be given to applicants with technology research or project participation.',
    status: 'Draft',
    scholarshipType: 'Program-Based',
    academicYear: '2026-2027',
    semester: '1st Semester',
    minimumGpa: '1.90',
    yearLevelAllowed: '1st Year to 4th Year',
    programAllowed: 'BSIT, BSCS, BSIS, BS Data Science',
    contactPerson: 'Prof. Miguel Santos',
    datePosted: 'Draft',
  },
]

export const complianceRecords = [
  {
    scholarName: 'Marcus Reyes',
    gpa: '1.42',
    complianceScore: '96%',
    complianceStatus: 'Compliant',
    riskLevel: 'Low',
  },
  {
    scholarName: 'Bianca Flores',
    gpa: '1.89',
    complianceScore: '82%',
    complianceStatus: 'Needs Monitoring',
    riskLevel: 'Medium',
  },
  {
    scholarName: 'Noel Garcia',
    gpa: '2.45',
    complianceScore: '61%',
    complianceStatus: 'At Risk',
    riskLevel: 'High',
  },
  {
    scholarName: 'Rina Bautista',
    gpa: '1.75',
    complianceScore: '88%',
    complianceStatus: 'Compliant',
    riskLevel: 'Low',
  },
]

export const analyticsCards = [
  {
    title: 'Forecasted Applicants',
    value: '1,520',
    subtitle: 'Expected next cycle',
    tone: 'blue',
  },
  {
    title: 'Approval Probability',
    value: '58%',
    subtitle: 'Based on current eligibility',
    tone: 'indigo',
  },
  {
    title: 'At-Risk Scholars',
    value: '47',
    subtitle: 'Require intervention',
    tone: 'red',
  },
  {
    title: 'Compliance Risk Overview',
    value: 'Medium',
    subtitle: 'Campus-wide trend',
    tone: 'amber',
  },
]

export const predictedAtRiskScholars = [
  {
    name: 'Noel Garcia',
    gpaTrend: 'Declining',
    complianceScore: '61%',
    riskScore: '89',
    forecastLabel: 'High Risk',
  },
  {
    name: 'Bianca Flores',
    gpaTrend: 'Stable',
    complianceScore: '82%',
    riskScore: '64',
    forecastLabel: 'Medium Risk',
  },
  {
    name: 'Jules Aquino',
    gpaTrend: 'Declining',
    complianceScore: '75%',
    riskScore: '71',
    forecastLabel: 'Medium Risk',
  },
]

export const reportPreviewRows = [
  {
    applicantName: 'Alyssa Mendoza',
    program: 'Academic Excellence Grant',
    status: 'Under Review',
    submitted: 'May 2, 2026',
  },
  {
    applicantName: 'Marcus Reyes',
    program: 'Financial Assistance Program',
    status: 'Approved',
    submitted: 'May 3, 2026',
  },
  {
    applicantName: 'Janelle Cruz',
    program: 'Academic Excellence Grant',
    status: 'For Revision',
    submitted: 'May 4, 2026',
  },
]
