<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'AI School') }} - نظام إدارة المدرسة الذكي</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&family=Alexandria:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="icon" type="image/svg+xml" href="{{ asset('student-assets/icon.svg') }}">

    <style>
        :root {
            --ai-primary: #364FAC;
            --ai-primary-hover: #293C85;
            --ai-accent: #58BA47;
            --ai-accent-hover: #459637;
            --ai-bg: #F5F7FC;
            --ai-card: #FFFFFF;
            --ai-text: #18243D;
            --ai-muted: #64748B;
            --ai-line: #E5EAF3;
            --ai-shadow: 0 10px 30px -4px rgba(24, 36, 61, 0.08);
            --ai-shadow-hover: 0 16px 40px -4px rgba(54, 79, 172, 0.16);
            --font-family: 'Cairo', 'Alexandria', system-ui, sans-serif;
        }

        @media (prefers-color-scheme: dark) {
            :root {
                --ai-bg: #0F172A;
                --ai-card: #1E293B;
                --ai-text: #F8FAFC;
                --ai-muted: #94A3B8;
                --ai-line: #2A374F;
                --ai-shadow: 0 10px 30px -4px rgba(0, 0, 0, 0.4);
                --ai-shadow-hover: 0 16px 40px -4px rgba(54, 79, 172, 0.35);
            }
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: var(--font-family);
            background: var(--ai-bg);
            color: var(--ai-text);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
            position: relative;
            overflow-x: hidden;
        }

        /* Subtle Background Atmosphere */
        .bg-glow-top {
            position: absolute;
            top: -100px;
            right: 15%;
            width: 450px;
            height: 450px;
            background: radial-gradient(circle, rgba(54, 79, 172, 0.12) 0%, rgba(54, 79, 172, 0) 70%);
            border-radius: 50%;
            pointer-events: none;
        }

        .bg-glow-bottom {
            position: absolute;
            bottom: -80px;
            left: 10%;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(88, 186, 71, 0.14) 0%, rgba(88, 186, 71, 0) 70%);
            border-radius: 50%;
            pointer-events: none;
        }

        .container {
            width: 100%;
            max-width: 1100px;
            position: relative;
            z-index: 10;
        }

        /* Hero Header */
        .header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .logo-wrap {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 1.25rem;
            background: var(--ai-card);
            border: 1px solid var(--ai-line);
            padding: 8px 18px 8px 12px;
            border-radius: 999px;
            box-shadow: 0 4px 16px rgba(24, 36, 61, 0.04);
        }

        .logo-badge {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 3px 8px rgba(54, 79, 172, 0.25);
        }

        .logo-badge svg {
            width: 100%;
            height: 100%;
        }

        .logo-name {
            font-size: 1.25rem;
            font-weight: 900;
            color: var(--ai-primary);
            letter-spacing: -0.02em;
        }

        .header h1 {
            font-size: 2.5rem;
            font-weight: 900;
            letter-spacing: -0.03em;
            margin-bottom: 0.75rem;
            color: var(--ai-text);
        }

        .header p {
            font-size: 1.15rem;
            color: var(--ai-muted);
            font-weight: 600;
        }

        .header-hint {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: 1rem;
            font-size: 0.88rem;
            color: var(--ai-accent);
            font-weight: 700;
            background: rgba(88, 186, 71, 0.1);
            padding: 6px 14px;
            border-radius: 999px;
        }

        /* Role Panels Grid */
        .grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.25rem;
            margin-bottom: 2.75rem;
        }

        .card {
            background: var(--ai-card);
            border: 1px solid var(--ai-line);
            border-radius: 18px;
            padding: 1.5rem;
            box-shadow: var(--ai-shadow);
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
            position: relative;
            overflow: hidden;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: var(--ai-shadow-hover);
            border-color: rgba(54, 79, 172, 0.3);
        }

        .card-icon {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.15rem;
            flex-shrink: 0;
            transition: transform 0.2s ease;
        }

        .card:hover .card-icon {
            transform: scale(1.08);
        }

        .icon-admin {
            background: #EEF2FF;
            color: #364FAC;
        }

        .icon-instructor {
            background: #EAF7E6;
            color: #58BA47;
        }

        .icon-student {
            background: #E0F2FE;
            color: #0284C7;
        }

        .icon-parent {
            background: #FFF4E5;
            color: #D97706;
        }

        .card h3 {
            font-size: 1.15rem;
            font-weight: 850;
            margin-bottom: 0.4rem;
            color: var(--ai-text);
        }

        .card p {
            font-size: 0.85rem;
            color: var(--ai-muted);
            margin-bottom: 1.25rem;
            line-height: 1.5;
            flex-grow: 1;
        }

        .badge-list {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }

        .role-badge {
            font-size: 0.72rem;
            font-weight: 750;
            padding: 4px 10px;
            border-radius: 8px;
            letter-spacing: 0.02em;
        }

        .badge-admin {
            background: #EEF2FF;
            color: #364FAC;
        }

        .badge-instructor {
            background: #EAF7E6;
            color: #1F7A18;
        }

        .badge-student {
            background: #E0F2FE;
            color: #0369A1;
        }

        .badge-parent {
            background: #FFF4E5;
            color: #B45309;
        }

        /* Action Gateway */
        .cta-section {
            text-align: center;
            margin-bottom: 3rem;
        }

        .btn-login {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: var(--ai-primary);
            color: #FFFFFF;
            font-size: 1.15rem;
            font-weight: 850;
            padding: 1rem 2.5rem;
            border-radius: 14px;
            text-decoration: none;
            box-shadow: 0 8px 24px -4px rgba(54, 79, 172, 0.4);
            transition: all 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .btn-login:hover {
            background: var(--ai-primary-hover);
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 12px 28px -4px rgba(54, 79, 172, 0.5);
        }

        .btn-login svg {
            width: 22px;
            height: 22px;
        }

        .cta-note {
            margin-top: 0.75rem;
            font-size: 0.88rem;
            color: var(--ai-muted);
            font-weight: 600;
        }

        /* How it works info */
        .info-card {
            background: var(--ai-card);
            border: 1px solid var(--ai-line);
            border-radius: 18px;
            padding: 1.75rem 2rem;
            box-shadow: var(--ai-shadow);
        }

        .info-card h4 {
            font-size: 1.1rem;
            font-weight: 850;
            color: var(--ai-text);
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .steps-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
            text-align: center;
        }

        .step-num {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: var(--ai-primary);
            color: #FFFFFF;
            font-weight: 900;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 0.65rem;
            box-shadow: 0 3px 10px rgba(54, 79, 172, 0.3);
        }

        .steps-grid p {
            font-size: 0.92rem;
            color: var(--ai-muted);
            font-weight: 700;
        }

        /* Responsiveness */
        @media (max-width: 1024px) {
            .grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 640px) {
            .header h1 {
                font-size: 1.85rem;
            }
            .grid {
                grid-template-columns: 1fr;
            }
            .steps-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            .btn-login {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="bg-glow-top"></div>
    <div class="bg-glow-bottom"></div>

    <div class="container">
        <!-- Brand Header -->
        <header class="header">
            <div class="logo-wrap">
                <div class="logo-badge">
                    <svg viewBox="0 0 192 192" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect width="192" height="192" rx="44" fill="#364FAC"/>
                        <path d="M36 129V64h17l42 48V64h19v65H96L55 83v46z" fill="white"/>
                        <path d="M129 64h27v18h-27zm0 31h27v34h-27z" fill="#58BA47"/>
                    </svg>
                </div>
                <span class="logo-name">AI SCHOOL</span>
            </div>
            <h1>{{ config('app.name', 'AI School') }}</h1>
            <p>منصة إدارة المدرسة والتعليم الذكي — تسجيل دخول موحد</p>
            <div class="header-hint">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
                <span>يتم توجيهك تلقائياً إلى لوحة التحكم المخصصة لدورك وصلاحياتك</span>
            </div>
        </header>

        <!-- Role Panels Showcase -->
        <main class="grid">
            <!-- 1. Admin Panel -->
            <article class="card">
                <div class="card-icon icon-admin">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 2l1.9 1.2 2.1-.6 1.2 1.9 2.2.4.4 2.2 1.9 1.2-.6 2.1L22 12l-1.2 1.9.6 2.1-1.9 1.2-.4 2.2-2.2.4-1.2 1.9-2.1-.6L12 22l-1.9-1.2-2.1.6-1.2-1.9-2.2-.4-.4-2.2-1.9-1.2.6-2.1L2 12l1.2-1.9-.6-2.1 1.9-1.2.4-2.2 2.2-.4 1.2-1.9 2.1.6L12 2Z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                </div>
                <h3>لوحة الإدارة</h3>
                <p>إدارة الفروع، الطلاب، الحسابات المالية، الخزائن والصلاحيات الشاملة</p>
                <div class="badge-list">
                    <span class="role-badge badge-admin">Super Admin</span>
                    <span class="role-badge badge-admin">Branch Manager</span>
                    <span class="role-badge badge-admin">Secretary</span>
                </div>
            </article>

            <!-- 2. Instructor Panel -->
            <article class="card">
                <div class="card-icon icon-instructor">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                </div>
                <h3>لوحة المعلمين</h3>
                <p>إدارة المجموعات التعليمية، تسجيل الحضور، الواجبات والامتحانات</p>
                <div class="badge-list">
                    <span class="role-badge badge-instructor">Instructor</span>
                    <span class="role-badge badge-instructor">Supervisor</span>
                </div>
            </article>

            <!-- 3. Student Panel -->
            <article class="card">
                <div class="card-icon icon-student">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m2 10 10-5 10 5-10 5-10-5Zm4 2v5c4 3 8 3 12 0v-5M22 10v7"></path>
                    </svg>
                </div>
                <h3>بوابة الطالب</h3>
                <p>متابعة الكورسات، الواجبات، الامتحانات، الحضور وتجربة Mobile App كاملة</p>
                <div class="badge-list">
                    <span class="role-badge badge-student">Student</span>
                    <span class="role-badge badge-student">Mobile Native App</span>
                </div>
            </article>

            <!-- 4. Parent Panel -->
            <article class="card">
                <div class="card-icon icon-parent">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 20h5v-2a3 3 0 0 0-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <h3>أولياء الأمور</h3>
                <p>متابعة مستوى الأبناء الدراسي، سجل الحضور، المصروفات والأقساط</p>
                <div class="badge-list">
                    <span class="role-badge badge-parent">Parent Portal</span>
                </div>
            </article>
        </main>

        <!-- Login Call to Action -->
        <section class="cta-section">
            <a href="{{ route('dashboard.redirect') }}" class="btn-login">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                    <polyline points="10 17 15 12 10 7"></polyline>
                    <line x1="15" y1="12" x2="3" y2="12"></line>
                </svg>
                <span>تسجيل الدخول إلى النظام</span>
            </a>
            <p class="cta-note">استخدم اسم المستخدم أو البريد الإلكتروني أو رقم الهاتف</p>
        </section>

        <!-- How It Works Card -->
        <section class="info-card">
            <h4>كيف يعمل الدخول الموحد؟</h4>
            <div class="steps-grid">
                <div>
                    <div class="step-num">1</div>
                    <p>اضغط على زر تسجيل الدخول</p>
                </div>
                <div>
                    <div class="step-num">2</div>
                    <p>أدخل بيانات الحساب الخاص بك</p>
                </div>
                <div>
                    <div class="step-num">3</div>
                    <p>سيتم نقلك فوراً إلى لوحتك المخصصة</p>
                </div>
            </div>
        </section>
    </div>
</body>
</html>
