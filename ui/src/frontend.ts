const markerId = 'forge-admin-suite-frontend';

if (!document.getElementById(markerId)) {
  const marker = document.createElement('div');
  marker.id = markerId;
  marker.textContent = 'Forge Admin Suite frontend assets loaded.';
  marker.className =
    'm-4 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm text-slate-700 shadow-sm';

  document.body.appendChild(marker);
}
