<style>
  @page { margin: 18mm 14mm 16mm 14mm; }
  * { box-sizing: border-box; }
  body {
    font-family: DejaVu Sans, sans-serif;
    font-size: 11px;
    color: #1c1917;
    line-height: 1.45;
    margin: 0;
  }
  .pdf-header {
    width: 100%;
    border-bottom: 2px solid #0f5c56;
    padding-bottom: 10px;
    margin-bottom: 14px;
  }
  .pdf-brand {
    font-size: 15px;
    font-weight: 700;
    color: #0f5c56;
    letter-spacing: -0.02em;
    margin: 0 0 2px;
  }
  .pdf-tagline {
    font-size: 10px;
    color: #57534e;
    margin: 0 0 4px;
  }
  .pdf-contact {
    font-size: 9.5px;
    color: #44403c;
    margin: 0;
  }
  .pdf-doc-head {
    width: 100%;
    margin: 0 0 12px;
  }
  .pdf-doc-head td { vertical-align: top; padding: 0; border: 0; }
  .pdf-doc-title {
    font-size: 13px;
    font-weight: 700;
    margin: 0 0 6px;
    text-transform: uppercase;
    letter-spacing: 0.03em;
  }
  .pdf-meta-table {
    width: 100%;
    border-collapse: collapse;
    margin: 0 0 12px;
  }
  .pdf-meta-table td {
    border: 0;
    padding: 2px 0;
    vertical-align: top;
    font-size: 10.5px;
  }
  .pdf-meta-table .lbl {
    width: 18%;
    color: #57534e;
  }
  .pdf-meta-table .val {
    width: 32%;
    font-weight: 600;
  }
  table.data {
    width: 100%;
    border-collapse: collapse;
    margin: 0 0 10px;
  }
  table.data th,
  table.data td {
    border: 1px solid #d6cfc3;
    padding: 5px 6px;
    font-size: 10px;
  }
  table.data th {
    background: #f0ebe3;
    color: #1c1917;
    font-weight: 700;
    text-align: left;
  }
  table.data .num { text-align: right; white-space: nowrap; }
  table.data .ctr { text-align: center; }
  table.data tfoot th {
    background: #e8f0ee;
    font-weight: 700;
  }
  .pdf-summary {
    margin: 4px 0 12px;
    font-size: 10px;
    color: #44403c;
  }
  .pdf-footer {
    margin-top: 18px;
    padding-top: 10px;
    border-top: 1px solid #e4ddd3;
    font-size: 9px;
    color: #57534e;
  }
  .pdf-footer strong { color: #1c1917; }
  .pdf-note {
    margin: 0 0 6px;
    font-style: italic;
  }
  .pdf-footer-meta {
    width: 100%;
    border-collapse: collapse;
  }
  .pdf-footer-meta td {
    border: 0;
    padding: 1px 0;
    vertical-align: top;
  }
  .status-pill {
    display: inline-block;
    padding: 1px 6px;
    border-radius: 3px;
    font-size: 9px;
    font-weight: 700;
    text-transform: uppercase;
  }
  .status-aman { background: #e8f5e9; color: #166534; }
  .status-rendah { background: #ffedd5; color: #9a3412; }
  .status-habis { background: #fee2e2; color: #991b1b; }
</style>
