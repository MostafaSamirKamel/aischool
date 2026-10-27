/**
 * AI SCHOOL — STUDENT PORTAL API ADAPTER
 * The UI does not assume a backend framework or hardcode student IDs.
 * Only authenticated requests may return data for the signed-in student.
 * See README.md for endpoint contract / integration rules.
 */
window.AISchoolConfig = {
  apiBaseUrl: '', // Set to your backend origin, e.g. https://api.example.com
  portalEndpoint: '/api/student/portal',
  credentials: 'include', // HttpOnly session cookie preferred. Set CORS correctly on your server.
  headers: {},
};
window.AISchoolAPI = {
  async getStudentPortal() {
    const cfg = window.AISchoolConfig;
    if (!cfg.apiBaseUrl) return null;
    const url = cfg.apiBaseUrl.replace(/\/$/, '') + cfg.portalEndpoint;
    const response = await fetch(url, {
      method: 'GET',
      credentials: cfg.credentials,
      headers: { Accept: 'application/json', ...cfg.headers },
    });
    if (!response.ok) throw new Error(`HTTP ${response.status}`);
    const json = await response.json();
    return json && typeof json.data === 'object' && !Array.isArray(json.data) ? json.data : json;
  },
  async post(relativePath, body) {
    const cfg = window.AISchoolConfig;
    if (!cfg.apiBaseUrl) throw new Error('API_NOT_CONFIGURED');
    const response = await fetch(cfg.apiBaseUrl.replace(/\/$/, '') + relativePath, {
      method: 'POST',
      credentials: cfg.credentials,
      headers: { Accept: 'application/json', 'Content-Type': 'application/json', ...cfg.headers },
      body: JSON.stringify(body),
    });
    if (!response.ok) throw new Error(`HTTP ${response.status}`);
    return response.json();
  },
};
