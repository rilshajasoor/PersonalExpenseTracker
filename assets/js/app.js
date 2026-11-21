document.addEventListener('DOMContentLoaded', () => {
  const search = document.getElementById('tableSearch');
  const table = document.getElementById('transactionsTable');
  if (search && table) {
    search.addEventListener('input', () => {
      const term = search.value.toLowerCase();
      table.querySelectorAll('tbody tr').forEach(row => {
        row.hidden = !row.textContent.toLowerCase().includes(term);
      });
    });
  }
});
