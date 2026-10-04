export default async function handler(_req, res) {
  const upstream = await fetch('https://files.manuscdn.com/user_upload_by_module/session_file/310519663567085695/oMamGGLqqmcCvyhv.css');

  if (!upstream.ok) {
    return res.status(502).send('Geo Booster stylesheet unavailable');
  }

  const css = await upstream.text();
  res.setHeader('Content-Type', 'text/css; charset=utf-8');
  res.setHeader('Content-Disposition', 'inline');
  res.setHeader('Cache-Control', 'public, max-age=60, s-maxage=300');
  return res.status(200).send(css);
}
