/** Smoke test of all screens, runs with Node.js and no external packages. */
const fs = require('node:fs');
const vm = require('node:vm');
const path = require('node:path');
const assert = require('node:assert/strict');
const root = path.resolve(__dirname, '..');
const listeners = {};
const rootElement = { innerHTML: '' };
const contentElement = {scrollTop:0};
const testDocument = {
  body: { classList:{toggle(){} } },
  getElementById(id){return id==='app'?rootElement:id==='content'?contentElement:null;},
  addEventListener(name,fn){listeners[name]=fn},
  querySelector(){return null},
  querySelectorAll(){return []}
};
const url={protocol:'file:', hash:''};
const sandbox={
  document:testDocument,
  window:{addEventListener(name,fn){listeners[name]=fn},scrollTo(){},AISchoolPreviewDemo:false},
  localStorage:{getItem(){return null},setItem(){}},
  location:url,
  navigator:{},
  console,
  setTimeout,
  clearTimeout,
  Intl,
  Blob,
  URL,
};
sandbox.window.location = url;
vm.createContext(sandbox);
for(const file of ['js/demo-data.js','js/api.js','js/app.js'])vm.runInContext(fs.readFileSync(path.join(root,file),'utf8'),sandbox,{filename:file});
assert(rootElement.innerHTML.includes('الواجهة غير متصلة'), 'Default is empty/clean');
assert(!rootElement.innerHTML.includes('أساسيات تطوير الويب'), 'No demo content loaded by default');
function click(action,dataset={}){
  const attrs=['data-action','data-nav','data-course','data-filter','data-course-tab','data-session-toggle','data-review','data-resource','data-resource-course','data-group','data-exam','data-assignment','data-attendance','data-rating','data-close-drawer'];
  const attr=action.startsWith('data-')?action:'data-action';
  const target={dataset:attr==='data-action'?{...dataset,action}:{...dataset},hasAttribute(name){return name===attr;},closest(){return this;}};
  listeners.click({target});
}
click('demo');
assert(rootElement.innerHTML.includes('أساسيات تطوير الويب'),'Demo button loads fixtures');
const routes=['dashboard','courses','services','reviews','enrollments','resources','exams','assignments','payments','messages','attendance','reports','profile','search'];
for(const route of routes){url.hash='#/'+route;listeners.hashchange();assert(rootElement.innerHTML.length>3500, 'Empty layout at '+route);console.log('PASS',route,rootElement.innerHTML.length);}
url.hash='#/course/c1';listeners.hashchange();assert(rootElement.innerHTML.includes('محتوى الكورس'));
assert(rootElement.innerHTML.includes('السيشن 1'));
console.log('PASS course details');
url.hash='#/reports';listeners.hashchange();assert(rootElement.innerHTML.includes('تصدير CSV'));
url.hash='#/attendance';listeners.hashchange();assert(rootElement.innerHTML.includes('نسبة الحضور'));
click('empty');
assert(!rootElement.innerHTML.includes('أساسيات تطوير الويب'),'Demo data cleared');
console.log('PASS reset demo to empty state');
console.log('ALL SMOKE TESTS PASSED');
