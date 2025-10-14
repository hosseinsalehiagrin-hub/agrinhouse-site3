// FORCE DEPLOY: 2025-10-14 - Functions sync fix
// functions/[[...path]].js - Complete SPA Router for AgrinHouse
export async function onRequest(context) {
  const url = new URL(context.request.url);
  const pathname = url.pathname;
  
  console.log(`🌐 SPA Route: ${pathname}`);
  
  // Static files
  const isStatic = /\.(js|css|png|jpg|webp|svg|json|html)$/i.test(pathname);
  if (isStatic) {
    if (pathname === '/home' || pathname === '/home.html') {
      try {
        const homeResp = await context.env.ASSETS.fetch(
          new Request(new URL('/home.html', context.request.url))
        );
        return new Response(homeResp.body, {
          status: 200,
          headers: { 'Content-Type': 'text/html', 'Cache-Control': 'public, max-age=3600' }
        });
      } catch (e) {
        console.error('Home fetch failed:', e);
      }
    }
    return await context.env.ASSETS.fetch(context.request);
  }
  
  // API Mock
  if (pathname === '/check_session.json') {
    return new Response(JSON.stringify({
      loggedIn: true,
      user: { id: 1, name: "Admin", email: "admin@agrinhouse.com", role: "admin" }
    }), {
      status: 200,
      headers: { 'Content-Type': 'application/json', 'Access-Control-Allow-Origin': '*' }
    });
  }
  
  // SPA Routes
  const spaPages = ['catalog', 'services', 'company-profile', 'inventory', 'about'];
  const isSPAPage = spaPages.some(page => pathname === `/${page}` || pathname.startsWith(`/${page}/`));
  
  if (isSPAPage || (pathname !== '/' && !pathname.includes('.'))) {
    console.log(`🎯 SPA: ${pathname} → index.html`);
    
    const indexResp = await context.env.ASSETS.fetch(
      new Request(new URL('/index.html', context.request.url))
    );
    let html = await indexResp.text();
    
    // Inject SPA script
    html = html.replace('</head>', `
      <script>
        window.__SPA_PATH = '${pathname}';
        window.APP_STATE = { isLoggedIn: true, currentPath: '${pathname}', isSPAMode: true };
        console.log('🚀 SPA Active:', window.__SPA_PATH);
        document.addEventListener('DOMContentLoaded', () => {
          setTimeout(() => {
            if (window.showWindow) {
              const page = '${pathname.replace(/^\//, '')}';
              window.showWindow('/${page}/index.html');
            }
          }, 300);
        });
      </script></head>
    `);
    
    return new Response(html, {
      status: 200,
      headers: { 
        'Content-Type': 'text/html',
        'X-SPA-Route': pathname,
        'Cache-Control': 'public, max-age=0'
      }
    });
  }
  
  // Fallback
  return await context.env.ASSETS.fetch(
    new Request(new URL('/index.html', context.request.url))
  );
}
