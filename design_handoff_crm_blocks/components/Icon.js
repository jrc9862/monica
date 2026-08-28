// Plain-JS Icon component for the DC runtime (no ESM).
function Icon({ name, size, ...rest }) {
  const d = (window.CRMIcons || {})[name];
  if (!d) return null;
  return React.createElement('svg', {
    width: size, height: size, viewBox: d.viewBox, fill: 'none',
    dangerouslySetInnerHTML: { __html: d.body }, ...rest
  });
}
window.Icon = Icon;
