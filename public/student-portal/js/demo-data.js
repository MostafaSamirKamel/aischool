/* Demo data is only used when a user explicitly clicks "تجربة بيانات توضيحية".
 * It is NOT related to any actual student and is OFF by default. */
window.AISchoolDemoData = () => {
  const data = ({
  student: { id:"demo", displayName:"طالب تجريبي", studentCode:"DEMO-001", email:"student@example.test", phone:"", avatarUrl:"", joinDate:"2026-08-01" },
  notifications: [
    { id:"n1", title:"واجب جديد في أساسيات الويب", body:"موعد التسليم يوم 10 أكتوبر", date:"2026-10-06", read:false, type:"assignment" },
    { id:"n2", title:"اختبار قريب", body:"اختبار JavaScript يوم 12 أكتوبر", date:"2026-10-05", read:false, type:"exam" }
  ],
  courses: [
    {id:"c1",title:"أساسيات تطوير الويب",code:"WEB-101",group:"G101",teacher:"م. سارة",color:"violet",totalSessions:12,completedSessions:8,attendedSessions:7,price:2100,status:"active",nextSession:"2026-10-09T17:00",description:"HTML و CSS و JavaScript من البداية",image:""},
    {id:"c2",title:"مقدمة في Python",code:"PY-102",group:"G205",teacher:"م. أحمد",color:"sky",totalSessions:10,completedSessions:10,attendedSessions:9,price:1800,status:"completed",nextSession:"",description:"فهم أساسيات البرمجة وكتابة تطبيقات بسيطة",image:""},
    {id:"c3",title:"التفكير البرمجي بـ Scratch",code:"SC-103",group:"G306",teacher:"م. نور",color:"coral",totalSessions:8,completedSessions:3,attendedSessions:3,price:1500,status:"active",nextSession:"2026-10-11T16:00",description:"أنشطة عملية لتنمية التفكير المنطقي",image:""}
  ],
  registrations: [
    {id:"r1",courseId:"c1",course:"أساسيات تطوير الويب",group:"G101",date:"2026-08-10",status:"active",price:2100},
    {id:"r2",courseId:"c2",course:"مقدمة في Python",group:"G205",date:"2026-06-15",status:"completed",price:1800},
    {id:"r3",courseId:"c3",course:"التفكير البرمجي بـ Scratch",group:"G306",date:"2026-09-10",status:"active",price:1500}
  ],
  teacherReviews: [
    {id:"t1",teacher:"م. سارة",course:"أساسيات تطوير الويب",group:"G101",rating:4.8,count:42,myRating:null,avatar:"س"},
    {id:"t2",teacher:"م. أحمد",course:"مقدمة في Python",group:"G205",rating:4.9,count:38,myRating:5,avatar:"أ"},
    {id:"t3",teacher:"م. نور",course:"التفكير البرمجي بـ Scratch",group:"G306",rating:4.7,count:27,myRating:null,avatar:"ن"}
  ],
  resources: [
    {id:"res1",title:"ملخص HTML الأساسي",course:"أساسيات تطوير الويب",type:"pdf",size:"1.8 MB",date:"2026-10-02",url:"",description:"ملخص الوسوم الأساسية"},
    {id:"res2",title:"تسجيل المحاضرة الثالثة",course:"أساسيات تطوير الويب",type:"video",size:"45 دقيقة",date:"2026-09-29",url:"",description:"تطبيق على CSS"},
    {id:"res3",title:"ملفات تدريب Python",course:"مقدمة في Python",type:"file",size:"450 KB",date:"2026-09-10",url:"",description:"تمارين على الحلقات والشروط"},
    {id:"res4",title:"مراجع Scratch",course:"التفكير البرمجي بـ Scratch",type:"link",size:"رابط",date:"2026-10-01",url:"",description:"روابط تعليمية مساعدة"}
  ],
  exams: [
    {id:"e1",title:"اختبار JavaScript الأول",course:"أساسيات تطوير الويب",date:"2026-10-12",duration:30,total:20,status:"upcoming",score:null,questions:[{id:"q1",title:"أي وسم يستخدم لعمل عنوان رئيسي؟",options:["<p>","<h1>","<div>"],correct:1},{id:"q2",title:"أي لغة تستخدم لتنسيق صفحات الويب؟",options:["CSS","SQL","Python"],correct:0}]},
    {id:"e2",title:"اختبار مقدمة Python",course:"مقدمة في Python",date:"2026-09-15",duration:40,total:30,status:"completed",score:27,questions:[]},
    {id:"e3",title:"اختبار التفكير البرمجي",course:"التفكير البرمجي بـ Scratch",date:"2026-10-08",duration:20,total:10,status:"available",score:null,questions:[{id:"q3",title:"أي خيار يُستخدم لتكرار الأوامر؟",options:["التكرار","الصوت","الخلفية"],correct:0}]}
  ],
  assignments: [
    {id:"a1",title:"صفحة شخصية باستخدام HTML",course:"أساسيات تطوير الويب",due:"2026-10-10",total:20,status:"pending",score:null,details:"صمّم صفحة تتضمن عنوانًا وصورة وقائمة."},
    {id:"a2",title:"تمارين CSS",course:"أساسيات تطوير الويب",due:"2026-10-04",total:20,status:"submitted",score:null,details:"استخدم flexbox لتوزيع عناصر الصفحة."},
    {id:"a3",title:"مشروع الآلة الحاسبة",course:"مقدمة في Python",due:"2026-09-05",total:30,status:"graded",score:28,details:"أنشئ آلة حاسبة بسيطة."}
  ],
  payments: [
    {id:"p1",course:"أساسيات تطوير الويب",date:"2026-08-10",amount:900,method:"نقدي",status:"paid",ref:"DEMO-P001"},
    {id:"p2",course:"مقدمة في Python",date:"2026-06-15",amount:1800,method:"تحويل",status:"paid",ref:"DEMO-P002"}
  ],
  installments: [
    {id:"i1",course:"أساسيات تطوير الويب",number:2,amount:600,due:"2026-09-10",status:"overdue"},
    {id:"i2",course:"أساسيات تطوير الويب",number:3,amount:600,due:"2026-10-15",status:"upcoming"}
  ],
  messages: [
    {id:"m1",group:"G101",course:"أساسيات تطوير الويب",sender:"م. سارة",role:"teacher",text:"برجاء مراجعة ملخص HTML قبل المحاضرة القادمة.",date:"2026-10-05T18:00"},
    {id:"m2",group:"G101",course:"أساسيات تطوير الويب",sender:"الإدارة",role:"admin",text:"تم إضافة واجب جديد على المنصة.",date:"2026-10-06T09:30"},
    {id:"m3",group:"G205",course:"مقدمة في Python",sender:"الإدارة",role:"admin",text:"مبروك انتهاء المستوى!",date:"2026-09-20T12:15"}
  ],
  attendance: [
    {id:"at1",course:"أساسيات تطوير الويب",group:"G101",date:"2026-10-03",time:"17:00",status:"present",session:"المحاضرة 8"},
    {id:"at2",course:"أساسيات تطوير الويب",group:"G101",date:"2026-09-26",time:"17:02",status:"present",session:"المحاضرة 7"},
    {id:"at3",course:"أساسيات تطوير الويب",group:"G101",date:"2026-09-19",time:"—",status:"absent",session:"المحاضرة 6"},
    {id:"at4",course:"مقدمة في Python",group:"G205",date:"2026-09-18",time:"16:59",status:"present",session:"المحاضرة 10"}
  ]
});
  // Distinct DEMO-only session metadata. Never invent sessions for API records.
  data.courses.forEach(c => {
    c.groupStatus = c.status === 'completed' ? 'closed' : 'open';
    c.sessions = Array.from({length: Math.min(c.totalSessions, 12)}, (_,index) => ({
      id: `${c.id}-session-${index+1}`,
      title: `السيشن ${index+1} — ${['مقدمة وتطبيق عملي','تدريبات على المفاهيم','مشروع صغير','مراجعة وتحديات'][index%4]}`,
      date: `2026-${String(8+Math.floor(index/4)).padStart(2,'0')}-${String(2+(index%4)*6).padStart(2,'0')}`,
      status: index < c.completedSessions ? 'completed' : 'upcoming',
      description: 'مراجعة المفاهيم الجديدة مع تدريب عملي داخل السيشن.',
      resource: index === 0, assignment: index === 2, exam: index === 5
    }));
  });
  data.registrations.forEach(r => { r.discount = 0; });
  data.payments[0].courseId = 'c1';
  data.payments[1].courseId = 'c2';
  return data;
};
