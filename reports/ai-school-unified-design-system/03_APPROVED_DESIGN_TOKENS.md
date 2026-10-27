# 03 — APPROVED DESIGN TOKENS & SYSTEM SPECIFICATION

**Project:** AI School Management System  
**Design System:** AI School Enterprise SaaS Design Tokens  

---

## 1. Color Palette Tokens

```css
:root {
    /* Brand Accent Colors */
    --ai-primary: #364FAC;            /* Royal Brand Blue */
    --ai-primary-hover: #293C85;      /* Deepened Blue */
    --ai-primary-weak: #EEF2FF;       /* Soft Blue Tint */
    --ai-primary-ring: rgba(54, 79, 172, 0.2);

    --ai-accent: #58BA47;             /* Vibrant Brand Green */
    --ai-accent-hover: #459637;       /* Darker Green */
    --ai-accent-weak: #EAF7E6;        /* Soft Mint Tint */

    /* Employee Dark Navy Sidebar System */
    --ai-sidebar-bg: #162746;         /* Approved Dark Navy Canvas */
    --ai-sidebar-header-bg: #0F1D36;  /* Deep Navy Brand Area */
    --ai-sidebar-hover: #1E355F;       /* Hover Surface */
    --ai-sidebar-active: #254580;      /* Selected Item Background */
    --ai-sidebar-text: #E2E8F0;        /* Clean Off-White Text */
    --ai-sidebar-text-muted: #94A3B8;  /* Secondary Muted Text */
    --ai-sidebar-group: #8FA3C7;       /* Group Heading Accent */
    --ai-sidebar-border: rgba(255, 255, 255, 0.08);

    /* Canvas & Surfaces (Light Mode Default) */
    --ai-bg: #F8FAFC;                 /* Neutral Light Background */
    --ai-bg-secondary: #F1F5F9;       /* Secondary Surface */
    --ai-card: #FFFFFF;               /* Crisp White Card */
    --ai-card-border: #E2E8F0;        /* Subtle Outline */

    /* Typography Colors */
    --ai-text: #0F172A;               /* High-Contrast Primary Text */
    --ai-text-secondary: #64748B;     /* Slate Secondary Text */
    --ai-text-muted: #94A3B8;         /* Subdued Helper Text */

    /* Dark Mode Palette */
    --ai-dark-bg: #0F172A;            /* Deep Slate Canvas */
    --ai-dark-card: #1E293B;          /* Slate Card Surface */
    --ai-dark-border: #2A374F;        /* Dark Mode Separator */
    --ai-dark-text: #F8FAFC;          /* Clean White Text */
    --ai-dark-text-secondary: #94A3B8;
}
```

---

## 2. Typography Hierarchy

Primary Arabic Font: **`Tajawal`**  
Secondary Latin / Numbers: **`Inter`**

| Element | Size | Weight | Line Height | Usage |
|:---|:---:|:---:|:---:|:---|
| **Page Title (H1)** | 22–24px | 800 (ExtraBold) | 1.25 | Panel headers, major page headings |
| **Section Title (H2)** | 17–18px | 700 (Bold) | 1.35 | Card titles, modal titles, table headers |
| **KPI Metric Values** | 24–28px | 800 (ExtraBold) | 1.15 | Dashboard stat counters and revenue figures |
| **Standard Body** | 14–15px | 400/500 (Regular/Medium) | 1.5 | Paragraphs, descriptions, notices |
| **Table Data** | 14px | 500/600 (Medium/SemiBold) | 1.4 | Table cell content |
| **Form Labels** | 14px | 600 (SemiBold) | 1.3 | Input labels and field wrappers |
| **Secondary Metadata** | 12–13px | 400/500 | 1.4 | Timestamps, IDs, descriptions |

---

## 3. Dimensional Standards
- **Input & Standard Button Height:** `42–44px`
- **Table Row Height (Medium Density):** `48–52px`
- **Header (Topbar) Height:** `62px`
- **Card Border Radius:** `14px`
- **Button & Input Border Radius:** `8–10px`
- **Modal Border Radius:** `16px`
- **Modal Max Height:** `90vh`
