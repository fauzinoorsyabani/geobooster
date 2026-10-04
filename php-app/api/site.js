const HTML_URL = 'https://files.manuscdn.com/user_upload_by_module/session_file/310519663567085695/mmfSwcFeeOmSspkC.html';
const CSS_URL = 'https://files.manuscdn.com/user_upload_by_module/session_file/310519663567085695/oMamGGLqqmcCvyhv.css';

export default async function handler(_req, res) {
  const upstream = await fetch(HTML_URL);

  if (!upstream.ok) {
    return res.status(502).send('Geo Booster upstream unavailable');
  }

  let html = await upstream.text();
  html = html.replaceAll(CSS_URL, '/api/style.css');
  res.setHeader('Content-Type', 'text/html; charset=utf-8');
  res.setHeader('Content-Disposition', 'inline');
  res.setHeader('Cache-Control', 'public, max-age=60, s-maxage=300');
  return res.status(200).send(html);
}
