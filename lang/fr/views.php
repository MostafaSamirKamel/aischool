<?php

return [
    // Bulk Grade Entry Page - Blade Template
    'bulk_grade_entry' => [
        'student_list_heading' => 'Liste des étudiants (:count étudiants)',
        'table' => [
            'number' => '#',
            'student_name' => 'Nom de l\'étudiant',
            'email' => 'Email',
            'marks_obtained' => 'Notes obtenues',
            'remarks' => 'Remarques',
        ],
        'save_all' => 'Enregistrer toutes les notes',
        'no_students' => 'Aucun étudiant inscrit à cet examen',
    ],

    // Student Transcript Page - Blade Template
    'transcript' => [
        'student_info' => [
            'heading' => 'Informations sur l\'étudiant',
            'full_name' => 'Nom complet',
            'email' => 'Email',
            'phone' => 'Téléphone',
            'not_available' => 'Non disponible',
        ],
        'statistics' => [
            'heading' => 'Statistiques de performance',
            'total_exams' => 'Total des examens',
            'average_percentage' => 'Pourcentage moyen',
            'pass_fail' => 'Réussi / Échoué',
            'grade_distribution' => 'Distribution des notes',
            'highest_grade' => 'Note la plus élevée',
            'lowest_grade' => 'Note la plus basse',
        ],
        'grades_by_course' => [
            'heading' => 'Notes par cours',
            'course' => 'Cours',
            'exam' => 'Examen',
            'type' => 'Type',
            'marks' => 'Notes',
            'percentage' => 'Pourcentage',
            'grade' => 'Note',
            'date' => 'Date',
            'no_grades' => 'Aucune note enregistrée',
        ],
        'actions' => [
            'download_pdf' => 'Télécharger le relevé de notes (PDF)',
            'print' => 'Imprimer',
        ],
    ],

    // Issue Certificate Page - Blade Template
    'certificate' => [
        'preview_heading' => 'Aperçu du certificat',
        'certificate_info' => 'Informations sur le certificat',
        'student_name' => 'Nom de l\'étudiant',
        'course_name' => 'Nom du cours',
        'issue_date' => 'Date d\'émission',
        'generate_button' => 'Générer le certificat',
        'actions' => [
            'preview' => 'Aperçu',
            'download' => 'Télécharger',
            'print' => 'Imprimer',
        ],
    ],

    // PDF Templates
    'pdf' => [
        'transcript' => [
            'title' => 'Relevé de notes de l\'étudiant',
            'student_id' => 'ID de l\'étudiant',
            'student_name' => 'Nom de l\'étudiant',
            'issue_date' => 'Date d\'émission',
            'academic_record' => 'Dossier académique',
            'course' => 'Cours',
            'exam' => 'Examen',
            'marks' => 'Notes',
            'percentage' => 'Pourcentage %',
            'grade' => 'Note',
            'overall_statistics' => 'Statistiques générales',
            'total_courses' => 'Total des cours',
            'total_exams' => 'Total des examens',
            'average_percentage' => 'Pourcentage moyen',
            'signature' => 'Signature',
            'registrar' => 'Registraire de l\'université',
        ],
        'certificate' => [
            'title' => 'Certificat de réussite du cours',
            'awarded_to' => 'Ce certificat est décerné à',
            'completion_text' => 'Pour avoir réussi les exigences de',
            'course' => 'Cours',
            'date' => 'Date',
            'instructor' => 'Instructeur',
            'director' => 'Directeur',
            'seal' => 'Sceau',
        ],
    ],

    // Exam Types (for displays in views)
    'exam_types' => [
        'quiz' => 'Quiz',
        'midterm' => 'Examen partiel',
        'final' => 'Examen final',
        'assignment' => 'Devoir',
        'project' => 'Projet',
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
        'pass' => 'Réussi',
        'fail' => 'Échoué',
    ],
];
