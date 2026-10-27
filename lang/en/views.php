<?php

return [
    // Bulk Grade Entry Page - Blade Template
    'bulk_grade_entry' => [
        'student_list_heading' => 'Student List (:count students)',
        'table' => [
            'number' => '#',
            'student_name' => 'Student Name',
            'email' => 'Email',
            'marks_obtained' => 'Marks Obtained',
            'remarks' => 'Remarks',
        ],
        'save_all' => 'Save All Grades',
        'no_students' => 'No students enrolled in this exam',
    ],

    // Student Transcript Page - Blade Template
    'transcript' => [
        'student_info' => [
            'heading' => 'Student Information',
            'full_name' => 'Full Name',
            'email' => 'Email',
            'phone' => 'Phone',
            'not_available' => 'Not Available',
        ],
        'statistics' => [
            'heading' => 'Performance Statistics',
            'total_exams' => 'Total Exams',
            'average_percentage' => 'Average Percentage',
            'pass_fail' => 'Pass / Fail',
            'grade_distribution' => 'Grade Distribution',
            'highest_grade' => 'Highest Grade',
            'lowest_grade' => 'Lowest Grade',
        ],
        'grades_by_course' => [
            'heading' => 'Grades by Course',
            'course' => 'Course',
            'exam' => 'Exam',
            'type' => 'Type',
            'marks' => 'Marks',
            'percentage' => 'Percentage',
            'grade' => 'Grade',
            'date' => 'Date',
            'no_grades' => 'No grades recorded',
        ],
        'actions' => [
            'download_pdf' => 'Download Transcript (PDF)',
            'print' => 'Print',
        ],
    ],

    // Issue Certificate Page - Blade Template
    'certificate' => [
        'preview_heading' => 'Certificate Preview',
        'certificate_info' => 'Certificate Information',
        'student_name' => 'Student Name',
        'course_name' => 'Course Name',
        'issue_date' => 'Issue Date',
        'generate_button' => 'Generate Certificate',
        'actions' => [
            'preview' => 'Preview',
            'download' => 'Download',
            'print' => 'Print',
        ],
    ],

    // PDF Templates
    'pdf' => [
        'transcript' => [
            'title' => 'Student Transcript',
            'student_id' => 'Student ID',
            'student_name' => 'Student Name',
            'issue_date' => 'Issue Date',
            'academic_record' => 'Academic Record',
            'course' => 'Course',
            'exam' => 'Exam',
            'marks' => 'Marks',
            'percentage' => 'Percentage %',
            'grade' => 'Grade',
            'overall_statistics' => 'Overall Statistics',
            'total_courses' => 'Total Courses',
            'total_exams' => 'Total Exams',
            'average_percentage' => 'Average Percentage',
            'signature' => 'Signature',
            'registrar' => 'University Registrar',
        ],
        'certificate' => [
            'title' => 'Course Completion Certificate',
            'awarded_to' => 'This certificate is awarded to',
            'completion_text' => 'For successfully completing the requirements of',
            'course' => 'Course',
            'date' => 'Date',
            'instructor' => 'Instructor',
            'director' => 'Director',
            'seal' => 'Seal',
        ],
    ],

    // Exam Types (for displays in views)
    'exam_types' => [
        'quiz' => 'Quiz',
        'midterm' => 'Midterm Exam',
        'final' => 'Final Exam',
        'assignment' => 'Assignment',
        'project' => 'Project',
    ],

    // Grade Labels
    'grades' => [
        'A+' => 'A+',
        'A' => 'A',
        'B+' => 'B+',
        'B' => 'B',
        'C+' => 'C+',
        'C' => 'C',
        'D' => 'D',
        'F' => 'F',
    ],

    // Status Labels
    'status' => [
        'pass' => 'Pass',
        'fail' => 'Fail',
    ],
];
