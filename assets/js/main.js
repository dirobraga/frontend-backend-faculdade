// FinControl — main.js

// Confirm delete dialogs
document.querySelectorAll('[data-confirm]').forEach(el => {
  el.addEventListener('click', e => {
    if (!confirm(el.dataset.confirm || 'Confirmar ação?')) e.preventDefault();
  });
});

// Auto-dismiss flash messages
setTimeout(() => {
  document.querySelectorAll('.flash').forEach(f => {
    f.style.transition = 'opacity .4s';
    f.style.opacity = '0';
    setTimeout(() => f.remove(), 400);
  });
}, 4000);

// Money mask for valor inputs
document.querySelectorAll('input[data-mask="money"]').forEach(inp => {
  inp.addEventListener('input', () => {
    let v = inp.value.replace(/\D/g, '');
    v = (parseInt(v || '0') / 100).toFixed(2);
    inp.value = v.replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
  });
});
